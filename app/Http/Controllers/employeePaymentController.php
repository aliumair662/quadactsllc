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
use PDF;


class employeePaymentController extends Controller
{
    public function employeePaymentList()
    {
        $list = DB::table('employee')->rightJoin('employee_payments', 'employee_payments.employee_id', '=', 'employee.id')->orderByDesc('employee_payments.id')->select('employee.id as employee_id','employee.name','employee_payments.*')->paginate(20);
        return view('employeePayment.list', array('employeePayments' => $list));
    }

    public function newEmployeePayment()
    {
        $list = DB::table('employee')->get();
        $voucher_number = DB::table('employee_payments')->count() + 1;
        return view('employeePayment.new', array('employees' => $list, 'voucher_no' => $voucher_number));
    }


    public function saveEmployeePayment(Request $request)
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
             * Insert Double entry
             *Employee A/c Debit
             *Cash A/c  Credit
             */
            $employee = DB::table('employee')->where('id', $request->employee_id)->first();
            $debit = array(
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => $employee->general_ledger_account_id,
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
            $employeePayment = array(
                'voucher_number' => $request->voucher_number,
                'employee_id' => $request->employee_id,
                'voucher_date' => $request->voucher_date,
                'note' => $request->note,
                'note' => $request->note,
                'payment_type' => $request->status,
                'amount' => $request->amount,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $emloyeePaymentId = DB::table('employee_payments')->insertGetId($employeePayment);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->voucher_number,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($employeePayment),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Employee Payments',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Employee Payment added successfully..', 'redirectUrl' => '/employeePayments/list'], 200);
        }
    }



    public function editEmployeePayment($id)
    {
        $employeePayment = DB::table('employee_payments')
            ->where('id', $id)->first();
        $employees = DB::table('employee')->get();
        $voucher_number = DB::table('employee_payments')->count() + 1;
        return view('employeePayment.new', array('employeePayment' => $employeePayment, 'voucher_no' => $voucher_number, 'employees' => $employees));
    }

    public function updateEmployeePayment(Request $request)
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
                'voucher_no.required' => 'The voucher number field is required.',
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
             * Insert Double entry
             *Employee A/c Debit
             *Cash A/c  Credit
             */
            $employee = DB::table('employee')->where('id', $request->employee_id)->first();
            $debit = array(
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => $employee->general_ledger_account_id,
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
            $employeePayment = array(
                'voucher_number' => $request->voucher_number,
                'employee_id' => $request->employee_id,
                'voucher_date' => $request->voucher_date,
                'note' => $request->note,
                'amount' => $request->amount,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            DB::table('employee_payments')->where('id', $request->id)->update($employeePayment);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->voucher_number,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($employeePayment),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Employee Payments',
                'payment_type' => $request->status,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Employee Payment updated successfully..', 'redirectUrl' => '/employeePayments/list'], 200);
        }
    }


    public function deleteEmployeePayment($id)
    {
        $payment = DB::table('employee_payments')->where('id', $id)->first();
        $this->deleteDoubleEntry($payment->voucher_number);
        DB::table('employee_payments')->where('id', $id)->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $payment->voucher_number,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($payment),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Employee Payments',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        return redirect('employeePayments/list');
    }



    public function search(Request $request)
    {
        if (isset($request->from_date) || isset($request->to_date) || isset($request->employee_name) || isset($request->invoice_number)) {

            $query = DB::table('employee');
            $query->rightJoin('employee_payments', 'employee_payments.employee_id', '=', 'employee.id');
            if (isset($request->invoice_number) && !empty($request->invoice_number)) {
                $query->where('employee_payments.voucher_number', 'like', "%$request->invoice_number%");
            } else {

                if (isset($request->employee_name)) {
                    $query->where('employee.name', 'like', "%$request->employee_name%");
                }
                if (isset($request->from_date) && isset($request->to_date)) {
                    $query->whereBetween('employee_payments.voucher_date', [$request->from_date, $request->to_date]);
                }
            }
            $result = $query->orderByDesc('employee_payments.id')->paginate(20);
            return view('employeePayment.list', array('employeePayments' => $result, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'employee_name' => $request->employee_name, 'invoice_number' => $request->invoice_number));
        } else {
            $list = DB::table('employee')->rightJoin('employee_payments', 'employee_payments.employee_id', '=', 'employee.id')->orderByDesc('employee_payments.id')->paginate(20);
            return view('employeePayment.list', array('employeePayments' => $list));
        }
    }



    public function recordPdf($id)
    {
        $employeePayment = DB::table('employee_payments')->leftJoin('employee','employee_payments.employee_id','=','employee.id')->select('employee_payments.*','employee.name')
        ->where('employee_payments.id', $id)->first();

        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo=url('/').$companyinfo->logo;
        $data = array('employeePayment' => $employeePayment,'companyinfo'=>$companyinfo);
        $pdf = PDF::loadView('employeePayment.recordPdf', $data);
        return $pdf->stream('recordPdf.pdf');
    }

    public function pagePdf($from_date,$to_date,$employee_name,$invoice_number)
    {
        $query = DB::table('employee')->rightJoin('employee_payments', 'employee_payments.employee_id', '=', 'employee.id');
        if($from_date != 'none' && $to_date != 'none'){
            $query->whereBetween('employee_payments.voucher_date', [$from_date, $to_date]);
        }
        if($invoice_number != 'none'){
            $query->where('employee_payments.voucher_number', 'like', "%$invoice_number%");
        }
        if($employee_name != 'none'){
            $query->where('employee.name', 'like', "%$employee_name%");
        }
            $list = $query->orderByDesc('employee_payments.id')->get();
        $net = $query->sum('employee_payments.amount');
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo=url('/').$companyinfo->logo;
        $data = array('employeePayments' => $list, 'net' => $net, 'companyinfo' => $companyinfo);
        $pdf = PDF::loadView('employeePayment.employeePagePdf', $data);
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
