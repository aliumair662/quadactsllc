<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PDF;

class purchaseController extends Controller
{
    public function purchaseList()
    {
        $list = DB::table('purchases')->join('vendors', 'purchases.vendor_id', '=', 'vendors.id')
            ->select('purchases.*', 'vendors.name as vendor_name')->where('purchases.branch', Auth::user()->branch)
            ->orderByDesc('purchases.id')
            ->paginate(20);
        $items = DB::table('items')->get();
        $net_total = DB::table('purchases')->sum('net_total');
        $net_qty = DB::table('purchases')->sum('net_qty');
        $net_pcs = DB::table('purchases')->sum('net_pcs');
        $vendors = DB::table('vendors')->get();
        return view('purchases.list', array('purchaselist' => $list, 'items' => $items, 'net_total' => $net_total, 'net_qty' => $net_qty, 'net_pcs' => $net_pcs, 'vendors' => $vendors));
    }
    public function newPurchase()
    {
        $vendors = DB::table('vendors')->select('id', 'name')->where('branch', Auth::user()->branch)->where('status', 1)->get();
        $items = DB::table('items')
            ->where('cancel_status', '!=', 1)
            ->whereIn('category', [3, 5, 6])
            ->get();
        $invoice_number = DB::table('purchases')->max('id') + 1;
        $term_conditions = DB::table('term_condition')->orderByDesc('id')->get();
        return view('purchases.new', array('vendors' => $vendors, 'items' => $items, 'invoice_number' => $invoice_number, 'term_conditions' => $term_conditions));
    }

    public function savePurchase(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $sale = DB::table('purchases')->where('invoice_number', $request->invoice_number)->first();
        if (!empty($sale)) {
            return response()->json(['success' => false, 'message' => 'Purchase Invoice already exits..', 'redirectUrl' => '/purchases/list'], 200);
        }


        $validator = Validator::make(
            $request->all(),
            [
                'invoice_number' => 'required',
                'invoice_date' => 'required',
                'vendor_id' => 'required|numeric',
                'net_total' => 'required|numeric|min:0|not_in:0',
                // 'net_pcs' => 'required|numeric|min:0|not_in:0',
                'net_qty' => 'required|numeric|min:0|not_in:0',
                // 'note' => 'required',
            ],
            [
                'invoice_number.required' => 'The Invoice #  is required.',
                'invoice_date.required' => 'The Invoice Date  is required.',
                'vendor_id.required' => 'The Vendor is required.',
                'net_total.required' => 'Net Total   is required.',
                // 'net_pcs.required' => 'Net Pcs   is required.',
                'net_qty.required' => 'Net Qty   is required.',
                // 'note.required' => 'Notes   is required.',
            ]
        );
        if ($validator->fails()) {
            //$response['message'] = $validator->messages();
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {

            $items_detail = array();
            $item_ids = $request->item_id;
            $item_prices = $request->item_price;
            $item_qtys = $request->item_qty;
            $item_pcss = $request->item_pcs;
            $amounts = $request->amount;
            $i = 0;
            foreach ($item_ids as $item) {
                $itemid = $item_ids[$i];
                $price = $item_prices[$i];
                $pcs = $item_pcss[$i];
                $qty = $item_qtys[$i];
                $amount = $amounts[$i];
                if ($amount > 0) {
                    $items_detail[] = array(
                        'item_id' => $itemid,
                        'item_price' => $price,
                        'item_qty' => $qty,
                        'item_pcs' => $pcs,
                        'amount' => $amount,
                    );
                }

                $i++;
            }
            /**
             * Insert Double entry
             * Purchases A/c Debit
             *Vendor A/c  Credit
             */
            $vendor = DB::table('vendors')->where('id', $request->vendor_id)->first();
            $debit = array(
                'voucher_date' => $request->invoice_date,
                'voucher_number' => $request->invoice_number,
                'general_ledger_account_id' => Config::get('constants.PURCHASE_ACCOUNT_GENERAL_LEDGER'),
                'note' => isset($request->note) ? $request->note : null,
                'debit' => $request->net_total,
                'credit' => 0,
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($debit);
            $credit = array(
                'voucher_date' => $request->invoice_date,
                'voucher_number' => $request->invoice_number,
                'general_ledger_account_id' => $vendor->general_ledger_account_id,
                'note' => isset($request->note) ? $request->note : null,
                'debit' => 0,
                'credit' => $request->net_total,
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($credit);
            /**
             * Insert Stock Entry for each time
             * 1.get category of item
             *2.get linked general ledger account id from category table
             * if finished good purchased then stock will calcualted by sale price else purchase price
             */
            foreach ($items_detail as $_detail) {
                $item = DB::table('items')->where('id', $_detail['item_id'])->first();
                $category = DB::table('category')->where('id', $item->category)->first();
                $company = DB::table('companyinfo')->first();
                if ($company->stock_calculation == 0) {

                    $debit = array(
                        'voucher_date' => $request->invoice_date,
                        'voucher_number' => $request->invoice_number,
                        'general_ledger_account_id' => $category->general_ledger_account_id,
                        'note' => $item->name . ' ' . $_detail['item_qty'] . ' @ ' . $_detail['item_price'] . ($category->general_ledger_account_id == Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER')) ? ' | ' . $_detail['item_qty'] . ' x ' . $item->sele_price  : '',
                        'debit' => ($category->general_ledger_account_id == Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER')) ? $_detail['item_qty'] * $item->sele_price  : $_detail['amount'],
                        'credit' => 0,
                        'branch' => Auth::user()->branch,
                        'created_at' => date('Y-m-d H:i:s'),
                    );
                    $this->insertDoubleEntry($debit);
                }
                $record = DB::table('items')->where('id', $_detail['item_id'])->first();
                $unseri = unserialize($record->linked_items);
                if (!empty($unseri)) {
                    if (count($unseri) > 0) {
                        foreach ($unseri as $value) {
                            $qty = $value['item_qty'] * $_detail['item_qty'];

                            $stock  = array(
                                'voucher_date' => $request->invoice_date,
                                'voucher_number' => $request->invoice_number,
                                'transaction_type' => '+',
                                'general_ledger_account_id' => $category->general_ledger_account_id,
                                'item_qty' => $qty,
                                'item_id' => $value['item_id'],
                                'branch' => Auth::user()->branch,
                                'created_at' => date('Y-m-d H:i:s'),
                            );
                            $this->stockManagementEntry($stock);
                        }
                    }
                }
                $stock  = array(
                    'voucher_date' => $request->invoice_date,
                    'voucher_number' => $request->invoice_number,
                    'transaction_type' => '+',
                    'general_ledger_account_id' => $category->general_ledger_account_id,
                    'item_qty' => $_detail['item_qty'],
                    'item_id' => $_detail['item_id'],
                    'branch' => Auth::user()->branch,
                    'created_at' => date('Y-m-d H:i:s'),
                );
                $this->stockManagementEntry($stock);
            }
            $purchase = array(
                'net_total' => $request->net_total,
                'gross_amount' => $request->gross_amount,
                'discount_amount' => $request->discount_amount,
                'net_pcs' => $request->net_pcs,
                'net_qty' => $request->net_qty,
                'created_at' => date('Y-m-d H:i:s'),
                'vendor_id' => $request->vendor_id,
                'items_detail' => serialize($items_detail),
                'invoice_number' => $request->invoice_number,
                'note' => isset($request->note) ? $request->note : null,
                'invoice_date' => $request->invoice_date,
                'branch' => Auth::user()->branch,
                'note_html' => isset($request->html_semantic) ? $request->html_semantic : null
            );

            $idForPdf = DB::table('purchases')->insertGetId($purchase);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->invoice_number,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($purchase),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Purchase Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Purchase Invoice added successfully..', 'redirectUrl' => '/purchases/purchaselist', 'print' => "/purchases/purchasePdf/{$idForPdf}"], 200);
        }
    }

    public function editPurchase($id)
    {
        $purchase = DB::table('purchases')->where('id', $id)->first();
        $vendors = DB::table('vendors')->where('status', 1)->where('branch', Auth::user()->branch)->get();
        $items = DB::table('items')
            ->where('cancel_status', '!=', 1)
            // ->whereIn('category', [3, 5, 6])
            ->get();
        $term_conditions = DB::table('term_condition')->orderByDesc('id')->get();
        return view('purchases.new', array('purchase' => $purchase, 'vendors' => $vendors, 'items' => $items, 'term_conditions' => $term_conditions));
    }


    public function updatePurchase(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $validator = Validator::make(
            $request->all(),
            [
                'invoice_number' => 'required',
                'invoice_date' => 'required',
                'vendor_id' => 'required|numeric',
                'net_total' => 'required|numeric|min:0|not_in:0',
                // 'note' => 'required',
            ],
            [
                'invoice_number.required' => 'The Invoice #  is required.',
                'invoice_date.required' => 'The Invoice Date  is required.',
                'vendor_id.required' => 'Vendor is required.',
                'net_total.required' => 'Net Total is required.',
                // 'note.required' => 'Notes field is required.',
            ]
        );
        if ($validator->fails()) {
            //$response['message'] = $validator->messages();
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {

            $items_detail = array();
            $item_ids = $request->item_id;
            $item_prices = $request->item_price;
            $item_qtys = $request->item_qty;
            $item_pcss = $request->item_pcs;
            $amounts = $request->amount;
            $i = 0;
            foreach ($item_ids as $item) {
                $itemid = $item_ids[$i];
                $price = $item_prices[$i];
                $qty = $item_qtys[$i];
                $pcs = $item_pcss[$i];
                $amount = $amounts[$i];
                if ($amount > 0) {
                    $items_detail[] = array(
                        'item_id' => $itemid,
                        'item_price' => $price,
                        'item_qty' => $qty,
                        'item_pcs' => $pcs,
                        'amount' => $amount,
                    );
                }

                $i++;
            }
            /**
             *Delete first all entries from General Ledger Transactions Table
             *Insert Double entry
             * Purchases A/c Debit
             *Vendor A/c  Credit
             */
            $this->deleteDoubleEntry($request->invoice_number);
            $this->stockManagementEntryDelete($request->invoice_number);
            $vendor = DB::table('vendors')->where('id', $request->vendor_id)->first();
            $debit = array(
                'voucher_date' => $request->invoice_date,
                'voucher_number' => $request->invoice_number,
                'general_ledger_account_id' => Config::get('constants.PURCHASE_ACCOUNT_GENERAL_LEDGER'),
                'note' => isset($request->note) ? $request->note : null,
                'debit' => $request->net_total,
                'credit' => 0,
                'branch' => Auth::user()->branch,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($debit);
            $credit = array(
                'voucher_date' => $request->invoice_date,
                'voucher_number' => $request->invoice_number,
                'general_ledger_account_id' => $vendor->general_ledger_account_id,
                'note' => isset($request->note) ? $request->note : null,
                'debit' => 0,
                'credit' => $request->net_total,
                'branch' => Auth::user()->branch,
                'updated_at' => date('Y-m-d H:i:s'),
            );

            $this->insertDoubleEntry($credit);
            /**
             * Insert Stock Entry for each time
             * 1.get category of item
             *2.get linked general ledger account id from ctegory table
             */
            foreach ($items_detail as $_detail) {
                $item = DB::table('items')->where('id', $_detail['item_id'])->first();
                $category = DB::table('category')->where('id', $item->category)->first();
                $company = DB::table('companyinfo')->first();
                if ($company->stock_calculation == 0) {

                    $debit = array(
                        'voucher_date' => $request->invoice_date,
                        'voucher_number' => $request->invoice_number,
                        'general_ledger_account_id' => $category->general_ledger_account_id,
                        'note' => $item->name . ' ' . $_detail['item_qty'] . ' @ ' . $_detail['item_price'] . ($category->general_ledger_account_id == Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER')) ? ' | ' . $_detail['item_qty'] . ' x ' . $item->sele_price  : '',
                        'debit' => ($category->general_ledger_account_id == Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER')) ? $_detail['item_qty'] * $item->sele_price  : $_detail['amount'],
                        'credit' => 0,
                        'branch' => Auth::user()->branch,
                        'updated_at' => date('Y-m-d H:i:s'),
                    );
                    $this->insertDoubleEntry($debit);
                }
                $record = DB::table('items')->where('id', $_detail['item_id'])->first();
                $unseri = unserialize($record->linked_items);
                if (!empty($unseri)) {

                    if (count($unseri) > 0) {
                        foreach ($unseri as $value) {
                            $qty = $value['item_qty'] * $_detail['item_qty'];
                            $stock  = array(
                                'voucher_date' => $request->invoice_date,
                                'voucher_number' => $request->invoice_number,
                                'transaction_type' => '+',
                                'general_ledger_account_id' => $category->general_ledger_account_id,
                                'item_qty' => $qty,
                                'item_id' => $value['item_id'],
                                'branch' => Auth::user()->branch,
                                'created_at' => date('Y-m-d H:i:s'),
                            );
                            $this->stockManagementEntry($stock);
                        }
                    }
                }
                $stock  = array(
                    'voucher_date' => $request->invoice_date,
                    'voucher_number' => $request->invoice_number,
                    'transaction_type' => '+',
                    'general_ledger_account_id' => $category->general_ledger_account_id,
                    'item_qty' => $_detail['item_qty'],
                    'item_id' => $_detail['item_id'],
                    'branch' => Auth::user()->branch,
                    'created_at' => date('Y-m-d H:i:s'),
                );

                $this->stockManagementEntry($stock);
            }
            $purchases = array(
                'net_total' => $request->net_total,
                'gross_amount' => $request->gross_amount,
                'discount_amount' => $request->discount_amount,
                'net_pcs' => $request->net_pcs,
                'net_qty' => $request->net_qty,
                'updated_at' => date('Y-m-d H:i:s'),
                'vendor_id' => $request->vendor_id,
                'items_detail' => serialize($items_detail),
                'invoice_number' => $request->invoice_number,
                'note' => isset($request->note) ? $request->note : null,
                'invoice_date' => $request->invoice_date,
                'branch' => Auth::user()->branch,
                'note_html' => isset($request->html_semantic) ? $request->html_semantic : null
            );

            DB::table('purchases')->where('id', $request->id)->update($purchases);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->invoice_number,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($purchases),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Purchase Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Purchase Updated added successfully..', 'redirectUrl' => '/purchases/purchaselist'], 200);
        }
    }



    public function deletePurchase($id)
    {
        $purchases = DB::table('purchases')->where('id', $id)->first();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $purchases->invoice_number,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($purchases),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Purchase Invoice',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        $this->stockManagementEntryDelete($purchases->invoice_number);
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $this->deleteDoubleEntry($purchases->invoice_number);
        DB::table('purchases')->where('invoice_number', $purchases->invoice_number)->delete();
        return response()->json(['success' => true, 'message' => 'Purchase deleted successfully..', 'redirectUrl' => '/purchases/purchaselist'], 200);
    }

    public function searchPurchase(Request $request)
    {
        $Queries = array();
        if (empty($request->from_date) && empty($request->to_date) && empty($request->vendor_id) && empty($request->invoice_number)) {
            return redirect('purchases/purchaselist');
        }
        $query = DB::table('purchases');
        $query->join('vendors', 'purchases.vendor_id', '=', 'vendors.id');
        $query->select('purchases.*', 'vendors.name as vendor_name', 'vendors.*');
        if (isset($request->invoice_number) && !empty($request->invoice_number)) {
            $Queries['invoice_number'] = $request->invoice_number;
            $query->where('purchases.invoice_number', 'like', "%$request->invoice_number%");
        }

        if (isset($request->from_date) && isset($request->to_date)) {
            $Queries['from_date'] = $request->from_date;
            $Queries['to_date'] = $request->to_date;
            $query->whereBetween('purchases.invoice_date', [$request->from_date, $request->to_date]);
        }
        if (isset($request->vendor_id)) {
            $Queries['vendor_id'] = $request->vendor_id;
            $query->where('vendors.id', "$request->vendor_id");
        }

        $result = $query->where('purchases.branch', Auth::user()->branch)->orderByDesc('purchases.id')->paginate(20);
        $result->appends($Queries);
        $net_total = $query->sum('net_total');
        $net_pcs = $query->sum('net_pcs');
        $net_qty = $query->sum('net_qty');
        $vendors = DB::table('vendors')->get();
        return view('purchases.list', array('purchaselist' => $result, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'vendor_id' => $request->vendor_id, 'invoice_number' => $request->invoice_number, 'queries' => $Queries, 'net_total' => $net_total, 'net_qty' => $net_qty, 'net_pcs' => $net_pcs, 'vendors' => $vendors));
    }


    public function purchasePagePdf($from_date, $to_date, $vendor_id, $invoice_number)
    {
        $query = DB::table('purchases')->join('vendors', 'purchases.vendor_id', '=', 'vendors.id')
            ->select('purchases.*', 'vendors.name as vendor_name')->where('purchases.branch', Auth::user()->branch);
        if ($from_date != 'none' && $to_date != 'none') {
            $query->whereBetween('purchases.invoice_date', [$from_date, $to_date]);
        }
        if ($invoice_number != 'none') {
            $query->where('purchases.invoice_number', 'like', "%$invoice_number%");
        }
        if ($vendor_id != 'none') {
            $query->where('vendors.id', "$vendor_id");
        }
        $list = $query->orderByDesc('purchases.id')->get();
        $net_total = $query->sum('net_total');
        $net_qty =  $query->sum('net_qty');
        $net_pcs =  $query->sum('net_pcs');
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;
        $data = array(
            'lists' => $list,
            'companyinfo' => $companyinfo,
            'net_total' => $net_total,
            'net_qty' => $net_qty,
            'net_pcs' => $net_pcs,
        );

        $pdf = PDF::loadView('purchases.purchasePagePdf', $data);
        return $pdf->stream('pagePdf.pdf');
    }


    public function purchasePdf($id)
    {
        $purchase = DB::table('purchases')->where('purchases.id', $id)
            ->leftJoin('vendors', 'purchases.vendor_id', '=', 'vendors.id')
            ->first();
        // $vendors = DB::table('vendors')->where('status', 1)->where('branch', Auth::user()->branch)->get();
        $items = DB::table('items')->where('branch', Auth()->user()->branch)->get();
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;
        $data =  array('purchase' => $purchase, 'items' => $items, 'companyinfo' => $companyinfo);
        $pdf = PDF::loadView('purchases.purchasePdf', $data);
        return $pdf->stream('purchasePdf.pdf');
    }
    public function insertDoubleEntry($data)
    {
        /**
         * In case of exception,Roll Back whole Entry
         * remove double entry
         *
         */
        try {
            DB::table('general_ledger_transactions')->insertGetId($data);
        } catch (\Exception $e) {
            DB::table('general_ledger_transactions')->where('voucher_number', $data['voucher_number'])->delete();
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'redirectUrl' => '/sales/list'], 200);
        }
    }
    public function deleteDoubleEntry($voucher_number)
    {
        try {
            DB::table('general_ledger_transactions')->where('voucher_number', $voucher_number)->delete();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'redirectUrl' => '/sales/list'], 200);
        }
    }
    /**
     * Stock General Entries
     *
     */
    public function stockEntry($voucher_number)
    {
        try {
            DB::table('general_ledger_transactions')->where('voucher_number', $voucher_number)->delete();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'redirectUrl' => '/sales/list'], 200);
        }
    }

    public function stockManagementEntry($data)
    {
        try {
            DB::table('general_inventory_transactions')->insertGetId($data);
        } catch (\Exception $e) {
            DB::table('general_inventory_transactions')->where('voucher_number', $data->voucher_number)->delete();
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'redirectUrl' => '/sales/list'], 200);
        }
    }

    public function stockManagementEntryDelete($voucher_number)
    {
        try {
            DB::table('general_inventory_transactions')->where('voucher_number', $voucher_number)->delete();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'redirectUrl' => '/sales/list'], 200);
        }
    }

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
}
