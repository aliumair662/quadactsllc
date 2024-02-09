<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use PDF;

class salesReturnController extends Controller
{
    public function saleReturnList()
    {
        $list = DB::table('sales_return')
            ->join('customers', 'sales_return.customer_id', '=', 'customers.id')
            ->select('sales_return.*', 'customers.name as customer_name')->where('sales_return.branch', Auth::user()->branch)
            ->orderByDesc('sales_return.id')
            ->paginate(20);
        $net_total = DB::table('sales_return')->sum('net_total');
        $net_qty = DB::table('sales_return')->sum('net_qty');
        $net_pcs = DB::table('sales_return')->sum('net_pcs');
        $customers = DB::table('customers')->get();
        return view('salesReturn.list', array('saleReturnList' => $list, 'net_total' => $net_total, 'net_pcs' => $net_pcs, 'net_qty' => $net_qty, 'customers' => $customers));
    }

    public function newSaleReturn()
    {
        $customers = DB::table('customers')->where('status', 1)->where('branch', Auth::user()->branch)->get();
        $invoice_number = DB::table('sales_return')->max('id') + 1;
        $items = DB::table('items')
            ->where('cancel_status', '!=', 1)
            ->whereIn('category', [4, 5, 6])
            ->get();
        return view('salesReturn.new', array('customers' => $customers, 'invoice_number' => $invoice_number, 'items' => $items));
    }

    public function saveSaleReturn(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $saleReturn = DB::table('sales_return')->where('invoice_number', $request->invoice_number)->first();
        if (!empty($saleReturn)) {
            return response()->json(['success' => false, 'message' => 'Sale Invoice already exits..', 'redirectUrl' => '/salesReturn/list'], 200);
        }


        $validator = Validator::make(
            $request->all(),
            [
                'invoice_number' => 'required',
                'invoice_date' => 'required',
                'customer_id' => 'required|numeric',
                'net_total' => 'required|numeric|min:0|not_in:0',
                'net_pcs' => 'required|numeric|min:0|not_in:0',
                'net_qty' => 'required|numeric|min:0|not_in:0',
                'note' => 'required',
            ],
            [
                'invoice_number.required' => 'The Invoice #  is required.',
                'invoice_date.required' => 'The Invoice Date  is required.',
                'customer_id.required' => 'The Customer   is required.',
                'net_total.required' => 'Net Total   is required.',
                'net_pcs.required' => 'Net Pcs   is required.',
                'net_qty.required' => 'Net Qty   is required.',
                'note.required' => 'Notes   is required.',
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
             * Insert Double entry
             *Sale Return A/c Debit
             *Customer A/c  Credit
             */

            $debit = array(
                'voucher_date' => $request->invoice_date,
                'voucher_number' => $request->invoice_number,
                'general_ledger_account_id' => Config::get('constants.SALE_RETURN_ACCOUNT_GENERAL_LEDGER'),
                'note' => $request->note,
                'debit' => $request->net_total,
                'credit' => 0,
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($debit);
            $customer = DB::table('customers')->where('id', $request->customer_id)->first();
            $credit = array(
                'voucher_date' => $request->invoice_date,
                'voucher_number' => $request->invoice_number,
                'general_ledger_account_id' => $customer->general_ledger_account_id,
                'note' => $request->note,
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
                        'note' => $item->name . ' ' . $_detail['item_qty'] . ' @ ' . $_detail['item_price'],
                        'debit' => $_detail['amount'],
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
            $sale_return = array(
                'net_total' => $request->net_total,
                'net_pcs' => $request->net_pcs,
                'net_qty' => $request->net_qty,
                'created_at' => date('Y-m-d H:i:s'),
                'customer_id' => $request->customer_id,
                'items_detail' => serialize($items_detail),
                'invoice_number' => $request->invoice_number,
                'note' => $request->note,
                'invoice_date' => $request->invoice_date,
                'branch' => Auth::user()->branch,
            );
            $sale = DB::table('sales_return')->insert($sale_return);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->invoice_number,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($sale_return),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Sale Return Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Sale Return Invoice added successfully..', 'redirectUrl' => '/salesReturn/list'], 200);
        }
    }


    public function editSaleReturn($id)
    {
        $sale_return = DB::table('sales_return')->where('id', $id)->first();
        $customers = DB::table('customers')->where('status', 1)->where('branch', Auth::user()->branch)->get();
        $items = DB::table('items')
            ->where('cancel_status', '!=', 1)
            ->whereIn('category', [4, 5, 6])
            ->get();
        return view('salesReturn.new', array('saleReturn' => $sale_return, 'customers' => $customers, 'items' => $items));
    }


    public function updateSaleReturn(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');


        $validator = Validator::make(
            $request->all(),
            [
                'invoice_number' => 'required',
                'invoice_date' => 'required',
                'customer_id' => 'required|numeric',
                'net_total' => 'required|numeric|min:0|not_in:0',
                'net_pcs' => 'required|numeric|min:0|not_in:0',
                'net_qty' => 'required|numeric|min:0|not_in:0',
                'note' => 'required',
            ],
            [
                'invoice_number.required' => 'The Invoice #  is required.',
                'invoice_date.required' => 'The Invoice Date  is required.',
                'customer_id.required' => 'The Customer   is required.',
                'net_total.required' => 'Net Total   is required.',
                'net_pcs.required' => 'Net Pcs   is required.',
                'net_qty.required' => 'Net Qty   is required.',
                'note.required' => 'Notes   is required.',
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
             * Insert Double entry
             *Sale Return A/c Debit
             *Customer A/c  Credit
             */
            $this->deleteDoubleEntry($request->invoice_number);
            $this->stockManagementEntryDelete($request->invoice_number);
            $debit = array(
                'voucher_date' => $request->invoice_date,
                'voucher_number' => $request->invoice_number,
                'general_ledger_account_id' => Config::get('constants.SALE_RETURN_ACCOUNT_GENERAL_LEDGER'),
                'note' => $request->note,
                'debit' => $request->net_total,
                'credit' => 0,
                'branch' => Auth::user()->branch,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($debit);
            $customer = DB::table('customers')->where('id', $request->customer_id)->first();
            $credit = array(
                'voucher_date' => $request->invoice_date,
                'voucher_number' => $request->invoice_number,
                'general_ledger_account_id' => $customer->general_ledger_account_id,
                'note' => $request->note,
                'debit' => 0,
                'credit' => $request->net_total,
                'branch' => Auth::user()->branch,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($credit);
            /**
             * Insert Stock Entry for each time
             * 1.get category of item
             *2.get linked general ledger account id from category table
             */
            foreach ($items_detail as $_detail) {
                $item = DB::table('items')->where('id', $_detail['item_id'])->first();
                $category = DB::table('category')->where('id', $item->category)->first();
                $company = DB::table('companyinfo')->first();
                if ($company->stock_calculation == 0) {

                    $credit = array(
                        'voucher_date' => $request->invoice_date,
                        'voucher_number' => $request->invoice_number,
                        'general_ledger_account_id' => $category->general_ledger_account_id,
                        'note' => $item->name . ' ' . $_detail['item_qty'] . ' @ ' . $_detail['item_price'],
                        'debit' => $_detail['amount'],
                        'credit' => 0,
                        'branch' => Auth::user()->branch,
                        'created_at' => date('Y-m-d H:i:s'),
                    );
                    $this->insertDoubleEntry($credit);
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

            $sale_return = array(
                'net_total' => $request->net_total,
                'net_pcs' => $request->net_pcs,
                'net_qty' => $request->net_qty,
                'updated_at' => date('Y-m-d H:i:s'),
                'customer_id' => $request->customer_id,
                'items_detail' => serialize($items_detail),
                'invoice_number' => $request->invoice_number,
                'note' => $request->note,
                'invoice_date' => $request->invoice_date,
                'branch' => Auth::user()->branch,
            );
            $sale = DB::table('sales_return')->where('id', $request->id)->update($sale_return);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->invoice_number,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($sale),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Sale Return Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Sale Return invoive updated successfully..', 'redirectUrl' => '/salesReturn/list'], 200);
        }
    }

    public function deleteSaleReturn($id)
    {
        $sale_return = DB::table('sales_return')->where('id', $id)->first();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $sale_return->invoice_number,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($sale_return),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Sale Return Invoice',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        $this->stockManagementEntryDelete($sale_return->invoice_number);
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $this->deleteDoubleEntry($sale_return->invoice_number);
        DB::table('sales_return')->where('invoice_number', $sale_return->invoice_number)->delete();
        return response()->json(['success' => true, 'message' => 'Sale Return deleted successfully..', 'redirectUrl' => '/salesReturn/list'], 200);
    }


    public function searchSaleReturn(Request $request)
    {
        $Queries = array();
        //   if ($request->isMethod('GET')) {

        //     $request->from_date= $request->get('from_date');
        //     $request->to_date=$request->get('to_date');
        //     $request->invoice_number=$request->get('invoice_number');
        //     $request->customer_id=$request->get('customer_id');
        // }
        if (empty($request->from_date) && empty($request->to_date) && empty($request->customer_id) && empty($request->invoice_number)) {
            return redirect('salesReturn/list');
        }

        $query = DB::table('sales_return');
        $query->join('customers', 'sales_return.customer_id', '=', 'customers.id');
        $query->select('sales_return.*', 'customers.name as customer_name', 'customers.*');
        if (isset($request->invoice_number) && !empty($request->invoice_number)) {
            $Queries['invoice_number'] = $request->invoice_number;
            $query->where('sales_return.invoice_number', 'like', "%$request->invoice_number%");
        }

        if (isset($request->customer_id)) {
            $Queries['customer_id'] = $request->customer_id;
            $query->where('sales_return.customer_id', '=', $request->customer_id);
        }
        if (isset($request->from_date) && isset($request->to_date)) {
            $Queries['from_date'] = $request->from_date;
            $Queries['to_date'] = $request->to_date;
            $query->whereBetween('sales_return.invoice_date', [$request->from_date, $request->to_date]);
        }

        $result = $query->where('sales_return.branch', Auth::user()->branch)->orderByDesc('sales_return.id')->paginate(20);
        $result->appends($Queries);
        $net_total = $query->sum('net_total');
        $net_pcs = $query->sum('net_pcs');
        $net_qty = $query->sum('net_qty');
        $customers = DB::table('customers')->get();
        return view('salesReturn.list', array('saleReturnList' => $result, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'customer_id' => $request->customer_id, 'invoice_number' => $request->invoice_number, 'queries' => $Queries, 'net_total' => $net_total, 'net_pcs' => $net_pcs, 'net_qty' => $net_qty, 'customers' => $customers));
    }



    public function recordPDF($id)
    {
        $sale_return = DB::table('sales_return')
            ->leftJoin('customers', 'sales_return.customer_id', '=', 'customers.id')
            ->where('sales_return.id', $id)
            ->first();
        $items = DB::table('items')->where('branch', Auth()->user()->branch)->get();
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;
        $currency_symbol = $this->getCurrencySymbol($companyinfo->currency_id);
        $data = array('saleReturn' => $sale_return, 'items' => $items, 'companyinfo' => $companyinfo, 'currency_symbol' => $currency_symbol);
        $pdf = PDF::loadView('salesReturn.recordPdf', $data);
        return $pdf->stream('recordPdf.pdf');
    }
    public function saleReturnPagePdf($from_date, $to_date, $customer_id, $invoice_number)
    {

        $query = DB::table('sales_return')
            ->join('customers', 'sales_return.customer_id', '=', 'customers.id')
            ->select('sales_return.*', 'customers.name as customer_name')->where('sales_return.branch', Auth::user()->branch);
        if ($from_date != 'none' && $to_date != 'none') {
            $query->whereBetween('sales_return.invoice_date', [$from_date, $to_date]);
        }
        if ($invoice_number != 'none') {
            $query->where('sales_return.invoice_number', $invoice_number);
        }
        if ($customer_id != 'none') {
            $query->where('customers.id', $customer_id);
        }
        $list = $query->orderByDesc('sales_return.id')->get();
        $net = $query->sum('net_total');
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;
        $currency_symbol = $this->getCurrencySymbol($companyinfo->currency_id);
        $data = array('saleReturnList' => $list, 'net' => $net, 'companyinfo' => $companyinfo, 'currency_symbol' => $currency_symbol);
        $pdf = PDF::loadView('salesReturn.saleReturnPagePdf', $data);
        return $pdf->stream('pagePdf.pdf');
    }

    function getCurrencySymbol($comp_curr_id)
    {
        $currency_data = config('constants.currency');
        foreach ($currency_data as $id => $name) {
            if ($id == $comp_curr_id) {
                return $name;
            }
        }
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
    public function updateDoubleEntry($data)
    {
        /**
         * In case of exception,no need to
         * remove double entry while updated because of
         * record already exisit in table
         * no mettars if no updated
         */
        try {
            DB::table('general_ledger_transactions')
                ->where('voucher_number', $data['voucher_number'])
                ->where('general_ledger_account_id', $data['general_ledger_account_id'])
                ->update($data);
        } catch (\Exception $e) {
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
