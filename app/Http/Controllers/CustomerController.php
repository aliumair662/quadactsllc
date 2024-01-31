<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PgSql\Lob;

class CustomerController extends Controller
{
    public function customerList()
    {

        if (Auth::user()->is_admin) {
            $lists = DB::table('customers')
                ->where('branch', Auth::user()->branch)
                ->orderByDesc('id')
                ->paginate(20);
            foreach ($lists as $list) {
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
        } else {
            $lists = DB::table('customers')
                ->where('branch', Auth::user()->branch)
                ->where('user_id', Auth::user()->id)
                ->orderByDesc('id')
                ->paginate(20);
            foreach ($lists as $list) {
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
        }

        return view('customer.list', array('customers' => $lists));
    }
    public function newCustomer()
    {
        $list = DB::table('customers')->get();
        return view('customer.new', array('customers' => $list));
    }
    public function storeCustomer(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $emailexist = DB::table('customers')->where('email', $request->email)->first();
        if (!empty($emailexist)) {
            return response()->json(['success' => false, 'message' => 'The email has already been taken.Please try another one.', 'redirectUrl' => ''], 200);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|min:3|max:500|unique:customers',
                'email' => 'required',
                'phone' => 'required',
                'address' => 'required'
            ],
            [
                'name.required' => 'The Name field is required.',
                'name.unique' => 'The name has already been taken.',
                'email.required' => 'The Email field is required.',
                'phone.required' => 'The Phone field is required.',
                'address.required' => 'The Address field is required.'
            ]
        );

        if ($validator->fails()) {
            //$response['message'] = $validator->messages();
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {
            $account = array(
                'name' => $request->name,
                'account_type_id' => Config::get('constants.CUSTOMER_CHART_OF_ACCOUNT_TYPE_ID'),
                'chart_of_account_id' => Config::get('constants.CUSTOMER_CHART_OF_ACCOUNT_ID'),
                'chart_of_accounts_category_id' => Config::get('constants.CUSTOMER_CHART_OF_ACCOUNT_CATEGORY_ID'),
                'created_at' => date('Y-m-d H:i:s'),
                'branch' => Auth::user()->branch,
            );
            DB::table('general_ledger_accounts')->insert($account);
            $general_ledger_account_id = DB::getPdo()->lastInsertId();
            if (!empty($general_ledger_account_id)) {
                $customer = array(
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'address' => $request->address,
                    'general_ledger_account_id' => $general_ledger_account_id,
                    'branch' => Auth::user()->branch,
                    'created_at' => date('Y-m-d H:i:s'),
                    'user_id' => Auth::user()->id,
                    'user_name' => Auth::user()->name,
                );
                $customerId = DB::table('customers')->insertGetId($customer);
                $log = array(
                    'user_id' => Auth::user()->id,
                    'voucher_number' => $customerId,
                    'transaction_action' => 'Created',
                    'transaction_detail' => serialize($customer),
                    'branch' => Auth::user()->branch,
                    'transaction_type' => 'Customer Invoice',
                    'created_at' => date('Y-m-d H:i:s'),
                );
                $this->addTransactionLog($log);
            }
            if (isset($request->customer_model) && $request->customer_model === '1') {
                return response()->json(['success' => true, 'message' => 'Customer added successfully..', 'redirectUrl' => 'currentPage'], 200);
            } else {
                return response()->json(['success' => true, 'message' => 'Customer added successfully..', 'redirectUrl' => '/customer/list'], 200);
            }
        }
    }


    public function editCustomer($id)
    {
        $menus = DB::table('customers')->where('id', $id)->first();
        // return $menus;
        // echo $menus->title;
        // exit;
        return view('customer.new', array('customer' => $menus));
    }
    public function updateCustomer(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $emailexist = DB::table('customers')->where('email', $request->email)->where('id', '!=', $request->id)->first();
        if (!empty($emailexist)) {
            return response()->json(['success' => false, 'message' => 'The email has already been taken.Please try another one.', 'redirectUrl' => ''], 200);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|min:3|max:500',
                'email' => 'required',
                'phone' => 'required',
                'address' => 'required'
            ],
            [
                'name.required' => 'The Name field is required.',
                'email.required' => 'The Email field is required.',
                'phone.required' => 'The Phone field is required.',
                'address.required' => 'The Address field is required.'
            ]
        );

        if ($validator->fails()) {
            //$response['message'] = $validator->messages();
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {
            $customerInfo = DB::table('customers')->where('id', $request->id)->first();
            $account = array(
                'name' => $request->name,
                'account_type_id' => Config::get('constants.CUSTOMER_CHART_OF_ACCOUNT_TYPE_ID'),
                'chart_of_account_id' => Config::get('constants.CUSTOMER_CHART_OF_ACCOUNT_ID'),
                'chart_of_accounts_category_id' => Config::get('constants.CUSTOMER_CHART_OF_ACCOUNT_CATEGORY_ID'),
                'updated_at' => date('Y-m-d H:i:s'),
                'branch' => Auth::user()->branch,
                'status' => $request->status,
            );
            DB::table('general_ledger_accounts')->where('id', $customerInfo->general_ledger_account_id)->update($account);
            $customer = array(
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'status' => $request->status,
                'branch' => Auth::user()->branch,
                'updated_at' => date('Y-m-d H:i:s'),
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->name,
            );
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->id,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($customer),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Customer Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            DB::table('customers')->where('id', $request->id)->update($customer);
            $customer_id = DB::getPdo()->lastInsertId();
            return response()->json(['success' => true, 'message' => 'Customer added successfully..', 'redirectUrl' => '/customer/list'], 200);
        }
    }
    public function deleteCustomer($id)
    {
        $customer = DB::table('customers')
            ->where('id', $id)
            ->first();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $id,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($customer),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Customer Invoice',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        $general_transaction = DB::table('general_ledger_transactions')
            ->where('general_ledger_account_id', $customer->general_ledger_account_id)
            ->first();
        if (isset($general_transaction)) {
            $customerInactive = array(
                'status' => 0,

            );
            $ledgerAccount = array(
                'status' => 0,
            );
            $customerInactive['updated_at'] = date('Y-m-d H:i:s');
            $ledgerAccount['updated_at'] = date('Y-m-d H:i:s');
            $ledgerAccount = DB::table('general_ledger_accounts')->where('id', $customer->general_ledger_account_id)->update($ledgerAccount);
            $customerInactive = DB::table('customers')->where('id', $id)->update($customerInactive);
            return redirect('customer/list');
        } else {
            $customerInactive = DB::table('customers')->where('id', $id)->delete();
            return redirect('customer/list');
        }
    }



    public function searchCustomers(Request $request)
    {
        $Queries = array();
        if (isset($request->customer_name)) {
            $Queries['customer_name'] = $request->customer_name;
        }
        $lists = DB::table('customers')
            ->where('name', 'like', "%$request->customer_name%")
            ->orderByDesc('id')
            ->paginate(1);
        $lists->appends($Queries);
        foreach ($lists as $list) {
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
        return view('customer.list', array('customers' => $lists, 'searchQuery' => $request->customer_name, 'queries' => $Queries));
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

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
}
