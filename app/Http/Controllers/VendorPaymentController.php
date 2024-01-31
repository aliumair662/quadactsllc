<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use PDF;

class VendorPaymentController extends Controller
{
    public function venoderPaymentList()
    {
        $list = DB::table('vendors')->rightJoin('vendor_payment', 'vendor_payment.vendor', '=', 'vendors.id')
            ->orderByDesc('vendor_payment.id')
            ->paginate(20);
        return view('vendorpayment.list', array('vendor_payments' => $list));
    }
    public function newVenoderPayment()
    {
        $list = DB::table('vendors')->where('status', 1)->get();
        $voucher_number = DB::table('vendor_payment')->count() + 1;
        return view('vendorpayment.new', array('vendors' => $list, 'voucher_no' => $voucher_number));
    }
    public function store(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');

        $validator = Validator::make(
            $request->all(),
            [
                'voucher_number' => 'required',
                'voucher_date' => 'required',
                'vendor_id' => 'required',
                'payment_mode' => 'required',
                'amount' => 'required',
                'note' => 'required'

            ],
            [
                'voucher_number.required' => 'The voucher number field is required.',
                'voucher_date.required' => 'The voucher Date field is required.',
                'vendor_id.required' => 'The vendor field is required.',
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
             *Vendor A/c Debit
             *Cash A/c  Credit
             */
            $vendor = DB::table('vendors')->where('id', $request->vendor_id)->first();
            $debit = array(
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => $vendor->general_ledger_account_id,
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
                'general_ledger_account_id' => Config::get('constants.CASH_ACCOUNT_GENERAL_LEDGER'),
                'note' => $request->note,
                'debit' => 0,
                'credit' => $request->amount,
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($credit);
            $vendorpayment = array(
                'voucher_number' => $request->voucher_number,
                'vendor' => $request->vendor_id,
                'received_date' => $request->voucher_date,
                'payment_mode' => $request->payment_mode,
                'check_number' => $request->check_number,
                'about_bank' => $request->bank_name,
                'note' => $request->note,
                'amount' => $request->amount
            );
            DB::table('vendor_payment')->insert($vendorpayment);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->voucher_number,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($vendorpayment),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Vendor Payment Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Vendor Payment added successfully..', 'redirectUrl' => '/vendorpayment/list'], 200);
        }
    }


    public function editVendorPayment($id)
    {
        $vendorpayment = DB::table('vendor_payment')
            ->where('id', $id)->first();
        $list = DB::table('vendors')->get();
        $voucher_number = DB::table('vendor_payment')->count() + 1;
        return view('vendorpayment.new', array('vendorpayment' => $vendorpayment, 'voucher_no' => $voucher_number, 'vendors' => $list));
    }
    public function update(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');

        $validator = Validator::make(
            $request->all(),
            [
                'voucher_number' => 'required',
                'voucher_date' => 'required',
                'vendor_id' => 'required',
                'payment_mode' => 'required',
                'amount' => 'required',
                'note' => 'required'

            ],
            [
                'voucher_number.required' => 'The voucher number field is required.',
                'voucher_date.required' => 'The voucher Date field is required.',
                'vendor_id.required' => 'The customer field is required.',
                'payment_mode.required' => 'The payment field is required.',
                'amount.required' => 'The amount field is required.',
                'note.required' => 'The note field is required.'
            ]
        );
        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response, 422);
        } else {

            $vendor = DB::table('vendors')->where('id', $request->vendor_id)->first();
            $debit = array(
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => $vendor->general_ledger_account_id,
                'note' => $request->note,
                'debit' => $request->amount,
                'credit' => 0,
                'branch' => Auth::user()->branch,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            $this->updateDoubleEntry($debit);
            $credit = array(
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => Config::get('constants.CASH_ACCOUNT_GENERAL_LEDGER'),
                'note' => $request->note,
                'debit' => 0,
                'credit' => $request->amount,
                'branch' => Auth::user()->branch,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            $this->updateDoubleEntry($credit);
            $vendorpayment = array(
                'voucher_number' => $request->voucher_number,
                'vendor' => $request->vendor_id,
                'received_date' => $request->voucher_date,
                'payment_mode' => $request->payment_mode,
                'check_number' => $request->check_number,
                'about_bank' => $request->bank_name,
                'note' => $request->note,
                'amount' => $request->amount
            );
            DB::table('vendor_payment')->where('id', $request->id)->update($vendorpayment);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->voucher_number,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($vendorpayment),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Vendor Payment Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Vendor Payment updated successfully..', 'redirectUrl' => '/vendorpayment/list'], 200);
        }
    }

    public function deleteVendorPayment($id)
    {
        $payment = DB::table('vendor_payment')->where('id', $id)->first();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $payment->voucher_number,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($payment),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Vendor Payment Invoice',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        $this->deleteDoubleEntry($payment->voucher_number);
        $menu = DB::table('vendor_payment')->where('id', $id)->delete();
        return redirect('vendorpayment/list');
    }



    public function searchVendorPayment(Request $request)
    {
        if (isset($request->from_date) || isset($request->to_date) || isset($request->vendor_name) || isset($request->invoice_number)) {

            $query = DB::table('vendors');
            $query->rightJoin('vendor_payment', 'vendor_payment.vendor', '=', 'vendors.id');
            if (isset($request->invoice_number) && !empty($request->invoice_number)) {
                $query->where('vendor_payment.voucher_number', 'like', "%$request->invoice_number%");
            } else {

                if (isset($request->vendor_name)) {
                    $query->where('vendors.name', 'like', "%$request->vendor_name%");
                }
                if (isset($request->from_date) && isset($request->to_date)) {
                    $query->whereBetween('vendor_payment.received_date', [$request->from_date, $request->to_date]);
                }
            }
            $result = $query->orderByDesc('vendor_payment.id')->paginate(20);
            return view('vendorpayment.list', array('vendor_payments' => $result, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'vendor_name' => $request->vendor_name, 'invoice_number' => $request->invoice_number));
        } else {
            $list = DB::table('vendors')->rightJoin('vendor_payment', 'vendor_payment.vendor', '=', 'vendors.id')->orderByDesc('vendor_payment.id')
                ->paginate(20);
            return view('vendorpayment.list', array('vendor_payments' => $list));
        }
    }



    public function recordPdf($id)
    {
        $vendorpayment = DB::table('vendor_payment')
            ->leftJoin('vendors', 'vendor_payment.vendor', '=', 'vendors.id')
            ->select('vendor_payment.*', 'vendors.name as name')
            ->where('vendor_payment.id', $id)->first();
            $companyinfo = DB::table('companyinfo')->first();
            $companyinfo->logo=url('/').$companyinfo->logo;
        $data = array('vendorpayment' => $vendorpayment,'companyinfo'=>$companyinfo);
        $pdf = PDF::loadView('vendorpayment.recordPdf', $data);
        return $pdf->stream('recordPdf.pdf');
    } 

    public function pagePdf($from_date,$to_date,$vendor_name,$invoice_number)
    {

        
        $query = DB::table('vendor_payment')->leftJoin('vendors', 'vendor_payment.vendor', '=', 'vendors.id')
        ->select('vendor_payment.*', 'vendors.name as name');
        if($from_date != 'none' && $to_date != 'none'){
            $query->where('vendor_payment.voucher_number', 'like', "%$invoice_number%");
        }
        if($invoice_number != 'none'){
            $query->where('vendor_payment.voucher_number', 'like', "%$invoice_number%");
        }
        if($vendor_name != 'none'){
            $query->where('vendors.name', 'like', "%$vendor_name%");
        }

        $list = $query->orderByDesc('vendor_payment.id')->get();
        $net =$query->sum('amount');
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo=url('/').$companyinfo->logo;
        $data = array('vendor_payments' => $list,'net'=>$net,'companyinfo'=>$companyinfo);
        $pdf = PDF::loadView('vendorpayment.vendorPayPagePdf', $data);
        return $pdf->stream('pagePdf.pdf');
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
