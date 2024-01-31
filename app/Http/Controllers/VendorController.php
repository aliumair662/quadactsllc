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
use Illuminate\Support\Facades\Log;

class VendorController extends Controller
{
    public function vendorList()
    {
        $lists = DB::table('vendors')->where('branch', Auth::user()->branch)->orderByDesc('id')->paginate(20);
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
        return view('vendor/list', array('vendors' => $lists));
    }


    public function newVendor()
    {

        return view('vendor/new');
    }



    public function storeVendor(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $emailexist = DB::table('vendors')->where('email', $request->email)->first();
        if (!empty($emailexist)) {
            return response()->json(['success' => false, 'message' => 'The email has already been taken.Please try another one.', 'redirectUrl' => ''], 200);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|min:3|max:500|unique:vendors',
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
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {

            $account = array(
                'name' => $request->name,
                'account_type_id' => Config::get('constants.VENDOR_CHART_OF_ACCOUNT_TYPE_ID'),
                'chart_of_account_id' => Config::get('constants.VENDOR_CHART_OF_ACCOUNT_ID'),
                'chart_of_accounts_category_id' => Config::get('constants.VENDOR_CHART_OF_ACCOUNT_CATEGORY_ID'),
                'created_at' => date('Y-m-d H:i:s'),
                'branch' => Auth::user()->branch,
            );
            $general_ledger_account_id = DB::table('general_ledger_accounts')->insertGetId($account);
            $vendor = array(
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'general_ledger_account_id' => $general_ledger_account_id,
                'created_at' => date('Y-m-d H:i:s'),
            );

            $vendorId = DB::table('vendors')->insertGetId($vendor);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $vendorId,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($vendor),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Vendor Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Vendor added successfully..', 'redirectUrl' => '/vendor/list'], 200);
        }
    }


    public function editVendor($id)
    {
        $menus = DB::table('vendors')->where('id', $id)->first();
        return view('vendor.new', array('vendor' => $menus));
    }

    public function updateVendor(Request $request)
    {
        $userinfo = DB::table('vendors')->where('id', $request->id)->first();
        $emailexist = DB::table('vendors')->where('email', $request->email)->where('id', '!=', $request->id)->first();
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
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
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {
            $vendorInfo = DB::table('vendors')->where('id', $request->id)->first();
            $account = array(
                'name' => $request->name,
                'account_type_id' => Config::get('constants.VENDOR_CHART_OF_ACCOUNT_TYPE_ID'),
                'chart_of_account_id' => Config::get('constants.VENDOR_CHART_OF_ACCOUNT_ID'),
                'chart_of_accounts_category_id' => Config::get('constants.VENDOR_CHART_OF_ACCOUNT_CATEGORY_ID'),
                'updated_at' => date('Y-m-d H:i:s'),
                'branch' => Auth::user()->branch,
                'status' => $request->status,
            );
            DB::table('general_ledger_accounts')->where('id', $vendorInfo->general_ledger_account_id)->update($account);
            $user = array(
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'updated_at' => date('Y-m-d H:i:s')
            );
            DB::table('vendors')->where('id', $request->id)->update($user);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->id,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($user),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Vendor Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'vendor update successfully..', 'redirectUrl' => '/vendor/list'], 200);
        }
    }


    public function deleteVendor($id)
    {

        $vendor = DB::table('vendors')
            ->where('id', $id)
            ->first();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $vendor->id,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($vendor),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Vendor Invoice',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        $general_transaction = DB::table('general_ledger_transactions')
            ->where('general_ledger_account_id', $vendor->general_ledger_account_id)
            ->first();
        if (isset($general_transaction)) {
            $vendorInactive = array(
                'status' => 0,

            );
            $ledgerAccount = array(
                'status' => 0,
            );
            $vendorInactive['updated_at'] = date('Y-m-d H:i:s');
            $ledgerAccount['updated_at'] = date('Y-m-d H:i:s');
            $ledgerAccount = DB::table('general_ledger_accounts')->where('id', $vendor->general_ledger_account_id)->update($ledgerAccount);
            $vendorInactive = DB::table('vendors')->where('id', $id)->update($vendorInactive);
            return redirect('vendor/list');
        } else {
            $vendorInactive = DB::table('vendors')->where('id', $id)->delete();
            return redirect('vendor/list');
        }
    }


    // Search for vendors
    public function searchVendor(Request $request)
    {
        $Queries = array();
        if (isset($request->vendor_name)) {
            $Queries['vendor_name'] = $request->vendor_name;
        }
        $lists = DB::table('vendors')
            ->where('name', 'like', "%$request->vendor_name%")->where('branch', Auth::user()->branch)
            ->orderByDesc('id')
            ->paginate(1);
        // $lists->appends($Queries);
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
        return view('vendor/list', array('vendors' => $list, 'searchQuery' => $request->vendor_name));
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
