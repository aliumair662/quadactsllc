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
use Carbon\CarbonPeriod;
use PDF;

class employeeController extends Controller
{
    //
    public function employeeList()
    {
        $employeelist = DB::table('employee')
            ->join('department', 'employee.department', '=', 'department.id')
            ->select('employee.*', 'department.name as department_name')
            ->where('employee.branch',Auth::user()->branch)
            ->orderByDesc('employee.id')
            ->paginate(20);
        foreach ($employeelist as $list) {
            $journal_entry_rule = $this->getAccountjournalentryrule($list->general_ledger_account_id);
            $journal_sum_rule = 'debit - credit';
            if ($journal_entry_rule == 'credit') {
                $journal_sum_rule = 'credit - debit';
            }
            $endingBalance = DB::table('general_ledger_transactions')
                ->where('voucher_date', '<=', Carbon::now()->format('Y-m-d'))
                ->where('general_ledger_account_id', $list->general_ledger_account_id)
                ->where('branch', Auth::user()->branch)
                ->sum(\DB::raw($journal_sum_rule));
            $list->balance = $endingBalance;
        }

        return view('employee.employeelist', array('employee' => $employeelist));
    }
    public function newEmployee()
    {
        $departmentlist = DB::table('department')->get();
        $itemlist = DB::table('items')->where('branch',Auth::user()->branch)->get();
        return view('employee.addemployee', array('department' => $departmentlist, 'items' => $itemlist));
    }
    public function storeEmployee(Request $request)
    {

        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $validator = Validator::make(
            $request->all(),
            [
                'code' => 'required',
                'name' => 'required|min:3|max:500|unique:employee',
                'phone' => 'required',
                'cnic' => 'required',
                'salery' => ['required', 'numeric'],

            ],
            [
                'code.required' => 'The code field is required.',
                'name.required' => 'The employee name field is required.',
                'phone.required' => 'The phone No field is required.',
                'cnic.required' => 'The CNIC field is required.',
                'salery.required' => 'The salery field is required.',

            ]
        );

        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {
            $file_path = Config::get('constants.EMPLOYEE_DEFAULT_PIC');
            if ($files = $request->file('pic')) {
                $destinationPath = public_path('/employee_pic/'); // upload path
                $profileImage = date('YmdHis') . "." . $files->getClientOriginalExtension();
                $files->move($destinationPath, $profileImage);
                $file_path = '/employee_pic/' . $profileImage;
            }
            $account = array(
                'name' => $request->name,
                'account_type_id' => Config::get('constants.EMPLOYEE_CHART_OF_ACCOUNT_TYPE_ID'),
                'chart_of_account_id' => Config::get('constants.EMPLOYEE_CHART_OF_ACCOUNT_ID'),
                'chart_of_accounts_category_id' => Config::get('constants.EMPLOYEE_CHART_OF_ACCOUNT_CATEGORY_ID'),
                'created_at' => date('Y-m-d H:i:s'),
                'branch' => Auth::user()->branch,
            );
            $general_ledger_account_id = DB::table('general_ledger_accounts')->insertGetId($account);
            $employee = array(
                'code' => $request->code,
                'name' => $request->name,
                'pic' => $file_path,
                'phone' => $request->phone,
                'cnic' => $request->cnic,
                'salery' => $request->salery,
                'advance' => $request->advance,
                'employee_type' => $request->employee_type,
                'department' => $request->department,
                'production_method' => $request->production_method,
                'general_ledger_account_id' => $general_ledger_account_id,
                'linked_items' => serialize(array()),
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),

            );
            $employee_id = DB::table('employee')->insertGetId($employee);
            /***
             * Add Labour Production Rate For each item they produce
             */
            if ($employee_id) {
                $items = $request->item_id;
                $rates = $request->rate;
                $additional_rates = $request->additional_rate;

                if (!empty($items)) {
                    $i = 0;
                    foreach ($items as $item) {
                        $itemid = $items[$i];
                        $rate = $rates[$i];
                        $additional_rate = $additional_rates[$i];
                        if ($rate > 0) {
                            $linked_items = array(
                                'itemid' => $itemid,
                                'rate' => $rate,
                                'additional_rate' => (!empty($additional_rate)) ? $additional_rate : 0,
                                'employee_id' => $employee_id,
                                'created_at' => date('Y-m-d H:i:s'),
                            );
                            DB::table('employee_production_rates')->insert($linked_items);
                        }
                        $i++;
                    }
                }
            }
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $employee_id,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($employee),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Employee',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'employee added successfully..', 'redirectUrl' => '/employee/employeeList'], 200);
        }
    }
    public function editEmployee($id)
    {
        $employee = DB::table('employee')->where('id', $id)->first();
        $employee->employee_production_rates = DB::table('employee_production_rates')->where('employee_id', $employee->id)->get();
        $department = DB::table('department')->get();
        $item = DB::table('items')->where('branch',Auth::user()->branch)->get();
        return view('employee.addemployee', array('items' => $item, 'department' => $department, 'employee' => $employee));
    }
    public function updateEmployee(Request $request)
    {
        $emailexist = DB::table('employee')->where('name', $request->name)->where('id', '!=', $request->id)->first();
        if (!empty($emailexist)) {
            return response()->json(['success' => false, 'message' => 'The employee name has already been taken.Please try another one.', 'redirectUrl' => ''], 200);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'code' => 'required',
                'name' => 'required|min:3|max:500',
                'phone' => 'required',
                'cnic' => 'required',
                'salery' => ['required', 'numeric'],

            ],
            [
                'code.required' => 'The code field is required.',
                'name.required' => 'The employee name field is required.',
                'phone.required' => 'The phone No field is required.',
                'cnic.required' => 'The CNIC field is required.',
                'salery.required' => 'The salery field is required.',

            ]
        );

        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {
            $file_path = Config::get('constants.EMPLOYEE_DEFAULT_PIC');
            if ($files = $request->file('pic')) {
                $destinationPath = public_path('/employee_pic/'); // upload path
                $profileImage = date('YmdHis') . "." . $files->getClientOriginalExtension();
                $files->move($destinationPath, $profileImage);
                $file_path = '/employee_pic/' . $profileImage;
            }
            $employeeInfo = DB::table('employee')->where('id', $request->id)->first();
            $account = array(
                'name' => $request->name,
                'account_type_id' => Config::get('constants.EMPLOYEE_CHART_OF_ACCOUNT_TYPE_ID'),
                'chart_of_account_id' => Config::get('constants.EMPLOYEE_CHART_OF_ACCOUNT_ID'),
                'chart_of_accounts_category_id' => Config::get('constants.EMPLOYEE_CHART_OF_ACCOUNT_CATEGORY_ID'),
                'created_at' => date('Y-m-d H:i:s'),
                'branch' => Auth::user()->branch,
            );
            DB::table('general_ledger_accounts')->where('id', $employeeInfo->general_ledger_account_id)->update($account);
            $employee = array(
                'code' => $request->code,
                'name' => $request->name,
                'pic' => $file_path,
                'phone' => $request->phone,
                'cnic' => $request->cnic,
                'salery' => $request->salery,
                'employee_type' => $request->employee_type,
                'advance' => $request->advance,
                'department' => $request->department,
                'production_method' => $request->production_method,
                'linked_items' => serialize(array()),
                'branch' => Auth::user()->branch,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            DB::table('employee')->where('id', $request->id)->update($employee);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->id,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($employee),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Employee',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            $employee_production_rates = DB::table('employee_production_rates')->where('employee_id', $request->id)->first();
            if (!empty($employee_production_rates)) {
                DB::table('employee_production_rates')->where('employee_id', $request->id)->delete();
            }
            $items = $request->item_id;
            $rates = $request->rate;
            $additional_rates = $request->additional_rate;
            if (!empty($items)) {
                $i = 0;
                foreach ($items as $item) {
                    $itemid = $items[$i];
                    $rate = $rates[$i];
                    $additional_rate = $additional_rates[$i];

                    if ($rate > 0) {
                        $linked_items = array(
                            'itemid' => $itemid,
                            'rate' => $rate,
                            'additional_rate' => (!empty($additional_rate)) ? $additional_rate : 0,
                            'employee_id' => $request->id,
                            'updated_at' => date('Y-m-d H:i:s'),
                        );
                        DB::table('employee_production_rates')->insert($linked_items);
                    }
                    $i++;
                }
            }


            return response()->json(['success' => true, 'message' => 'Employee update successfully..', 'redirectUrl' => '/employee/employeeList'], 200);
        }
    }
    public function deleteEmployee($id)
    {
        $employee = DB::table('employee')
            ->where('id', $id)
            ->first();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $employee->id,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($employee),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Employee',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        $general_transaction = DB::table('general_ledger_transactions')
            ->where('general_ledger_account_id', $employee->general_ledger_account_id)
            ->first();
        if (isset($general_transaction)) {
            $employeeInactive = array(
                'status' => 0,

            );
            $ledgerAccount = array(
                'status' => 0,
            );
            $employeeInactive['updated_at'] = date('Y-m-d H:i:s');
            $ledgerAccount['updated_at'] = date('Y-m-d H:i:s');
            $ledgerAccount = DB::table('general_ledger_accounts')->where('id', $employee->general_ledger_account_id)->update($ledgerAccount);
            $employeeInactive = DB::table('employee')->where('id', $id)->update($employeeInactive);
            return redirect('employee/employeeList');
        } else {
            // $employeeInactive = DB::table('employee')->where('id', $id)->delete();
            // return redirect('vendor/list');
            $employee_production_rates = DB::table('employee_production_rates')->where('employee_id', $id)->first();
            if (!empty($employee_production_rates)) {
                DB::table('employee_production_rates')->where('employee_id', $id)->delete();
            }
            DB::table('employee')->where('id', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Employee deleted successfully..', 'redirectUrl' => '/employee/employeeList'], 200);
        }
    }









    public function getItemEmployeeRates($id)
    {
        $employee_production_rates = DB::table('employee_production_rates')
            ->join('employee', 'employee_production_rates.employee_id', '=', 'employee.id')
            ->select('employee_production_rates.*', 'employee.name as employee_name', 'employee.production_method as production_method')
            ->where('employee_production_rates.itemid', $id)
            ->get();

        if (!empty($employee_production_rates)) {
            return response()->json(['success' => true, 'data' => $employee_production_rates], 200);
        } else {
            return response()->json(['success' => false, 'data' => array()], 200);
        }
    }


    public function searchEmployee(Request $request)
    {
        // $Queries = array();
        // if(isset($request->emloyee_name)){
        //     $Queries['employee_name'] = $request->employee_name;
        // }
        $employeelist = DB::table('employee')
            ->join('department', 'employee.department', '=', 'department.id')
            ->select('employee.*', 'department.name as department_name')
            ->where('employee.name', 'like', "%$request->employee_name%")
            ->where('employee.branch',Auth::user()->branch)
            ->orderByDesc('employee.id')
            ->paginate(20);
            // $employeelist->appends($Queries);
            foreach ($employeelist as $list) {
                $journal_entry_rule = $this->getAccountjournalentryrule($list->general_ledger_account_id);
                $journal_sum_rule = 'debit - credit';
                if ($journal_entry_rule == 'credit') {
                    $journal_sum_rule = 'credit - debit';
                }
                $endingBalance = DB::table('general_ledger_transactions')
                    ->where('voucher_date', '<=', Carbon::now()->format('Y-m-d'))
                    ->where('general_ledger_account_id', $list->general_ledger_account_id)
                    ->where('branch', Auth::user()->branch)
                    ->sum(\DB::raw($journal_sum_rule));
                $list->balance = $endingBalance;
            }

        return view('employee.employeelist', array('employee' => $employeelist, 'employee_name' => $request->employee_name));
    }


    public function getAccountjournalentryrule($general_ledger_account_id)
    {
        $account = DB::table('general_ledger_accounts')
            ->where('id', '=', $general_ledger_account_id)
            ->first();
        $chart_of_account = DB::table('chart_of_accounts')
            ->where('id', '=', $account->chart_of_account_id)
            ->first();
        return $chart_of_account->journal_entry_rule;
    }



    // Employee Attendee
    public function employeeAttendeelist()
    {
        $list = DB::table('employee_attendee_sheet')
            ->leftJoin('employee', 'employee_attendee_sheet.employee_id', '=', 'employee.id')
            ->where('employee_attendee_sheet.branch',Auth::user()->branch)
            ->select('employee_attendee_sheet.*', 'employee.name')
            ->paginate(10);
        return view('employeeAttendee.employeeAttendeeList', array('lists' => $list));
    }

    public function editAttendeeSheet($id)
    {
        $list = DB::table('employee_attendee_sheet')
            ->leftJoin('employee', 'employee_attendee_sheet.employee_id', '=', 'employee.id')
            ->select('employee_attendee_sheet.*', 'employee.name')
            ->where('branch',Auth::user()->branch)
            ->where('employee_attendee_sheet.id', $id)
            ->first();
        return view('employeeAttendee.editAttendee', array('record' => $list));
    }

    public function updateAttendee(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');


        $validator = Validator::make(
            $request->all(),
            [
                'voucher_number' => 'required',
                'voucher_date' => 'required',
                'total_present' => 'required',
                'total_absent' => 'required',
                'total_leave' => 'required',
                'half_days' => 'required',
                'holidays' => 'required',
                'net_working_days' => 'required',
                'basic_salary' => 'required',
                'net_salary' => 'required|numeric|min:0|not_in:0',
                'name' => 'required',

            ],
            [
                'voucher_number.required' => 'The Invoice #  is required.',
                'voucher_date.required' => 'The voucher_date is required.',
                'total_present.required' => 'The Total Present  is required.',
                'total_absent.required' => 'Total Absent is required.',
                'total_leave.required' => 'Total Leave is required.',
                'half_days.required' => 'Half Days is required.',
                'net_working_days.required' => 'Net Working Days is required.',
                'basic_salary.required' => 'Basic Salary is required.',
                'net_salary.required' => 'Total Salary is required.',
                'name.required' => 'Name is required.'
            ]
        );
        if ($validator->fails()) {
            //$response['message'] = $validator->messages();
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {

            $data = array(
                'total_present' => $request->total_present,
                'total_absent' => $request->total_absent,
                'total_leave' => $request->total_leave,
                'half_days' => $request->half_days,
                'holiday' => $request->holidays,
                'basic_salary' => $request->basic_salary,
                'net_working_days' => $request->net_working_days,
                'net_salary' => $request->net_salary,
            );

            DB::table('employee_attendee_sheet')->where('id', $request->id)->update($data);
            return response()->json(['success' => true, 'message' => 'Updated successfully..', 'redirectUrl' => '/employee/employeeAttendeelist'], 200);
        }
    }

    public function deleteAttendee($id)
    {
        $delete = DB::table('employee_attendee_sheet')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully..', 'redirectUrl' => '/employee/employeeAttendeelist'], 200);
    }

    public function employeeAttendee()
    {

        $data = collect(range(11, 0));

        $monthList = $data->map(function ($i) {
            $dt = today()->startOfMonth()->subMonth($i);
            return [
                'month' => $dt->month,
                'month_name' => $dt->shortMonthName,
                'year' => $dt->format('Y')
            ];
        });
        $employees = array();
        $voucher_number = '';
        return view('employeeAttendee.list', array('month_year' => '', 'monthList' => $monthList, 'employees' => $employees, 'voucher_number' => $voucher_number));
    }
    // Search Attendee records
    public function searchAttendeeRecords(Request $request)
    {
        if (isset($request->invoice_number) || isset($request->employee_name)) {

            $query = DB::table('employee_attendee_sheet');
            $query->leftJoin('employee', 'employee_attendee_sheet.employee_id', '=', 'employee.id');
            $query->select('employee_attendee_sheet.*', 'employee.name');
            if (isset($request->invoice_number) && !empty($request->invoice_number)) {
                $query->where('employee_attendee_sheet.voucher_number', 'like', "%$request->invoice_number%");
            } else {
                $query->where('employee.name', 'like', "%$request->employee_name%");
            }
            $result = $query->where('employee_attendee_sheet.branch',Auth::user()->branch)->orderByDesc('id')->paginate(10);
            return view('employeeAttendee.employeeAttendeeList', array('lists' => $result, 'invoice_number' => $request->invoice_number, 'employee_name' => $request->employee_name));
        } else {
            $list = DB::table('employee_attendee_sheet')
                ->leftJoin('employee', 'employee_attendee_sheet.employee_id', '=', 'employee.id')
                ->select('employee_attendee_sheet.*', 'employee.name')
                ->where('employee_attendee_sheet.branch',Auth::user()->branch)
                ->paginate(10);
            return view('employeeAttendee.employeeAttendeeList', array('lists' => $list));
        }
    }
    public function searchMonthAttendeeList(Request $request)
    {


        if (isset($request->from_date) && isset($request->to_date)) {
            // $boundUser = DB::table('employee_attendee_sheet')->where('from_date',$request->from_date)->where('to_date',$request->to_date)->get();
            // if(!empty($boundUser) && isset($boundUser)){
            //     return response()->json(['success' => false, 'message' => 'Salary sheet within this date is already exi'], 200);
            // }
            // else{

            // }
            $allDates = array();
            $allDays = array();
            $allDaysName = array();
            $allDaysMonth = array();
            $dateRange = CarbonPeriod::create($request->from_date, $request->to_date);
            foreach ($dateRange->toArray() as $date) {
                $allDates[] = $date->toDateString();
                $allDays[] = $date->day;
                $allDaysName[] = $date->shortDayName;
                $allDaysMonth[] = $date->day . '-' . $date->shortMonthName;
            }
            $data = collect(range(11, 0));
            $monthList = $data->map(function ($i) {
                $dt = today()->startOfMonth()->subMonth($i);
                return [
                    'month' => $dt->month,
                    'month_name' => $dt->shortMonthName,
                    'year' => $dt->format('Y')
                ];
            });
        } else {
            $date = $request->month_year . '-1';
            $allDates = array();
            $allDays = array();
            $allDaysName = array();
            $allDaysMonth = array();
            $startDate = Carbon::createFromFormat('Y-m-d', $date)
                ->firstOfMonth()
                ->format('Y-m-d');
            $endDate = Carbon::createFromFormat('Y-m-d', $date)
                ->lastOfMonth()
                ->format('Y-m-d');
            $dateRange = CarbonPeriod::create($startDate, $endDate);
            foreach ($dateRange->toArray() as $date) {
                $allDates[] = $date->toDateString();
                $allDays[] = $date->day;
                $allDaysName[] = $date->shortDayName;
                $allDaysMonth[] = $date->day . '-' . $date->shortMonthName;
            }
            $data = collect(range(11, 0));
            $monthList = $data->map(function ($i) {
                $dt = today()->startOfMonth()->subMonth($i);
                return [
                    'month' => $dt->month,
                    'month_name' => $dt->shortMonthName,
                    'year' => $dt->format('Y')
                ];
            });
        }
        $employees = DB::table('employee')->where('branch', '=', Auth::user()->branch)->where('employee_type', '=', 1)->get();
        $voucher_number = DB::table('employee_attendee_sheet')->count() + 1;
        $voucher_number= Config::get('constants.EMPLOYEE_SALARY_SHEET').$voucher_number;
        $employee_attendee = array();
        if(isset($request->from_date) && isset($request->to_date)){
            $employee_attendee = DB::table('employee_attendee_sheet')->where('from_date', '=', $request->from_date)->where('to_date',$request->to_date)
                ->where('branch', '=', Auth::user()->branch)
                ->get();
            }
            else{
                $employee_attendee = DB::table('employee_attendee_sheet')->where('month_year',$request->month_year)
                ->where('branch', '=', Auth::user()->branch)
                ->get();

            }
            // echo $request->month_year;
            // echo "<pre>";
            // print_r($employee_attendee);
            // exit;
        $allAttendance = array();
            $ledger_entry =0;

        if (!$employee_attendee->isEmpty()) {
            $voucher_number=$employee_attendee[0]->voucher_number;
            $ledger_entry=$employee_attendee[0]->ledger_entry;
            $employees = array();
            foreach ($employee_attendee as $attendance) {
                // print_r($attendance->total_present);
                // exit;
                $employee = DB::table('employee')
                    ->where('id', '=', $attendance->employee_id)->first();
                $employee->total_present = $attendance->total_present;
                $employee->total_absent = $attendance->total_absent;
                $employee->total_leave = $attendance->total_leave;
                $employee->half_days = $attendance->half_days;
                $employee->holiday = $attendance->holiday;
                $employee->basic_salary = $attendance->basic_salary;
                $employee->net_salary = $attendance->net_salary;
                $employee->net_working_days = $attendance->net_working_days;
                $employee->net_working_days = $attendance->net_working_days;
                $employee->gross_salary = $attendance->gross_salary;
                $employee->total_deduction = $attendance->total_deduction;
                $employee->salary_paid = $attendance->salary_paid;
                $employees[] = $employee;
                $attandence_details = unserialize($attendance->employee_details);
                foreach ($attandence_details as $detail) {
                    $allAttendance[] = $detail['attendence'];
                }
            }
        }
        return view('employeeAttendee.list', array('month_year' => $request->month_year, 'monthList' => $monthList, 'employees' => $employees, 'allDates' => $allDates, 'allDays' => $allDays, 'allDaysName' => $allDaysName, 'allDaysMonth' => $allDaysMonth, 'voucher_number' => $voucher_number, 'allAttendance' => $allAttendance,'from_date'=>$request->from_date,'to_date'=>$request->to_date,'ledger_entry'=>$ledger_entry));
    }

    public function attendeeSave(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');

        $validator = Validator::make(
            $request->all(),
            [
                'voucher_number' => 'required',
                // 'month_year' => 'required'
            ],
            [
                'voucher_number.required' => 'The voucher number field is required.',
                // 'month_year.required' => 'The Month year field is required.',
            ]
        );
        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response, 422);
        } else {
            $ledgerCheck = 0;
            if(isset($request->ledger_entry)){
                $ledgerCheck = $request->ledger_entry;
            }
            $month_year = '';
            if(isset($request->from_date) && isset($request->to_date)){

                $checkMonthSalary = DB::table('employee_attendee_sheet')
                    ->where('from_date', $request->from_date)
                    ->where('to_date',$request->to_date)
                    ->get();
                if (!empty($checkMonthSalary)) {
                    DB::table('employee_attendee_sheet')->where('from_date', $request->from_date)->where('to_date',$request->to_date)->delete();
                }


            }
            else{

                $checkMonthSalary = DB::table('employee_attendee_sheet')
                ->where('month_year', $request->month_year)
                ->get();
                if (!empty($checkMonthSalary)) {
                    DB::table('employee_attendee_sheet')->where('month_year', $request->month_year)->delete();
                }
                $month_year = $request->month_year;
            }
            $checkLedgerEntry = DB::table('general_ledger_transactions')->where('voucher_number', $request->voucher_number)->get();
            if (!$checkLedgerEntry->isEmpty()) {
                $this->deleteDoubleEntry($request->voucher_number);
            }
            $allEmployees = [];
            $voucher_number = $request->voucher_number;
            $data = $request->attendece;
            foreach ($data as $value) {
                $arr = explode('~', $value);
                $employeeId = $arr[1];
                $attendence = array(
                    'date' => $arr[0],
                    'employee_id' => $arr[1],
                    'status' => $arr[2],
                    'attendence' => $value,
                );
                if (array_key_exists($employeeId, $allEmployees)) {
                    $allEmployees[$employeeId][] = $attendence;
                } else {
                    $allEmployees[$employeeId][] = $attendence;
                }
            }
            $employees = array_keys($allEmployees);
            foreach ($employees as $employee_id) {
                $employee = DB::table('employee')->where('id', $employee_id)->first();
                $total_present_ = 'total_present_' . $employee_id;
                $total_absent_ = 'total_absent_' . $employee_id;
                $total_leave_ = 'total_leave_' . $employee_id;
                $total_half_days_ = 'total_half_days_' . $employee_id;
                $total_basic_salary_ = 'total_basic_salary_' . $employee_id;
                $total_working_days_ = 'total_working_days_' . $employee_id;
                $net_salary_ = 'net_salary_' . $employee_id;
                $total_holidays_ = 'total_holidays_' . $employee_id;
                $gross_salary_ = 'gross_salary_' . $employee_id;
                $total_deduction_ = 'total_deduction_' . $employee_id;
                $salary_paid_ = 'salary_paid_' . $employee_id;
                $employeeData = array(
                    'employee_id' => $employee_id,
                    'employee_details' => serialize($allEmployees[$employee_id]),
                    'voucher_number' => $voucher_number,
                    'month_year' => $month_year,
                    'from_date' => $request->from_date,
                    'to_date' => $request->to_date,
                    'ledger_entry' => $ledgerCheck,
                    'total_present' => $request->{$total_present_},
                    'total_absent' => $request->{$total_absent_},
                    'total_leave' => $request->{$total_leave_},
                    'half_days' => $request->{$total_half_days_},
                    'basic_salary' => $request->{$total_basic_salary_},
                    'net_working_days' => $request->{$total_working_days_},
                    'net_salary' => $request->{$net_salary_},
                    'gross_salary' => $request->{$gross_salary_},
                    'total_deduction' => $request->{$total_deduction_},
                    'salary_paid' => $request->{$salary_paid_},
                    'holiday' => $request->{$total_holidays_},
                    'branch' => Auth::user()->branch,
                    'created_at' => date('Y-m-d'),
                );


                if(isset($request->ledger_entry) && $request->ledger_entry=='1'){

                    $debit = array(
                        'voucher_date' => date('Y-m-d'),
                        'voucher_number' => $request->voucher_number,
                        'general_ledger_account_id' => Config::get('constants.SALARY_EXPENSE_ACCOUNT_GENERAL_LEDGER'),
                        'note' => $employee->name . ' Attendance & Salary Posted ',
                        'debit' => $request->{$net_salary_},
                        'credit' => 0,
                        'branch' => Auth::user()->branch,
                        'created_at' => date('Y-m-d H:i:s'),
                    );
                    $this->insertDoubleEntry($debit);

                    $credit = array(
                        'voucher_date' => date('Y-m-d'),
                        'voucher_number' => $request->voucher_number,
                        'general_ledger_account_id' => $employee->general_ledger_account_id,
                        'note' => $employee->name .' Attendance & Salary Posted ',
                        'debit' => 0,
                        'credit' => $request->{$net_salary_}, //add actual production amount
                        'branch' => Auth::user()->branch,
                        'created_at' => date('Y-m-d H:i:s'),
                    );
                    $this->insertDoubleEntry($credit);

                    if(!empty($request->{$salary_paid_}) && $request->{$salary_paid_} > 0){
                        /**
                         * Insert Double entry
                         *Employee A/c Debit
                         *Cash A/c  Credit
                         */
                        $debit = array(
                            'voucher_date' =>date('Y-m-d'),
                            'voucher_number' => $request->voucher_number,
                            'general_ledger_account_id' => $employee->general_ledger_account_id,
                            'note' =>$employee->name .' Salary Paid ',
                            'debit' => $request->{$salary_paid_},
                            'credit' => 0,
                            'branch' => Auth::user()->branch,
                            'updated_at' => date('Y-m-d H:i:s'),
                        );
                        $this->insertDoubleEntry($debit);
                        $credit = array(
                            'voucher_date' => date('Y-m-d'),
                            'voucher_number' => $request->voucher_number,
                            'general_ledger_account_id' => Config::get('constants.CASH_ACCOUNT_GENERAL_LEDGER'),
                            'note' => $employee->name .' Salary Paid ',
                            'debit' => 0,
                            'credit' => $request->{$salary_paid_},
                            'branch' => Auth::user()->branch,
                            'updated_at' => date('Y-m-d H:i:s'),
                        );
                        $this->insertDoubleEntry($credit);
                    }

                }
                DB::table('employee_attendee_sheet')->insert($employeeData);
            }

            return response()->json(['success' => true, 'message' => 'Attendee Sheet added successfully..', 'redirectUrl' => '/employee/employeeAttendeeAdd'], 200);
        }
    }

    public function deleteAllEmployeeAttendee($date)
    {
        DB::table('employee_attendee_sheet')->where('month_year', $date)->delete();
        // DB::table('general_ledger_transations')->where('')
    }

    public function attendeePdf($employee_name,$invoice_number)
    {
        $query = DB::table('employee_attendee_sheet')
            ->leftJoin('employee', 'employee_attendee_sheet.employee_id', '=', 'employee.id');
            if($employee_name != 'none'){
                $query->where('employee.name', 'like', "%$employee_name%");
            }
            if($invoice_number != 'none'){
                $query->where('employee_attendee_sheet.voucher_number', 'like', "%$invoice_number%");

            }
            $list = $query->get();
        // return view('employeeAttendee.employeeAttendeeList',array('lists'=>$list));

        $data = array('lists' => $list);
        $customPaper = array(0, 0, 800.00, 1000.80);
        $pdf = PDF::loadView('employeeAttendee.attendeePdf', $data)->setPaper($customPaper, 'landscape');

        return $pdf->stream('pagePdf.pdf');
    }

    public function singleRecordPdf($id)
    {
        $list = DB::table('employee_attendee_sheet')
            ->leftJoin('employee', 'employee_attendee_sheet.employee_id', '=', 'employee.id')
            ->where('employee_attendee_sheet.id', $id)
            ->get();
        // return view('employeeAttendee.employeeAttendeeList',array('lists'=>$list));


        $data = array('lists'=>$list);
        $customPaper = array(0,0,800.00,1000.80);
        $pdf = PDF::loadView('employeeAttendee.singleAttendeePdf', $data)->setPaper($customPaper, 'landscape');

        return $pdf->stream('Salary Sheet.pdf');
    }

    // Employee Advance
    public function employeeAdvance($id)
    {
        $list = DB::table('employee_advance_return')->leftJoin('employee', 'employee_advance_return.employee_id', '=', 'employee.id')->select('employee_advance_return.*', 'employee.name', 'employee.id', 'employee.advance')->where('employee_advance_return.employee_id', $id)->paginate(4);
        $sum = DB::table('employee_advance_return')->leftJoin('employee', 'employee_advance_return.employee_id', '=', 'employee.id')->select('employee_advance_return.*', 'employee.name', 'employee.id', 'employee.advance')->where('employee_advance_return.employee_id', $id)->paginate(4)->sum('amount');

        $advance = DB::table('employee')->where('id', $id)->first();
        return view('employeeAdvance', array('lists' => $list, 'advance' => $advance, 'net_amount' => $sum));
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

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
}
