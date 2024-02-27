<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use PDF;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class SaleController extends Controller
{
    public function saleList()
    {
        $Queries = array();
        $list = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->select('sales.*', 'customers.name as customer_name')->where('sales.branch', Auth::user()->branch)
            ->orderByDesc('sales.id')
            ->paginate(20);
        // ->where('branch', Auth::user()->branch)
        $net_total = DB::table('sales')->where('branch', Auth::user()->branch)->sum('net_total');
        $net_profit = DB::table('sales')->sum('net_profit');
        $net_qty = DB::table('sales')->where('branch', Auth::user()->branch)->sum('net_qty');
        $net_pcs = DB::table('sales')->where('branch', Auth::user()->branch)->sum('net_pcs');
        $customers = DB::table('customers')->where('branch', Auth::user()->branch)->get();

        foreach ($list as $item) {
            $item->items_detail = unserialize($item->items_detail);

            foreach ($item->items_detail as &$data) {
                $item_data = DB::table('items')->where('id', $data['item_id'])->first();
                $data['item_name'] = $item_data->name;
            }
            unset($data);
        }
        // ->where('is_admin', 0)
        $users = DB::table('users')->where('status', 1)->get();
        return view('sales.list', array('salelist' => $list, 'queries' => $Queries, 'customers' => $customers, 'net_total' => $net_total, 'net_pcs' => $net_pcs, 'net_qty' => $net_qty, 'users' => $users, 'net_profit' => $net_profit));
    }
    public function newsale()
    {
        if (Auth::user()->is_admin) {
            $customers = DB::table('customers')
                ->where('status', 1)
                // ->where('branch', Auth::user()->branch)
                ->get();
        } else {
            $customers = DB::table('customers')
                ->where('status', 1)
                ->where('user_id', Auth::user()->id)
                ->get();
        }
        $invoice_number = DB::table('sales')->max('id') + 1;
        // ->where('branch', Auth()->user()->branch)
        $items = DB::table('items')
            ->where('cancel_status', '!=', 1)
            ->whereIn('category', [4, 5, 6])
            ->get();
        // ->where('is_admin', 0)
        $users = DB::table('users')->where('status', 1)->get();
        return view('sales.new', array('customers' => $customers, 'invoice_number' => $invoice_number, 'items' => $items, 'users' => $users));
    }
    public function store(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $sale = DB::table('sales')->where('invoice_number', $request->invoice_number)->first();
        if (!empty($sale)) {
            return response()->json(['success' => false, 'message' => 'Sale Invoice already exits..', 'redirectUrl' => '/sales/list'], 200);
        }


        $validator = Validator::make(
            $request->all(),
            [
                'invoice_number' => 'required',
                'invoice_date' => 'required',
                'customer_id' => 'required|numeric',
                'net_total' => 'required|numeric|min:0|not_in:0',
                // 'note' => 'required',
                // 'net_pcs' => 'required|numeric|min:0|not_in:0',
                'net_qty' => 'required|numeric|min:0|not_in:0',
            ],
            [
                'invoice_number.required' => 'The Invoice #  is required.',
                'invoice_date.required' => 'The Invoice Date  is required.',
                'customer_id.required' => 'The Customer   is required.',
                'net_total.required' => 'Net Total   is required.',
                // 'note.required' => 'Notes   is required.',
                // 'net_pcs.required' => 'Pcs is required.',
                'net_qty.required' => 'Qty is required.',
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
            // $item_pcss = $request->item_pcs;
            $amounts = $request->amount;
            $total_purchase_amount = $request->total_purchase_amount;
            $item_purchase_price = $request->item_purchase_price;
            $i = 0;
            foreach ($item_ids as $item) {
                $itemid = $item_ids[$i];
                $price = $item_prices[$i];
                $qty = $item_qtys[$i];
                // $pcs = $item_pcss[$i];
                $amount = $amounts[$i];
                $purchase_price = $total_purchase_amount[$i];
                $item_price = $item_purchase_price[$i];
                if ($amount > 0) {
                    $items_detail[] = array(
                        'item_id' => $itemid,
                        'item_price' => $price,
                        'item_qty' => $qty,
                        'amount' => $amount,
                        'total_purchase_amount' => $purchase_price,
                        'item_purchase_price' => $item_price,
                        // 'item_pcs' => $pcs,
                    );
                }

                $i++;
            }
            /**
             * Insert Double entry
             *Customer A/c Debit
             *Sale A/c  Credit
             */
            $customer = DB::table('customers')->where('id', $request->customer_id)->first();
            $debit = array(
                'voucher_date' => $request->invoice_date,
                'voucher_number' => $request->invoice_number,
                'general_ledger_account_id' => $customer->general_ledger_account_id,
                'note' => isset($request->note) ? $request->note : null,
                'debit' => $request->net_total,
                'credit' => 0,
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($debit);
            if (isset($request->recieved_amount)) {
                $customerCredit = array(
                    'voucher_date' => $request->invoice_date,
                    'voucher_number' => $request->invoice_number,
                    'general_ledger_account_id' => $customer->general_ledger_account_id,
                    'note' => isset($request->note) ? $request->note : null,
                    'debit' => 0,
                    'credit' => $request->recieved_amount,
                    'branch' => Auth::user()->branch,
                    'created_at' => date('Y-m-d H:i:s'),
                );
                $this->insertDoubleEntry($customerCredit);
            }
            $credit = array(
                'voucher_date' => $request->invoice_date,
                'voucher_number' => $request->invoice_number,
                'general_ledger_account_id' => Config::get('constants.SALE_ACCOUNT_GENERAL_LEDGER'),
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
             * Stock Amount will be reduced according to purchase price of the item
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
                        'debit' => 0,
                        'credit' => $_detail['amount'],
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
                                'transaction_type' => '-',
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
                // else {

                $stock  = array(
                    'voucher_date' => $request->invoice_date,
                    'voucher_number' => $request->invoice_number,
                    'transaction_type' => '-',
                    'general_ledger_account_id' => $category->general_ledger_account_id,
                    'item_qty' => $_detail['item_qty'],
                    'item_id' => $_detail['item_id'],
                    'branch' => Auth::user()->branch,
                    'created_at' => date('Y-m-d H:i:s'),
                );
                $this->stockManagementEntry($stock);
                // }
            }
            $user =  DB::table('users')->where('id', $request->user_id)->first();
            $sale = array(
                'net_total' => $request->net_total,
                'gross_amount' => $request->gross_amount,
                'discount_amount' => $request->discount_amount,
                'created_at' => date('Y-m-d H:i:s'),
                'customer_id' => $request->customer_id,
                'items_detail' => serialize($items_detail),
                'invoice_number' => $request->invoice_number,
                'note' => isset($request->note) ? $request->note : null,
                // 'net_pcs' => $request->net_pcs,
                'net_qty' => $request->net_qty,
                'invoice_date' => $request->invoice_date,
                'branch' => Auth::user()->branch,
                'recieved_amount' => $request->recieved_amount,
                'balance_amount' => $request->balance_amount,
                'gross_purchase_amount' => $request->gross_purchase_amount,
                'user_id' => $request->user_id,
                'sale_user_name' => isset($user->name) ? $user->name : null,
                'net_profit' => $request->net_profit,
                'note_html' => isset($request->html_semantic) ? $request->html_semantic : null
            );
            $idForPdf =  DB::table('sales')->insertGetId($sale);
            /***
             * add entry to transaction log
             *
             */
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->invoice_number,
                'transaction_action' => 'Add',
                'transaction_detail' => serialize($sale),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Sale Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);

            return response()->json(['success' => true, 'message' => 'Sale Invoice added successfully..', 'redirectUrl' => '/sales/list', 'print' => "/sales/pdf/{$idForPdf}"], 200);
        }
    }
    public function edit($id)
    {
        $sale = DB::table('sales')->where('id', $id)->first();
        if (Auth::user()->is_admin) {
            $customers = DB::table('customers')
                ->where('status', 1)
                // ->where('branch', Auth::user()->branch)
                ->get();
        } else {
            $customers = DB::table('customers')
                ->where('status', 1)
                ->where('user_id', Auth::user()->id)
                ->get();
        }
        $items = DB::table('items')
            ->where('cancel_status', '!=', 1)
            ->get();
        $users = DB::table('users')->where('status', 1)->where('is_admin', 0)->get();
        return view('sales.new', array('sale' => $sale, 'customers' => $customers, 'items' => $items, 'create_Invoice' => 0, 'users' => $users));
    }
    public function update(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $validator = Validator::make(
            $request->all(),
            [
                'invoice_number' => 'required',
                'invoice_date' => 'required',
                'customer_id' => 'required|numeric',
                'net_total' => 'required|numeric|min:0|not_in:0',
                // 'note' => 'required',
                // 'net_pcs' => 'required|numeric|min:0|not_in:0',
                'net_qty' => 'required|numeric|min:0|not_in:0',
            ],
            [
                'invoice_number.required' => 'The Invoice #  is required.',
                'invoice_date.required' => 'The Invoice Date  is required.',
                'customer_id.required' => 'The Customer   is required.',
                'net_total.required' => 'Net Total   is required.',
                // 'note.required' => 'Notes   is required.',
                // 'total_pcs.required' => 'Pcs is required.',
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
            // $item_pcss = $request->item_pcs;
            $amounts = $request->amount;
            $total_purchase_amount = $request->total_purchase_amount;
            $item_purchase_price = $request->item_purchase_price;
            $i = 0;
            foreach ($item_ids as $item) {
                $itemid = $item_ids[$i];
                $price = $item_prices[$i];
                // $pcs = $item_pcss[$i];
                $qty = $item_qtys[$i];
                $amount = $amounts[$i];
                $purchase_price = $total_purchase_amount[$i];
                $item_price = $item_purchase_price[$i];
                if ($amount > 0) {
                    $items_detail[] = array(
                        'item_id' => $itemid,
                        'item_price' => $price,
                        'item_qty' => $qty,
                        'amount' => $amount,
                        'total_purchase_amount' => $purchase_price,
                        'item_purchase_price' => $item_price,
                        // 'item_pcs' => $pcs,
                    );
                }

                $i++;
            }


            /**Delete first all entries from General Ledger Transactions Table
             * Insert Double entry
             *Customer A/c Debit
             *Sale A/c  Credit
             */
            $this->deleteDoubleEntry($request->invoice_number);
            $this->stockManagementEntryDelete($request->invoice_number);
            $customer = DB::table('customers')->where('id', $request->customer_id)->first();
            $debit = array(
                'voucher_date' => $request->invoice_date,
                'voucher_number' => $request->invoice_number,
                'general_ledger_account_id' => $customer->general_ledger_account_id,
                'note' => isset($request->note) ? $request->note : null,
                'debit' => $request->net_total,
                'credit' => 0,
                'branch' => Auth::user()->branch,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($debit);
            if (isset($request->recieved_amount)) {
                $customerCredit = array(
                    'voucher_date' => $request->invoice_date,
                    'voucher_number' => $request->invoice_number,
                    'general_ledger_account_id' => $customer->general_ledger_account_id,
                    'note' => $request->note,
                    'debit' => 0,
                    'credit' => $request->recieved_amount,
                    'branch' => Auth::user()->branch,
                    'created_at' => date('Y-m-d H:i:s'),
                );
                $this->insertDoubleEntry($customerCredit);
            }
            $credit = array(
                'voucher_date' => $request->invoice_date,
                'voucher_number' => $request->invoice_number,
                'general_ledger_account_id' => Config::get('constants.SALE_ACCOUNT_GENERAL_LEDGER'),
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
                        'debit' => 0,
                        'credit' => $_detail['amount'],
                        'branch' => Auth::user()->branch,
                        'updated_at' => date('Y-m-d H:i:s'),
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
                                'transaction_type' => '-',
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
                    'transaction_type' => '-',
                    'general_ledger_account_id' => $category->general_ledger_account_id,
                    'item_qty' => $_detail['item_qty'],
                    'item_id' => $_detail['item_id'],
                    'branch' => Auth::user()->branch,
                    'created_at' => date('Y-m-d H:i:s'),
                );
                $this->stockManagementEntry($stock);
            }
            $user =  DB::table('users')->where('id', $request->user_id)->first();
            $sale = array(
                'net_total' => $request->net_total,
                'gross_amount' => $request->gross_amount,
                'discount_amount' => $request->discount_amount,
                // 'net_pcs' => $request->net_pcs,
                'net_qty' => $request->net_qty,
                'updated_at' => date('Y-m-d H:i:s'),
                'customer_id' => $request->customer_id,
                'items_detail' => serialize($items_detail),
                'invoice_number' => $request->invoice_number,
                'note' => isset($request->note) ? $request->note : null,
                'invoice_date' => $request->invoice_date,
                'branch' => Auth::user()->branch,
                'recieved_amount' => $request->recieved_amount,
                'balance_amount' => $request->balance_amount,
                'gross_purchase_amount' => $request->gross_purchase_amount,
                'user_id' => $request->user_id ?? null,
                'sale_user_name' => isset($user->name) ? $user->name : null,
                'net_profit' => $request->net_profit,
                'note_html' => isset($request->html_semantic) ? $request->html_semantic : null
            );
            DB::table('sales')->where('id', $request->id)->update($sale);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->invoice_number,
                'transaction_action' => 'Update',
                'transaction_detail' => serialize($sale),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Sale Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Sale Updated added successfully..', 'redirectUrl' => '/sales/list'], 200);
        }
    }

    public function delete($id)
    {
        $sale = DB::table('sales')->where('id', $id)->first();
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $this->deleteDoubleEntry($sale->invoice_number);
        DB::table('sales')->where('invoice_number', $sale->invoice_number)->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $sale->invoice_number,
            'transaction_action' => 'Delete',
            'transaction_detail' => serialize($sale),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Sale Invoice',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        $this->stockManagementEntryDelete($sale->invoice_number);
        return response()->json(['success' => true, 'message' => 'Sale deleted successfully..', 'redirectUrl' => '/sales/list'], 200);
    }


    // Search Sales
    public function searchSales(Request $request)
    {
        $Queries = array();

        if (empty($request->from_date) && empty($request->to_date) && empty($request->customer_id) && empty($request->invoice_number) && empty($request->user_id)) {
            return redirect('sales/list');
        }
        $query = DB::table('sales');
        $query->join('customers', 'sales.customer_id', '=', 'customers.id');
        $query->select('sales.*', 'customers.name as customer_name', 'customers.*');

        if (!empty($request->from_date) && !empty($request->to_date)) {
            $Queries['from_date'] = $request->from_date;
            $Queries['to_date'] = $request->to_date;
            $query->whereBetween('sales.invoice_date', [$request->from_date, $request->to_date]);
        }
        if (!empty($request->invoice_number)) {
            $Queries['invoice_number'] = $request->invoice_number;
            $query->where('sales.invoice_number', 'like', "%$request->invoice_number%");
        }
        if (!empty($request->customer_id)) {
            $Queries['customer_id'] = $request->customer_id;
            $query->where('sales.customer_id', '=', $request->customer_id);
        }
        if (!empty($request->user_id)) {
            $Queries['user_id'] = $request->user_id;
            $query->where('sales.user_id', '=', $request->user_id);
        }
        $list = $query->orderByDesc('sales.id')->paginate(20);
        $list->appends($Queries);
        $net_total = $query->sum('net_total');
        $net_qty = $query->sum('net_qty');
        $net_pcs = $query->sum('net_pcs');
        $customers = DB::table('customers')->get();
        // ->where('is_admin', 0)
        $users = DB::table('users')->where('status', 1)->get();
        foreach ($list as $item) {
            $item->items_detail = unserialize($item->items_detail);

            foreach ($item->items_detail as &$data) {
                $item_data = DB::table('items')->where('id', $data['item_id'])->first();
                $data['item_name'] = $item_data->name;
            }
            unset($data);
        }
        return view('sales.list', array('salelist' => $list, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'customer_id' => $request->customer_id, 'invoice_number' => $request->invoice_number, 'customers' => $customers, 'net_total' => $net_total, 'net_pcs' => $net_pcs, 'net_qty' => $net_qty, 'user_id' => $request->user_id, 'users' => $users));
    }



    // PDF generator
    public function recordPDF(Request $request, $id)
    {
        $sale = DB::table('sales')->where('sales.id', $id)
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->first();
        $items = DB::table('items')->where('branch', Auth()->user()->branch)->get();
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;

        $currency_symbol = $this->getCurrencySymbol($companyinfo->currency_id);

        $qrCodeString = $this->generateQrCode($request->url());

        $data =  array('sale' => $sale, 'items' => $items, 'companyinfo' => $companyinfo, 'qrCodeString' => $qrCodeString, 'currency_symbol' => $currency_symbol);

        if ($companyinfo->auto_print_invoice == 0) {
            $pdf = PDF::loadView('sales.salePdf', $data);
        } else {
            $customPaper = array(20, 0, 800.00, 280.80);
            $pdf = PDF::loadView('sales.saleThermalPdf', $data)->setPaper($customPaper, 'landscape');
        }
        return $pdf->stream('salePdf.pdf');
    }

    public function salePagePDF($from_date, $to_date, $customer_id, $invoice_number)
    {
        $query = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->select('sales.*', 'customers.name as customer_name')->where('sales.branch', Auth::user()->branch);
        if ($from_date != 'none' && $to_date != 'none') {
            $query->whereBetween('sales.invoice_date', [$from_date, $to_date]);
        }
        if ($invoice_number != 'none') {
            $query->where('sales.invoice_number', 'like', "%$invoice_number%");
        }
        if ($customer_id != 'none') {
            $query->where('customers.id', "$customer_id");
        }

        $list = $query->orderByDesc('sales.id')->get();

        foreach ($list as $item) {
            $item->items_detail = unserialize($item->items_detail);

            foreach ($item->items_detail as &$data) {
                $item_data = DB::table('items')->where('id', $data['item_id'])->first();
                $data['item_name'] = $item_data->name;
            }
            unset($data);
        }
        $net = $query->orderByDesc('sales.id')->sum('net_total');
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;

        $currency_symbol = $this->getCurrencySymbol($companyinfo->currency_id);

        $data = array(
            'salelist' => $list,
            'companyinfo' => $companyinfo,
            'net' => $net,
            'currency_symbol' => $currency_symbol
        );

        $pdf = PDF::loadView('sales.salePagePdf', $data);
        return $pdf->stream('pagePdf.pdf');
    }

    public function customerledger()
    {
        /* $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        DB::table('sales')->where('id',$id)->delete();
        return response()->json(['success' => true, 'message' => 'Sale deleted successfully..', 'redirectUrl' => '/sales/list'],200);*/
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
            DB::table('general_ledger_transactions')->where('voucher_number', $data->voucher_number)->delete();
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

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }

    public function stockManagementEntryDelete($voucher_number)
    {
        try {
            DB::table('general_inventory_transactions')->where('voucher_number', $voucher_number)->delete();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'redirectUrl' => '/sales/list'], 200);
        }
    }

    // *********************  Quotation Crud ********************
    public function quotationList()
    {
        $Queries = array();
        if (Auth::user()->is_admin) {
            // ->where('branch', Auth::user()->branch)
            $list = DB::table('quotation')
                ->orderBy('id', 'DESC')
                ->paginate(20);
            $net_total = DB::table('quotation')->where('branch', Auth::user()->branch)->sum('net_total');
            $net_qty = DB::table('quotation')->where('branch', Auth::user()->branch)->sum('net_qty');
            $net_profit = DB::table('quotation')->sum('net_profit');
        } else {
            // ->where('branch', Auth::user()->branch)
            $list = DB::table('quotation')->where('user_id', Auth::user()->id)
                ->orderBy('id', 'DESC')
                ->paginate(20);
            $net_total = DB::table('quotation')->where('branch', Auth::user()->branch)->where('user_id', Auth::user()->id)->sum('net_total');
            $net_qty = DB::table('quotation')->where('branch', Auth::user()->branch)->where('user_id', Auth::user()->id)->sum('net_qty');
            $net_profit = DB::table('quotation')->where('user_id', Auth::user()->id)->sum('net_profit');
        }
        // ->where('is_admin', 0)
        $users = DB::table('users')->where('status', 1)->get();

        return view('quotation/list', array('lists' => $list, 'queries' => $Queries, 'net_total' => $net_total, 'net_qty' => $net_qty, 'users' => $users, 'net_profit' => $net_profit));
    }

    public function PackagesList()
    {

        $list = $list = DB::table('quotation_packages')
            // ->where('branch', Auth::user()->branch)
            ->orderBy('id', 'ASC')
            ->paginate(20);

        return view('packages/list', array('lists' => $list));
    }

    public function newQuotation()
    {
        if (Auth::user()->is_admin) {
            $customers = DB::table('customers')
                ->where('status', 1)
                // ->where('branch', Auth::user()->branch)
                ->get();
        } else {
            $customers = DB::table('customers')
                ->where('status', 1)
                ->where('user_id', Auth::user()->id)
                ->get();
        }
        $invoice_number = DB::table('quotation')->max('id') + 1;
        // ->where('branch', Auth()->user()->branch)
        $items = DB::table('items')
            ->where('cancel_status', '!=', 1)
            ->whereIn('category', [4, 5, 6])
            ->get();
        $response = ['invoice_number' => $invoice_number, 'items' => $items, 'customers' => $customers];
        if (request()->input('pkg_id')) {
            $pkg_data = DB::table('quotation_packages')->where('id', request()->input('pkg_id'))->first();
            $pkg_data->customer_id = '';
            $pkg_data->invoice_number = Config::get('constants.QUOTATION_INVOICE_PREFIX') . $invoice_number;
            $pkg_data->pkg_id = request()->input('pkg_id');
            $response['quotation'] = $pkg_data;
        }
        $file_name = request()->input('file_name') ?? null;
        return view('quotation/new', $response);
        // return view('quotation/new', array($response, 'file_name' => $file_name));
    }

    public function newPackage()
    {
        if (Auth::user()->is_admin) {
            $customers = DB::table('customers')
                ->where('status', 1)
                // ->where('branch', Auth::user()->branch)
                ->get();
        } else {
            $customers = DB::table('customers')
                ->where('status', 1)
                ->where('user_id', Auth::user()->id)
                ->get();
        }
        $invoice_number = DB::table('quotation_packages')->max('id') + 1;

        $items = DB::table('items')
            ->where('cancel_status', '!=', 1)
            ->whereIn('category', [4, 5, 6])
            ->get();
        return view('packages/new', array('invoice_number' => $invoice_number, 'items' => $items, 'customers' => $customers));
    }

    public function saveQuotation(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $sale = DB::table('quotation')->where('invoice_number', $request->invoice_number)->first();
        if (!empty($sale)) {
            return response()->json(['success' => false, 'message' => 'Quotation Invoice already exits..', 'redirectUrl' => '/quotation/list'], 200);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'invoice_number' => 'required',
                'invoice_date' => 'required',
                'customer_id' => 'required|numeric',
                'net_total' => 'required|numeric|min:0|not_in:0',
                // 'note' => 'required',
                // 'net_pcs' => 'required|numeric|min:0|not_in:0',
                'net_qty' => 'required|numeric|min:0|not_in:0',
            ],
            [
                'invoice_number.required' => 'The Invoice #  is required.',
                'invoice_date.required' => 'The Invoice Date  is required.',
                'customer_id.required' => 'The Customer is required.',
                'net_total.required' => 'Net Total   is required.',
                // 'note.required' => 'Notes   is required.',
                // 'net_pcs.required' => 'Pcs is required.',
                'net_qty.required' => 'Qty is required.',
            ]
        );
        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {
            $items_detail = array();
            $item_ids = $request->item_id;
            $item_prices = $request->item_price;
            $item_qtys = $request->item_qty;
            $total_purchase_amount = $request->total_purchase_amount;
            $item_purchase_price = $request->item_purchase_price;
            // $item_pcss = $request->item_pcs;
            $amounts = $request->amount;
            $i = 0;
            foreach ($item_ids as $item) {
                $itemid = $item_ids[$i];
                $price = $item_prices[$i];
                $qty = $item_qtys[$i];
                $purchase_price = $total_purchase_amount[$i];
                $item_price = $item_purchase_price[$i];
                // $pcs = $item_pcss[$i];
                $amount = $amounts[$i];

                if ($amount > 0) {
                    $items_detail[] = array(
                        'item_id' => $itemid,
                        'item_price' => $price,
                        'item_qty' => $qty,
                        'amount' => $amount,
                        'total_purchase_amount' => $purchase_price,
                        'item_purchase_price' => $item_price,
                        // 'item_pcs' => $pcs,
                    );
                }

                $i++;
            }
            $customer = DB::table('customers')->where('id', $request->customer_id)->first();

            $sale = array(
                'net_total' => $request->net_total,
                'gross_amount' => $request->gross_amount,
                'discount_amount' => $request->discount_amount,
                'created_at' => date('Y-m-d H:i:s'),
                'customer_id' => $request->customer_id,
                'customer_name' => $customer->name,
                'items_detail' => serialize($items_detail),
                'invoice_number' => $request->invoice_number,
                'note' => isset($request->note) ? $request->note : null,
                // 'net_pcs' => $request->net_pcs,
                'net_qty' => $request->net_qty,
                'invoice_date' => $request->invoice_date,
                'branch' => Auth::user()->branch,
                'cancel_status' => false,
                'user_id' => Auth::user()->id,
                'quotation_user_name' => Auth::user()->name,
                'gross_purchase_amount' => $request->gross_purchase_amount,
                'net_profit' => $request->net_profit,
                'note_html' => isset($request->html_semantic) ? $request->html_semantic : null

            );

            $idForPdf = DB::table('quotation')->insertGetId($sale);
            /***
             * add entry to transaction log
             *
             */
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->invoice_number,
                'transaction_action' => 'Add',
                'transaction_detail' => serialize($sale),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Quotation',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);

            return response()->json(['success' => true, 'message' => 'Quotation added successfully..', 'redirectUrl' => "/quotation/list", 'print' => "/quotation/pdf/{$idForPdf}"], 200);
        }
    }

    public function savePackage(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $sale = DB::table('quotation_packages')->where('invoice_number', $request->invoice_number)->first();
        if (!empty($sale)) {
            return response()->json(['success' => false, 'message' => 'Package already exits..', 'redirectUrl' => '/packages/list'], 200);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'invoice_number' => 'required',
                'invoice_date' => 'required',
                // 'customer_id' => 'required|numeric',
                'net_total' => 'required|numeric|min:0|not_in:0',
                'note' => 'required',
                // 'net_pcs' => 'required|numeric|min:0|not_in:0',
                'net_qty' => 'required|numeric|min:0|not_in:0',
            ],
            [
                'invoice_number.required' => 'The Invoice #  is required.',
                'invoice_date.required' => 'The Invoice Date  is required.',
                // 'customer_id.required' => 'The Customer is required.',
                'net_total.required' => 'Net Total   is required.',
                'note.required' => 'Notes   is required.',
                // 'net_pcs.required' => 'Pcs is required.',
                'net_qty.required' => 'Qty is required.',
            ]
        );
        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {
            $items_detail = array();
            $item_ids = $request->item_id;
            $item_prices = $request->item_price;
            $item_qtys = $request->item_qty;
            $total_purchase_amount = $request->total_purchase_amount;
            $item_purchase_price = $request->item_purchase_price;
            // $item_pcss = $request->item_pcs;
            $amounts = $request->amount;
            $i = 0;
            foreach ($item_ids as $item) {
                $itemid = $item_ids[$i];
                $price = $item_prices[$i];
                $qty = $item_qtys[$i];
                $purchase_price = $total_purchase_amount[$i];
                $item_price = $item_purchase_price[$i];
                // $pcs = $item_pcss[$i];
                $amount = $amounts[$i];

                if ($amount > 0) {
                    $items_detail[] = array(
                        'item_id' => $itemid,
                        'item_price' => $price,
                        'item_qty' => $qty,
                        'amount' => $amount,
                        'total_purchase_amount' => $purchase_price,
                        'item_purchase_price' => $item_price,
                        // 'item_pcs' => $pcs,
                    );
                }

                $i++;
            }
            // $customer = DB::table('customers')->where('id', $request->customer_id)->first();

            $sale = array(
                'net_total' => $request->net_total,
                'gross_amount' => $request->gross_amount,
                'discount_amount' => $request->discount_amount,
                'created_at' => date('Y-m-d H:i:s'),
                // 'customer_id' => $request->customer_id,
                // 'customer_name' => $customer->name,
                'items_detail' => serialize($items_detail),
                'invoice_number' => $request->invoice_number,
                'note' => $request->note,
                // 'net_pcs' => $request->net_pcs,
                'net_qty' => $request->net_qty,
                'invoice_date' => $request->invoice_date,
                'branch' => Auth::user()->branch,
                'cancel_status' => false,
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->name,
                'gross_purchase_amount' => $request->gross_purchase_amount,
                'net_profit' => $request->net_profit,
                'note_html' => isset($request->html_semantic) ? $request->html_semantic : null
            );

            $idForPdf = DB::table('quotation_packages')->insert($sale);
            /***
             * add entry to transaction log
             *
             */
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->invoice_number,
                'transaction_action' => 'Add',
                'transaction_detail' => serialize($sale),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Quotation',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            // , 'print' => "/packages/pdf/{$idForPdf}"
            return response()->json(['success' => true, 'message' => 'Package added successfully..', 'redirectUrl' => '/packages/list'], 200);
        }
    }

    public function editQuotation($id)
    {
        $sale = DB::table('quotation')->where('id', $id)->first();
        if (Auth::user()->is_admin) {
            $customers = DB::table('customers')
                ->where('status', 1)
                // ->where('branch', Auth::user()->branch)
                ->get();
        } else {
            $customers = DB::table('customers')
                ->where('status', 1)
                ->where('user_id', Auth::user()->id)
                ->get();
        }
        $items = DB::table('items')
            ->where('cancel_status', '!=', 1)
            ->whereIn('category', [4, 5, 6])
            ->get();

        return view('quotation/new', array('quotation' => $sale, 'items' => $items, 'customers' => $customers));
    }

    public function editPackage($id)
    {
        $package = DB::table('quotation_packages')->where('id', $id)->first();
        if (Auth::user()->is_admin) {
            $customers = DB::table('customers')
                ->where('status', 1)
                // ->where('branch', Auth::user()->branch)
                ->get();
        } else {
            $customers = DB::table('customers')
                ->where('status', 1)
                ->where('user_id', Auth::user()->id)
                ->get();
        }
        $items = DB::table('items')
            ->where('cancel_status', '!=', 1)
            ->whereIn('category', [4, 5, 6])
            ->get();
        return view('quotation/new', array('quotation' => $package, 'items' => $items, 'customers' => $customers));
    }

    public function updateQuotation(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $companyBusinessType = DB::table('companyinfo')->first();

        $validator = Validator::make(
            $request->all(),
            [
                'invoice_number' => 'required',
                'invoice_date' => 'required',
                'customer_id' => 'required|numeric',
                'net_total' => 'required|numeric|min:0|not_in:0',
                // 'note' => 'required',
                // 'net_pcs' => 'required|numeric|min:0|not_in:0',
                'net_qty' => 'required|numeric|min:0|not_in:0',
            ],
            [
                'invoice_number.required' => 'The Invoice #  is required.',
                'invoice_date.required' => 'The Invoice Date  is required.',
                'customer_id.required' => 'The Customer is required.',
                'net_total.required' => 'Net Total   is required.',
                // 'note.required' => 'Notes   is required.',
                // 'net_pcs.required' => 'Pcs is required.',
                'net_qty.required' => 'Qty is required.',
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
            $total_purchase_amount = $request->total_purchase_amount;
            $item_purchase_price = $request->item_purchase_price;
            // $item_pcss = $request->item_pcs;
            $amounts = $request->amount;
            $i = 0;
            foreach ($item_ids as $item) {
                $itemid = $item_ids[$i];
                $price = $item_prices[$i];
                $qty = $item_qtys[$i];
                $purchase_price = $total_purchase_amount[$i];
                $item_price = $item_purchase_price[$i];
                // $pcs = $item_pcss[$i];
                $amount = $amounts[$i];


                if ($amount > 0) {
                    $items_detail[] = array(
                        'item_id' => $itemid,
                        'item_price' => $price,
                        'item_qty' => $qty,
                        'amount' => $amount,
                        'total_purchase_amount' => $purchase_price,
                        'item_purchase_price' => $item_price,
                        // 'item_pcs' => $pcs,
                    );
                }

                $i++;
            }
            $customer = DB::table('customers')->where('id', $request->customer_id)->first();
            $quotation = array(
                'net_total' => $request->net_total,
                'gross_amount' => $request->gross_amount,
                'discount_amount' => $request->discount_amount,
                'updated_at' => date('Y-m-d H:i:s'),
                'customer_id' => $request->customer_id,
                'customer_name' => $customer->name,
                'items_detail' => serialize($items_detail),
                'invoice_number' => $request->invoice_number,
                'note' => isset($request->note) ? $request->note : null,
                // 'net_pcs' => $request->net_pcs,
                'net_qty' => $request->net_qty,
                'invoice_date' => $request->invoice_date,
                'branch' => Auth::user()->branch,
                'cancel_status' => false,
                'user_id' => Auth::user()->id,
                'quotation_user_name' => Auth::user()->name,
                'gross_purchase_amount' => $request->gross_purchase_amount,
                'net_profit' => $request->net_profit,
                'note_html' => isset($request->html_semantic) ? $request->html_semantic : null
            );
            DB::table('quotation')->where('id', $request->id)->update($quotation);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->invoice_number,
                'transaction_action' => 'Update',
                'transaction_detail' => serialize($quotation),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Quotation',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Quotation Updated added successfully..', 'redirectUrl' => '/quotation/list'], 200);
        }
    }


    public function deleteQuotation($id)
    {
        $quotation = DB::table('quotation')->where('id', $id)->first();
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        DB::table('quotation')->where('invoice_number', $quotation->invoice_number)->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $quotation->invoice_number,
            'transaction_action' => 'Delete',
            'transaction_detail' => serialize($quotation),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Quotation',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        return response()->json(['success' => true, 'message' => 'Quotation deleted successfully..', 'redirectUrl' => '/quotation/list'], 200);
    }

    public function deletePackage($id)
    {
        $package = DB::table('quotation_packages')->where('id', $id)->first();
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        DB::table('quotation_packages')->where('invoice_number', $package->invoice_number)->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $package->invoice_number,
            'transaction_action' => 'Delete',
            'transaction_detail' => serialize($package),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Package',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        return response()->json(['success' => true, 'message' => 'Package deleted successfully..', 'redirectUrl' => '/packages/list'], 200);
    }

    public function searchQuotation(Request $request)
    {
        $Queries = array();
        if (empty($request->from_date) && empty($request->to_date) &&  empty($request->customer_name) && empty($request->invoice_number) && empty($request->user_id)) {
            return redirect('/quotation/list');
        }
        $query = DB::table('quotation');

        if (!empty($request->from_date) && !empty($request->to_date)) {
            $Queries['from_date'] = $request->from_date;
            $Queries['to_date'] = $request->to_date;
            $query->whereBetween('invoice_date', [$request->from_date, $request->to_date]);
        }
        if (!empty($request->invoice_number)) {
            $Queries['invoice_number'] = $request->invoice_number;
            $query->where('invoice_number', 'like', "%$request->invoice_number%");
        }
        if (!empty($request->customer_name)) {
            $Queries['customer_name'] = $request->customer_name;
            $query->where('customer_name', '=', $request->customer_name);
        }
        if (!empty($request->user_id)) {
            $Queries['user_id'] = $request->user_id;
            $query->where('quotation.user_id', '=', $request->user_id);
        }
        $list = $query->orderByDesc('id')->paginate(20);
        $list->appends($Queries);
        $net_total = $query->sum('net_total');
        $net_qty = $query->sum('net_qty');
        // ->where('is_admin', 0)
        $users = DB::table('users')->where('status', 1)->get();
        // $net_pcs = $query->sum('net_pcs');
        return view('quotation/list', array('lists' => $list, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'customer_name' => $request->customer_name, 'invoice_number' => $request->invoice_number, 'net_total' => $net_total, 'net_qty' => $net_qty, 'user_id' => $request->user_id, 'users' => $users));
    }

    public function searchPackages(Request $request)
    {
        $Queries = array();
        if (empty($request->from_date) && empty($request->to_date) &&  empty($request->customer_name) && empty($request->invoice_number)) {
            return redirect('packages/list');
        }
        $query = DB::table('quotation_packages');

        if (!empty($request->from_date) && !empty($request->to_date)) {
            $Queries['from_date'] = $request->from_date;
            $Queries['to_date'] = $request->to_date;
            $query->whereBetween('invoice_date', [$request->from_date, $request->to_date]);
        }
        if (!empty($request->invoice_number)) {
            $Queries['invoice_number'] = $request->invoice_number;
            $query->where('invoice_number', 'like', "%$request->invoice_number%");
        }
        // if (!empty($request->customer_name)) {
        //     $Queries['customer_name'] = $request->customer_name;
        //     $query->where('customer_name', '=', $request->customer_name);
        // }
        $list = $query->orderByDesc('id')->paginate(20);
        $list->appends($Queries);
        // $net_total = $query->sum('net_total');
        // $net_qty = $query->sum('net_qty');
        // $net_pcs = $query->sum('net_pcs');
        return view('packages/list', array('lists' => $list, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'customer_name' => $request->customer_name, 'invoice_number' => $request->invoice_number));
    }
    public function quotationPagePdf($from_date, $to_date, $customer_name, $invoice_number)
    {
        $query = DB::table('quotation')
            ->where('branch', Auth::user()->branch);
        if ($from_date != 'none' && $to_date != 'none') {
            $query->whereBetween('invoice_date', [$from_date, $to_date]);
        }
        if ($invoice_number != 'none') {
            $query->where('invoice_number', 'like', "%$invoice_number%");
        }
        if ($customer_name != 'none') {
            $query->where('customer_name', "$customer_name");
        }

        $list = $query->orderBy('id', 'ASC')->get();
        $net = $query->orderByDesc('id')->sum('net_total');
        // $pcs = $query->orderByDesc('id')->sum('net_pcs');
        $qty = $query->orderByDesc('id')->sum('net_qty');
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;

        $currency_symbol = $this->getCurrencySymbol($companyinfo->currency_id);

        $data = array(
            'quotationlist' => $list,
            'companyinfo' => $companyinfo,
            'net' => $net,
            // 'pcs' => $pcs,
            'qty' => $qty,
            'currency_symbol' => $currency_symbol
        );

        $pdf = PDF::loadView('quotation.quotationPagePdf', $data);
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

    public function quotationRecordPdf(Request $request, $id)
    {
        $quotation = DB::table('quotation')->where('quotation.id', $id)
            ->leftJoin('customers', 'quotation.customer_id', '=', 'customers.id')
            ->first();

        $items = DB::table('items')
            // ->where('branch', Auth()->user()->branch)
            ->get();
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;

        $currency_symbol = $this->getCurrencySymbol($companyinfo->currency_id);

        $qrCodeString = $this->generateQrCode($request->url());

        $data =  array('quotation' => $quotation, 'items' => $items, 'companyinfo' => $companyinfo, 'qrCodeString' => $qrCodeString, 'currency_symbol' => $currency_symbol);

        if ($companyinfo->auto_print_invoice == 0) {
            $pdf = PDF::loadView('quotation.quotationPdf', $data);
        } else {
            $customPaper = array(20, 0, 800.00, 280.80);
            $pdf = PDF::loadView('quotation.quotationThermalPdf', $data)->setPaper($customPaper, 'landscape');
        }
        return $pdf->stream('quotationPdf.pdf');
    }
    public function generateQrCode($url)
    {
        // $pdf_data = 'https://wa.me/?text=' . $url;
        $qrCodeString = base64_encode(QrCode::format('svg')->size(100)->errorCorrection('H')->generate($url));

        return $qrCodeString;
    }

    public function quatationToSalesInvoice($id)
    {
        $sale = DB::table('quotation')->where('id', $id)->first();

        $items_detail = unserialize($sale->items_detail);
        $new_item_detail = array();
        foreach ($items_detail as $detail) {
            $item_id = $detail['item_id'];
            $items = DB::table('items')->where('id', $item_id)->first();
            $sale_price =  $items->sele_price;
            $detail['sale_price'] = $sale_price;
            $new_item_detail[] = $detail;
        }
        $sale->items_detail =  serialize($new_item_detail);
        // ->where('branch', Auth::user()->branch)
        $customers = DB::table('customers')->where('status', 1)->get();
        $items = DB::table('items')
            // ->where('branch', Auth()->user()->branch)
            ->whereIn('category', [4, 5, 6])
            ->get();
        $invoice_number = DB::table('sales')->max('id') + 1;
        $sale->new_invoice_number = Config::get('constants.SALE_INVOICE_PREFIX') . $invoice_number;
        $sale->salesOrder = 'yes';
        // $sale->customer_id = 0;
        $sale->cash_customer_name = '';
        $sale->recieved_amount  = '';
        $sale->balance_amount   = '';
        $users = DB::table('users')->where('status', 1)->get();
        return view('sales.new', array('sale' => $sale, 'customers' => $customers, 'items' => $items, 'create_Invoice' => 1, 'users' => $users));
    }

    public function cancelQuotation($id)
    {
        $quotation = DB::table('quotation')->where('id', $id)->first();
        if (empty($quotation)) {
            return response()->json(['success' => false, 'message' => "Quotation Invoice doesn't exits..", 'redirectUrl' => '/quotation/list'], 200);
        }
        $quotation_cancel = array(
            'cancel_status' => true,
        );
        DB::table('quotation')->where('id', $id)->update($quotation_cancel);
        return redirect('/quotation/list');
    }
}
