<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Auth;
use PDF;

class CustomerRecController extends Controller
{
    public function customer_receipt_List()
    {
        if (Auth::user()->is_admin) {
            $list = DB::table('customers')->rightJoin('customer_receipt', 'customer_receipt.customer', '=', 'customers.id')->select('customer_receipt.*', 'customers.name')
                ->orderByDesc('customer_receipt.id')
                ->paginate(20);
        } else {
            $list = DB::table('customers')->rightJoin('customer_receipt', 'customer_receipt.customer', '=', 'customers.id')->select('customer_receipt.*', 'customers.name')
                ->where('customer_receipt.user_id', Auth::user()->id)
                ->orderByDesc('customer_receipt.id')
                ->paginate(20);
        }
        return view('customer_receipt.list', array('customer_receipts' => $list));
    }

    public function newCustomer_receipt()
    {
        $list = DB::table('customers')->where('branch', Auth::user()->branch)->get();
        $voucher_number = DB::table('customer_receipt')->count() + 1;
        return view('customer_receipt.new', array('customers' => $list, 'voucher_no' => $voucher_number));
    }
    public function store(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');

        $validator = Validator::make(
            $request->all(),
            [
                'voucher_number' => 'required',
                'voucher_date' => 'required',
                'customer_id' => 'required',
                'payment_mode' => 'required',
                'amount' => 'required',
                'note' => 'required'

            ],
            [
                'voucher_number.required' => 'The voucher number field is required.',
                'voucher_date.required' => 'The voucher Date field is required.',
                'customer_id.required' => 'The customer field is required.',
                'payment_mode.required' => 'The payment field is required.',
                'amount.required' => 'The amount field is required.',
                'note.required' => 'The note field is required.'
            ]
        );
        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response, 422);
        } else {
            /**
             * Insert Double entry
             *Cash A/c Debit
             *Customer A/c  Credit
             */
            $customer = DB::table('customers')->where('id', $request->customer_id)->first();
            $debit = array(
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => Config::get('constants.CASH_ACCOUNT_GENERAL_LEDGER'),
                'note' => $request->note,
                'debit' => $request->amount,
                'credit' => 0,
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($debit);
            $credit = array(
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => $customer->general_ledger_account_id,
                'note' => $request->note,
                'debit' => 0,
                'credit' => $request->amount,
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($credit);
            $customer_receipt = array(
                'voucher_number' => $request->voucher_number,
                'customer' => $request->customer_id,
                'received_date' => $request->voucher_date,
                'payment_mode' => $request->payment_mode,
                'check_number' => $request->check_number,
                'bank_name' => $request->bank_name,
                'note' => $request->note,
                'amount' => $request->amount,
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->name,
            );
            $customerReceipt = DB::table('customer_receipt')->insert($customer_receipt);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->voucher_number,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($customer_receipt),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Customer Receipt Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Customer Receipt added successfully..', 'redirectUrl' => '/customerreceipt/list'], 200);
        }
    }

    public function editCustomer_receipt($id)
    {
        $menus = DB::table('customer_receipt')->join('customers', 'customer_receipt.customer', '=', 'customers.id')->where('customer_receipt.id', $id)->select('customers.id as customer_id', 'customers.*', 'customer_receipt.*')->first();
        $list = DB::table('customers')->where('branch', Auth::user()->branch)->get();
        $voucher_number = DB::table('customer_receipt')->count() + 1;
        return view('customer_receipt.new', array('customer_receipt' => $menus, 'voucher_no' => $voucher_number, 'customers' => $list));
    }



    public function updateCustomer_receipt(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');

        $validator = Validator::make(
            $request->all(),
            [
                'voucher_number' => 'required',
                'voucher_date' => 'required',
                'customer_id' => 'required',
                'payment_mode' => 'required',
                'amount' => 'required',
                'note' => 'required'

            ],
            [
                'voucher_number.required' => 'The voucher number field is required.',
                'voucher_date.required' => 'The voucher Date field is required.',
                'customer_id.required' => 'The customer field is required.',
                'payment_mode.required' => 'The payment field is required.',
                'amount.required' => 'The amount field is required.',
                'note.required' => 'The note field is required.'
            ]
        );
        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response, 422);
        } else {
            /**
             * Insert Double entry
             *Cash A/c Debit
             *Customer A/c  Credit
             */
            $this->deleteDoubleEntry($request->voucher_number);
            $customer = DB::table('customers')->where('id', $request->customer_id)->first();
            $debit = array(
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => Config::get('constants.CASH_ACCOUNT_GENERAL_LEDGER'),
                'note' => $request->note,
                'debit' => $request->amount,
                'credit' => 0,
                'branch' => Auth::user()->branch,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            // $this->updateDoubleEntry($debit);
            $this->insertDoubleEntry($debit);
            $credit = array(
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => $customer->general_ledger_account_id,
                'note' => $request->note,
                'debit' => 0,
                'credit' => $request->amount,
                'branch' => Auth::user()->branch,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($credit);
            $customer_receipt = array(
                'voucher_number' => $request->voucher_number,
                'customer' => $request->customer_id,
                'received_date' => $request->voucher_date,
                'payment_mode' => $request->payment_mode,
                'check_number' => $request->check_number,
                'bank_name' => $request->bank_name,
                'note' => $request->note,
                'amount' => $request->amount,
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->name,
            );

            DB::table('customer_receipt')->where('id', $request->id)->update($customer_receipt);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->voucher_number,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($customer_receipt),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Customer Receipt Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Customer Receipt updated successfully..', 'redirectUrl' => '/customerreceipt/list'], 200);
        }
    }


    public function deleteCustomer_receipt($id)
    {
        $receipt = DB::table('customer_receipt')->where('id', $id)->first();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $receipt->voucher_number,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($receipt),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Customer Receipt Invoice',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        $this->deleteDoubleEntry($receipt->voucher_number);
        DB::table('customer_receipt')->where('id', $id)->delete();
        return redirect('customerreceipt/list');
    }

    public function searchCustomerReceipt(Request $request)
    {
        $Queries = array();
        if (empty($request->from_date) && empty($request->to_date) && empty($request->customer_name) && empty($request->invoice_number)) {
            return  redirect('customerreceipt/list');
        }

        $query = DB::table('customers');
        $query->rightJoin('customer_receipt', 'customer_receipt.customer', '=', 'customers.id');
        if (isset($request->invoice_number) && !empty($request->invoice_number)) {
            $Queries['invoice_number'] = $request->invoice_number;
            $query->where('customer_receipt.voucher_number', 'like', "%$request->invoice_number%");
        }

        if (isset($request->from_date) && isset($request->to_date)) {
            $Queries['from_date'] = $request->from_date;
            $Queries['to_date'] = $request->to_date;
            $query->whereBetween('customer_receipt.received_date', [$request->from_date, $request->to_date]);
        }
        if (isset($request->customer_name)) {
            $Queries['customer_name'] = $request->customer_name;
            $query->where('customers.name', 'like', "%$request->customer_name%");
        }

        $result = $query->where('customer_receipt.branch', Auth::user()->branch)->orderByDesc('customer_receipt.id')->paginate(20);
        $result->appends($Queries);
        return view('customer_receipt.list', array('customer_receipts' => $result, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'customer_name' => $request->customer_name, 'invoice_number' => $request->invoice_number));
    }



    public function recordPdf($id)
    {
        $menus = DB::table('customer_receipt')->join('customers', 'customer_receipt.customer', '=', 'customers.id')->where('customer_receipt.id', $id)->first();
        $list = DB::table('customers')->get();
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;

        $currency_symbol = $this->getCurrencySymbol($companyinfo->currency_id);

        $data = array('customer_receipt' => $menus, 'customers' => $list, 'companyinfo' => $companyinfo, 'currency_symbol' => $currency_symbol);
        $pdf = PDF::loadView('customer_receipt.recordPdf', $data);
        return $pdf->stream('recordPdf.pdf');
    }


    public function pagePdf($from_date, $to_date, $customer_name, $invoice_number)
    {

        $query = DB::table('customers')->rightJoin('customer_receipt', 'customer_receipt.customer', '=', 'customers.id')->where('customer_receipt.branch', Auth::user()->branch);
        if ($from_date != 'none' && $to_date != 'none') {
            $query->whereBetween('customer_receipt.received_date', [$from_date, $to_date]);
        }
        if ($invoice_number != 'none') {
            $query->where('customer_receipt.voucher_number', 'like', "%$invoice_number%");
        }
        if ($customer_name != 'none') {
            $query->where('customers.name', 'like', "%$customer_name%");
        }
        $list = $query->orderByDesc('customer_receipt.id')->get();
        $net = $query->orderByDesc('customer_receipt.id')->sum('amount');

        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;

        $currency_symbol = $this->getCurrencySymbol($companyinfo->currency_id);

        $data = array('customer_receipts' => $list, 'net' => $net, 'companyinfo' => $companyinfo, 'currency_symbol' => $currency_symbol);
        $pdf = PDF::loadView('customer_receipt.receiptPagePdf', $data);
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

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
}
