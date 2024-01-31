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


class advanceReturnController extends Controller
{
    public function list()
    {
        $list = DB::table('employee_advance_return')->leftJoin('employee', 'employee_advance_return.employee_id', '=', 'employee.id')->select('employee_advance_return.*', 'employee.name')->where('employee_advance_return.branch', Auth::user()->branch)->orderByDesc('employee_advance_return.id')->paginate(5);
        return view('advanceReturn.list', array('lists' => $list));
    }

    public function new()
    {
        $list = DB::table('employee')->where('branch', Auth::user()->branch)->get();
        $voucher_number = DB::table('employee_advance_return')->count() + 1;
        return view('advanceReturn.new', array('voucher_no' => $voucher_number, 'employees' => $list));
    }

    public function store(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');

        $validator = Validator::make(
            $request->all(),
            [
                'voucher_number' => 'required',
                'voucher_date' => 'required',
                'employee_id' => 'required',
                'amount' => 'required',
                'note' => 'required'

            ],
            [
                'voucher_number.required' => 'The voucher number field is required.',
                'voucher_date.required' => 'The voucher Date field is required.',
                'employee_id.required' => 'The Customer field is required.',
                'amount.required' => 'The amount field is required.',
                'note.required' => 'The note field is required.'
            ]
        );
        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response, 422);
        } else {
            /**
             * Insert Double entry later on
             *Employee A/c Debit
             *Cash A/c  Credit
             */
            $employeePayment = array(
                'voucher_number' => $request->voucher_number,
                'employee_id' => $request->employee_id,
                'voucher_date' => $request->voucher_date,
                'note' => $request->note,
                'branch' => Auth::user()->branch,
                'amount' => $request->amount,
                'payment_type' => $request->status,
                'created_at' => date('Y-m-d H:i:s'),
            );
            DB::table('employee_advance_return')->insertGetId($employeePayment);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->voucher_number,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($employeePayment),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Advance Return',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Advance Return added successfully..', 'redirectUrl' => '/advanceReturn/list'], 200);
        }
    }

    public function edit($id)
    {
        $employeePayment = DB::table('employee_advance_return')
            ->where('id', $id)->first();
        $employees = DB::table('employee')->get();
        // $voucher_number = DB::table('employee_advance_return')->count() + 1;
        return view('advanceReturn.new', array('advanceReturn' => $employeePayment, 'employees' => $employees));
    }

    public function update(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');

        $validator = Validator::make(
            $request->all(),
            [
                'voucher_number' => 'required',
                'voucher_date' => 'required',
                'employee_id' => 'required',
                'amount' => 'required',
                'note' => 'required'

            ],
            [
                'voucher_number.required' => 'The voucher number field is required.',
                'voucher_date.required' => 'The voucher Date field is required.',
                'employee_id.required' => 'The Customer field is required.',
                'amount.required' => 'The amount field is required.',
                'note.required' => 'The note field is required.'
            ]
        );
        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response, 422);
        } else {
            /**
             * Insert Double entry Later on
             *Employee A/c Debit
             *Cash A/c  Credit
             */
            $employeePayment = array(
                'voucher_number' => $request->voucher_number,
                'employee_id' => $request->employee_id,
                'voucher_date' => $request->voucher_date,
                'note' => $request->note,
                'branch' => Auth::user()->branch,
                'amount' => $request->amount,
                'payment_type' => $request->status,
                'created_at' => date('Y-m-d H:i:s'),
            );
            DB::table('employee_advance_return')->where('id', $request->id)->update($employeePayment);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->voucher_number,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($employeePayment),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Advance Return',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Advance Return updated successfully..', 'redirectUrl' => '/advanceReturn/list'], 200);
        }
    }


    public function delete($id)
    {
        $payment = DB::table('employee_advance_return')->where('id', $id)->first();
        //$this->deleteDoubleEntry($payment->voucher_number);
        DB::table('employee_advance_return')->where('id', $id)->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $payment->voucher_number,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($payment),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Advance Return',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        return redirect('advanceReturn/list');
    }


    public function searchAdvance(Request $request)
    {
        if (isset($request->from_date) || isset($request->to_date) || isset($request->employee_name) || isset($request->invoice_number)) {

            $query = DB::table('employee_advance_return');
            $query->leftJoin('employee', 'employee_advance_return.employee_id', '=', 'employee.id');
            $query->select('employee_advance_return.*', 'employee.name');
            if (isset($request->invoice_number) && !empty($request->invoice_number)) {
                $query->where('employee_advance_return.voucher_number', 'like', "%$request->invoice_number%");
            } else {

                if (isset($request->employee_name)) {
                    $query->where('employee.name', 'like', "%$request->employee_name%");
                }
                if (isset($request->from_date) && isset($request->to_date)) {
                    $query->whereBetween('employee_advance_return.voucher_date', [$request->from_date, $request->to_date]);
                }
            }
            $result = $query->where('employee_advance_return.branch', Auth::user()->branch)->orderByDesc('employee_advance_return.id')->paginate(5);
            return view('advanceReturn.list', array('lists' => $result, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'employee_name' => $request->employee_name, 'invoice_number' => $request->invoice_number));
        } else {
            $list = DB::table('employee_advance_return')->leftJoin('employee', 'employee_advance_return.employee_id', '=', 'employee.id')->select('employee_advance_return.*', 'employee.name')->where('employee_advance_return.branch', Auth::user()->branch)->orderByDesc('employee_advance_return.id')->paginate(5);
            return view('advanceReturn.list', array('lists' => $list));
        }
    }


    public function advanceReturnPagePDf($from_date,$to_date,$employee_name,$invoice_number)
    {

        $query = DB::table('employee_advance_return')->leftJoin('employee', 'employee_advance_return.employee_id', '=', 'employee.id')->select('employee_advance_return.*', 'employee.name')->where('employee_advance_return.branch', Auth::user()->branch);
        if($from_date != 'none' && $to_date != 'none'){
            $query->whereBetween('employee_advance_return.voucher_date', [$from_date, $to_date]);
        }
        if($invoice_number != 'none'){
            $query->where('employee_advance_return.voucher_number', 'like', "%$invoice_number%");
        }
        if($employee_name != 'none'){
            $query->where('employee.name', 'like', "%$employee_name%");
        }
        $list = $query->orderByDesc('employee_advance_return.id')->get();
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;
        $data = array(
            'lists' => $list,
            'companyinfo' => $companyinfo,
         
        );

        $pdf = PDF::loadView('advanceReturn.advanceReturnPagePdf', $data);
        return $pdf->stream('pagePdf.pdf');
  
    }



public function advanceReturnPdf($id)
{
    $record = DB::table('employee_advance_return')->leftJoin('employee', 'employee_advance_return.employee_id', '=', 'employee.id')->select('employee_advance_return.*', 'employee.name')->where('employee_advance_return.id', $id)->first();
    $companyinfo = DB::table('companyinfo')->first();
    $companyinfo->logo = url('/') . $companyinfo->logo;

    $data =  array('record' => $record, 'companyinfo' => $companyinfo);
    $pdf = PDF::loadView('advanceReturn.advanceReturnPdf', $data);
    return $pdf->stream('advanceReturnPdf.pdf');
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
