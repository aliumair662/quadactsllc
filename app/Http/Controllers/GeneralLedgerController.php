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
use Illuminate\Support\Facades\Log;

class GeneralLedgerController extends Controller
{
    public function ledger($general_ledger_account_id)
    {
        /***
         * Default Begining Date will be last day from today
         * and Ending date will be today date
         */
        $journal_entry_rule = $this->getAccountjournalentryrule($general_ledger_account_id);
        $journal_sum_rule = 'debit - credit';
        if ($journal_entry_rule == 'credit') {
            $journal_sum_rule = 'credit - debit';
        }
        $account = DB::table('general_ledger_accounts')
            ->join('general_ledger_accounts_types', 'general_ledger_accounts.account_type_id', '=', 'general_ledger_accounts_types.id')
            ->where('general_ledger_accounts.id', '=', $general_ledger_account_id)
            ->select('general_ledger_accounts.*', 'general_ledger_accounts_types.name as account_type')
            ->first();
        $beginningBalance = DB::table('general_ledger_transactions')
            ->where('voucher_date', '<', Carbon::now()->format('d-m-Y'))
            ->where('general_ledger_account_id', $general_ledger_account_id)
            // ->where('branch', Auth::user()->branch)
            ->sum(\DB::raw($journal_sum_rule));
        $allTransactions = DB::table('general_ledger_transactions')
            ->where('general_ledger_account_id', $general_ledger_account_id)
            ->where('voucher_date', '=', Carbon::now()->format('d-m-Y'))
            // ->where('branch', Auth::user()->branch)
            ->get();
        $transactions = array();
        $balance = $beginningBalance;
        if (!empty($allTransactions)) {
            foreach ($allTransactions as $transaction) {
                if ($journal_entry_rule == 'credit') {
                    $transaction->closingBalance = $balance + $transaction->credit - $transaction->debit;
                } else {
                    $transaction->closingBalance = $balance + $transaction->debit - $transaction->credit;
                }
                $transactions[] = $transaction;
                $balance = $transaction->closingBalance;
            }
        }
        $endingBalance = DB::table('general_ledger_transactions')
            ->where('voucher_date', '<=', Carbon::now()->format('d-m-Y'))
            ->where('general_ledger_account_id', $general_ledger_account_id)
            // ->where('branch', Auth::user()->branch)
            ->sum(\DB::raw($journal_sum_rule));

        $data = array(
            'beginningBalance' => $beginningBalance,
            'transactions' => $transactions,
            'endingBalance' => $endingBalance,
            'account' => $account,
            'journal_entry_rule' => $journal_entry_rule
        );
        return view('ledger/list', array('data' => $data));
    }
    public function searchLedger(Request $request)
    {
        /***
         * Default Begining Date will be last day from today
         * and Ending date will be today date
         */
        $journal_entry_rule = $this->getAccountjournalentryrule($request->general_ledger_account_id);
        $journal_sum_rule = 'debit - credit';
        if ($journal_entry_rule == 'credit') {
            $journal_sum_rule = 'credit - debit';
        }
        $account = DB::table('general_ledger_accounts')
            ->join('general_ledger_accounts_types', 'general_ledger_accounts.account_type_id', '=', 'general_ledger_accounts_types.id')
            ->where('general_ledger_accounts.id', '=', $request->general_ledger_account_id)
            ->select('general_ledger_accounts.*', 'general_ledger_accounts_types.name as account_type')
            ->first();
        $beginningBalance = DB::table('general_ledger_transactions')
            ->where('voucher_date', '<', $request->from_date)
            ->where('general_ledger_account_id', $request->general_ledger_account_id)
            // ->where('branch', Auth::user()->branch)
            ->sum(\DB::raw($journal_sum_rule));
        $allTransactions = DB::table('general_ledger_transactions')
            ->where('general_ledger_account_id', $request->general_ledger_account_id)
            ->where('voucher_date', '>=', $request->from_date)
            ->where('voucher_date', '<=', $request->to_date)
            // ->where('branch', Auth::user()->branch)
            ->get();
        $transactions = array();
        $balance = $beginningBalance;
        if (!empty($allTransactions)) {
            foreach ($allTransactions as $transaction) {
                if ($journal_entry_rule == 'credit') {
                    $transaction->closingBalance = $balance + $transaction->credit - $transaction->debit;
                } else {
                    $transaction->closingBalance = $balance + $transaction->debit - $transaction->credit;
                }

                $transactions[] = $transaction;
                $balance = $transaction->closingBalance;
            }
        }
        $endingBalance = DB::table('general_ledger_transactions')
            ->where('voucher_date', '<=', $request->to_date)
            ->where('general_ledger_account_id', $request->general_ledger_account_id)
            // ->where('branch', Auth::user()->branch)
            ->sum(\DB::raw($journal_sum_rule));
        $data = array(
            'beginningBalance' => $beginningBalance,
            'transactions' => $transactions,
            'endingBalance' => $endingBalance,
            'account' => $account,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'journal_entry_rule' => $journal_entry_rule
        );
        return view('ledger/list', array('data' => $data));
    }
    public function ledgerPdf(Request $request, $general_ledger_account_id, $customer_name, $type)
    {
        /***
         * Default Begining Date will be last day from today
         * and Ending date will be today date
         */
        $journal_entry_rule = $this->getAccountjournalentryrule($general_ledger_account_id);
        $journal_sum_rule = 'debit - credit';
        if ($journal_entry_rule == 'credit') {
            $journal_sum_rule = 'credit - debit';
        }
        $account = DB::table('general_ledger_accounts')
            ->join('general_ledger_accounts_types', 'general_ledger_accounts.account_type_id', '=', 'general_ledger_accounts_types.id')
            ->where('general_ledger_accounts.id', '=', $general_ledger_account_id)
            ->select('general_ledger_accounts.*', 'general_ledger_accounts_types.name as account_type')
            ->first();
        $beginningBalance = DB::table('general_ledger_transactions')
            ->where('voucher_date', '<', $request->from_date)
            ->where('general_ledger_account_id', $general_ledger_account_id)
            ->where('branch', Auth::user()->branch)
            ->sum(\DB::raw($journal_sum_rule));
        $allTransactions = DB::table('general_ledger_transactions')
            ->where('general_ledger_account_id', $general_ledger_account_id)
            ->where('voucher_date', '>=', $request->from_date)
            ->where('voucher_date', '<=', $request->to_date)
            ->where('branch', Auth::user()->branch)
            ->get();
        $transactions = array();
        $balance = $beginningBalance;
        if (!empty($allTransactions)) {
            foreach ($allTransactions as $transaction) {
                if ($journal_entry_rule == 'credit') {
                    $transaction->closingBalance = $balance + $transaction->credit - $transaction->debit;
                } else {
                    $transaction->closingBalance = $balance + $transaction->debit - $transaction->credit;
                }
                $transactions[] = $transaction;
                $balance = $transaction->closingBalance;
            }
        }
        $endingBalance = DB::table('general_ledger_transactions')
            ->where('voucher_date', '<=',  $request->to_date)
            ->where('general_ledger_account_id', $general_ledger_account_id)
            ->where('branch', Auth::user()->branch)
            ->sum(\DB::raw($journal_sum_rule));
        if ($type == 'Vendor') {
            $vendor = DB::table('vendors')->where('name',  $customer_name)->first();
        } else {
            $customer = DB::table('customers')->where('name',  $customer_name)->first();
        }

        $data = array(
            'beginningBalance' => $beginningBalance,
            'transactions' => $transactions,
            'endingBalance' => $endingBalance,
            'account' => $account,
            'journal_entry_rule' => $journal_entry_rule,
            'customer' => isset($customer) ? $customer : '',
            'vendor' => isset($vendor) ? $vendor : ''
        );

        $pdf = PDF::loadView('ledger.ledgerPdf', compact('data'));
        // return $pdf->download('salePdf.pdf');
        return $pdf->stream('ledger.pdf');
    }

    // Ledger Accounts CRUD starts
    public function ledgerAccountsList()
    {
        /**
         * Get Only Accounts thoes have type General Ledger
         * but not default accounts
         */
        $list = DB::table('general_ledger_accounts')
            ->leftjoin('general_ledger_accounts_types', 'general_ledger_accounts.account_type_id', '=', 'general_ledger_accounts_types.id')
            ->leftjoin('chart_of_accounts', 'general_ledger_accounts.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->leftjoin('chart_of_accounts_category', 'general_ledger_accounts.chart_of_accounts_category_id', '=', 'chart_of_accounts_category.id')
            ->select('general_ledger_accounts.*', 'general_ledger_accounts_types.name as ledger_account_name', 'chart_of_accounts.name as chart_name', 'chart_of_accounts_category.name as accounts_category_name')
            ->where('general_ledger_accounts.branch', Auth::user()->branch)
            //->where('general_ledger_accounts.default_account',0)
            //->where('general_ledger_accounts.status',1)
            ->orderByDesc('general_ledger_accounts.id')
            ->paginate(20);
        $chart_of_accounts = DB::table('chart_of_accounts')->get();
        return view('ledgerAccounts/list', array('ledgerAccountsList' => $list, 'chart_of_accounts' => $chart_of_accounts));
    }

    public function newLedgerAccount()
    {
        $chart_of_accounts = DB::table('chart_of_accounts')->get();
        $general_ledger_accounts_types = DB::table('general_ledger_accounts_types')
            ->where('id', 4)
            ->get();
        $chart_of_accounts_category = DB::table('chart_of_accounts_category')
            ->get();
        return view('ledgerAccounts/new', array('account_group' => $chart_of_accounts, 'account_type' => $general_ledger_accounts_types, 'account_category' => $chart_of_accounts_category));
    }

    public function saveLedgerAccount(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|min:3|max:500|unique:general_ledger_accounts',
                'account_group' => 'required',
                'account_type' => 'required',
                'account_category' => 'required'
            ],
            [
                'name.required' => 'The name field is required.',
                'account_group.required' => 'The account group field is required.',
                'account_type.required' => 'The account type field is required.',
                'account_category.required' => 'The account category field is required.'
            ]
        );

        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {

            $general_ledger_accounts = array(
                'name' => $request->name,
                'account_type_id' => $request->account_type,
                'chart_of_account_id' => $request->account_group,
                'chart_of_accounts_category_id' => $request->account_category,
                'created_at' => date('d-m-Y H:i:s'),
                'status' => 1
            );

            $account = DB::table('general_ledger_accounts')->insertGetId($general_ledger_accounts);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $account,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($general_ledger_accounts),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Ledger Account',
                'created_at' => date('d-m-Y H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Account added successfully..', 'redirectUrl' => '/ledgerAccounts/list'], 200);
        }
    }

    public function editLedgerAccount($id)
    {
        $ledger_account = DB::table('general_ledger_accounts')
            ->leftjoin('general_ledger_accounts_types', 'general_ledger_accounts.account_type_id', '=', 'general_ledger_accounts_types.id')
            ->leftjoin('chart_of_accounts', 'general_ledger_accounts.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->leftjoin('chart_of_accounts_category', 'general_ledger_accounts.chart_of_accounts_category_id', '=', 'chart_of_accounts_category.id')
            ->select('general_ledger_accounts.id', 'general_ledger_accounts.name', 'general_ledger_accounts_types.id as ledger_account_id', 'chart_of_accounts.id as chart_id', 'chart_of_accounts_category.id as accounts_category_id')
            ->where('general_ledger_accounts.id', $id)
            ->first();
        // echo "<pre>";
        // print_r($ledger_account[0]->ledger_account_id);
        // exit;
        $chart_of_accounts = DB::table('chart_of_accounts')->get();
        $general_ledger_accounts_types = DB::table('general_ledger_accounts_types')->get();
        $chart_of_accounts_category = DB::table('chart_of_accounts_category')->get();
        return view('ledgerAccounts/new', array('ledgerAccount' => $ledger_account, 'account_group' => $chart_of_accounts, 'account_type' => $general_ledger_accounts_types, 'account_category' => $chart_of_accounts_category));
    }


    public function updateLedgerAccount(Request $request)
    {
        $nameexist = DB::table('general_ledger_accounts')->where('name', $request->name)->where('id', '!=', $request->ledger_id)->first();
        if (!empty($nameexist)) {
            return response()->json(['success' => false, 'message' => 'The name has already been taken.Please try another one.', 'redirectUrl' => ''], 200);
        }
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|min:3|max:500',
                'account_group' => 'required',
                'account_type' => 'required',
                'account_category' => 'required'
            ],
            [
                'name.required' => 'The name field is required.',
                'account_group.required' => 'The account group field is required.',
                'account_type.required' => 'The account type field is required.',
                'account_category.required' => 'The account category field is required.'
            ]
        );

        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {

            $general_ledger_accounts = array(
                'name' => $request->name,
                'account_type_id' => $request->account_type,
                'chart_of_account_id' => $request->account_group,
                'chart_of_accounts_category_id' => $request->account_category,
                'created_at' => date('d-m-Y H:i:s'),
                'status' => 1
            );

            $account = DB::table('general_ledger_accounts')->where('id', $request->ledger_id)->update($general_ledger_accounts);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->id,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($general_ledger_accounts),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Ledger Account',
                'created_at' => date('d-m-Y H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Account Updated successfully..', 'redirectUrl' => '/ledgerAccounts/list'], 200);
        }
    }

    public function deleteLedgerAccount($id)
    {
        $ledger = DB::table('general_ledger_accounts')->where('id', $id)->first();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $ledger->id,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($ledger),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Ledger Account',
            'created_at' => date('d-m-Y H:i:s'),
        );
        $this->addTransactionLog($log);
        if ($ledger->default_account == 1) {
            return redirect('ledgerAccounts/list');
        }
        $ledger_transactions = DB::table('general_ledger_transactions')->where('general_ledger_account_id', $id)->first();
        if (!empty($ledger_transactions)) {
            $statusUpdate = array(
                'status' => '0'
            );
            $ledger = DB::table('general_ledger_accounts')->where('id', $id)->update($statusUpdate);
            return redirect('ledgerAccounts/list');
        } else {
            $deleteLedger = DB::table('general_ledger_accounts')->where('id', $id)->delete();
            return redirect('ledgerAccounts/list');
        }
    }


    // Search For Ledger Accounts
    public function searchGeneralAccounts(Request $request)
    {
        $list = DB::table('general_ledger_accounts')
            ->leftjoin('general_ledger_accounts_types', 'general_ledger_accounts.account_type_id', '=', 'general_ledger_accounts_types.id')
            ->leftjoin('chart_of_accounts', 'general_ledger_accounts.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->leftjoin('chart_of_accounts_category', 'general_ledger_accounts.chart_of_accounts_category_id', '=', 'chart_of_accounts_category.id')
            ->select('general_ledger_accounts.*', 'general_ledger_accounts_types.name as ledger_account_name', 'chart_of_accounts.name as chart_name', 'chart_of_accounts_category.name as accounts_category_name')
            ->where('general_ledger_accounts.name', 'like', "%{$request->ledger_name}%")
            ->where('chart_of_accounts.id', 'like', "%{$request->account_group}%")
            ->where('general_ledger_accounts.branch', Auth::user()->branch)
            ->orderByDesc('general_ledger_accounts.id')
            ->paginate(20);
        $chart_of_accounts = DB::table('chart_of_accounts')->get();
        return view('ledgerAccounts/list', array('ledgerAccountsList' => $list, 'searchQuery' => $request->ledger_name, 'searchByAccountGroup' => $request->account_group, 'chart_of_accounts' => $chart_of_accounts));
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




    public function balanceListSearch(Request $request)
    {
        $fromdate = $request->from_date;
        $todate = $request->to_date;
        /**
         * all assets
         */
        $allAssetsAccounts = DB::table('general_ledger_accounts')
            ->join('chart_of_accounts', 'general_ledger_accounts.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.id', '=', 1)
            //->where('general_ledger_accounts.account_type_id', '=', 4)
            //->whereNotIn('general_ledger_accounts.id',  [4,10])
            ->select('general_ledger_accounts.*', 'chart_of_accounts.name as account_type_name')
            ->get();

        $allAssets = array();
        $allAssetsTotal = 0;
        foreach ($allAssetsAccounts as $account) {
            $account->balance = ($this->getAccountBalance($account->id, 'debit - credit', $fromdate, $todate, '') > 0) ? $this->getAccountBalance($account->id, 'debit - credit', $fromdate, $todate, '') : 0;
            $allAssetsTotal += $account->balance;
            $allAssets[] = $account;
        }
        /**
         * all liabilities
         */
        $allLiabilitiesAccounts = DB::table('general_ledger_accounts')
            ->join('chart_of_accounts', 'general_ledger_accounts.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.id', '=', 2)
            ->select('general_ledger_accounts.*', 'chart_of_accounts.name as account_type_name')
            ->get();
        $allLiabilitiesTotal = 0;
        $allLiabilities = array();
        foreach ($allLiabilitiesAccounts as $account) {
            $account->balance = $this->getAccountBalance($account->id, 'credit - debit', $fromdate, $todate, '');
            $allLiabilitiesTotal += $account->balance;
            $allLiabilities[] = $account;
        }
        /**
         * all equity
         */
        $allEquityAccounts = DB::table('general_ledger_accounts')
            ->join('chart_of_accounts', 'general_ledger_accounts.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.id', '=', 3)
            ->select('general_ledger_accounts.*', 'chart_of_accounts.name as account_type_name')
            ->get();

        $allEquityTotal = 0;
        $allEquites = array();

        foreach ($allEquityAccounts as $account) {
            $account->balance = $this->getAccountBalance($account->id, 'credit - debit', $fromdate, $todate, '');
            $allEquityTotal += $account->balance;
            $allEquites[] = $account;
        }
        $data = $this->incomeStatment($fromdate, $todate);
        return view('ledgerAccounts.balanceSheet', array('allAssets' => $allAssets, 'allAssetsTotal' => $allAssetsTotal, 'allLiabilities' => $allLiabilities, 'allLiabilitiesTotal' => $allLiabilitiesTotal, 'allEquites' => $allEquites, 'allEquityTotal' => $allEquityTotal, 'netProfileLoss' => $data['netProfileLoss'], 'fromdate' => $fromdate, 'todate' => $todate));
    }
    public function balanceList()
    {
        $fromdate = Carbon::now()->subMonth()->format('d-m-Y');
        $todate = date('d-m-Y');
        /**
         * all assets
         */
        $allAssetsAccounts = DB::table('general_ledger_accounts')
            ->join('chart_of_accounts', 'general_ledger_accounts.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.id', '=', 1)
            //->where('general_ledger_accounts.account_type_id', '=', 4)
            //->whereNotIn('general_ledger_accounts.id',  [4,10])
            ->select('general_ledger_accounts.*', 'chart_of_accounts.name as account_type_name')
            ->get();

        $allAssets = array();
        $allAssetsTotal = 0;
        foreach ($allAssetsAccounts as $account) {
            $account->balance = ($this->getAccountBalance($account->id, 'debit - credit', $fromdate, $todate, '') > 0) ? $this->getAccountBalance($account->id, 'debit - credit', $fromdate, $todate, '') : 0;
            $allAssetsTotal += $account->balance;
            $allAssets[] = $account;
        }
        /**
         * all liabilities
         */
        $allLiabilitiesAccounts = DB::table('general_ledger_accounts')
            ->join('chart_of_accounts', 'general_ledger_accounts.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.id', '=', 2)
            ->select('general_ledger_accounts.*', 'chart_of_accounts.name as account_type_name')
            ->get();
        $allLiabilitiesTotal = 0;
        $allLiabilities = array();
        foreach ($allLiabilitiesAccounts as $account) {
            $account->balance = $this->getAccountBalance($account->id, 'credit - debit', $fromdate, $todate, '');
            $allLiabilitiesTotal += $account->balance;
            $allLiabilities[] = $account;
        }
        /**
         * all equity
         */
        $allEquityAccounts = DB::table('general_ledger_accounts')
            ->join('chart_of_accounts', 'general_ledger_accounts.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.id', '=', 3)
            ->select('general_ledger_accounts.*', 'chart_of_accounts.name as account_type_name')
            ->get();

        $allEquityTotal = 0;
        $allEquites = array();

        foreach ($allEquityAccounts as $account) {
            $account->balance = $this->getAccountBalance($account->id, 'credit - debit', $fromdate, $todate, '');
            $allEquityTotal += $account->balance;
            $allEquites[] = $account;
        }
        $data = $this->incomeStatment($fromdate, $todate);
        return view('ledgerAccounts.balanceSheet', array('allAssets' => $allAssets, 'allAssetsTotal' => $allAssetsTotal, 'allLiabilities' => $allLiabilities, 'allLiabilitiesTotal' => $allLiabilitiesTotal, 'allEquites' => $allEquites, 'allEquityTotal' => $allEquityTotal, 'netProfileLoss' => $data['netProfileLoss'], 'fromdate' => $fromdate, 'todate' => $todate));
    }

    // Balance List Pdf
    public function balancePdf()
    {
        $fromdate = Carbon::now()->subMonth()->format('d-m-Y');
        $todate = date('d-m-Y');
        /**
         * all assets
         */
        $allAssetsAccounts = DB::table('general_ledger_accounts')
            ->join('chart_of_accounts', 'general_ledger_accounts.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.id', '=', 1)
            //->where('general_ledger_accounts.account_type_id', '=', 4)
            //->whereNotIn('general_ledger_accounts.id',  [4,10])
            ->select('general_ledger_accounts.*', 'chart_of_accounts.name as account_type_name')
            ->get();

        $allAssets = array();
        $allAssetsTotal = 0;
        foreach ($allAssetsAccounts as $account) {
            $account->balance = ($this->getAccountBalance($account->id, 'debit - credit', $fromdate, $todate, '') > 0) ? $this->getAccountBalance($account->id, 'debit - credit', $fromdate, $todate, '') : 0;
            $allAssetsTotal += $account->balance;
            $allAssets[] = $account;
        }
        /**
         * all liabilities
         */
        $allLiabilitiesAccounts = DB::table('general_ledger_accounts')
            ->join('chart_of_accounts', 'general_ledger_accounts.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.id', '=', 2)
            ->select('general_ledger_accounts.*', 'chart_of_accounts.name as account_type_name')
            ->get();
        $allLiabilitiesTotal = 0;
        $allLiabilities = array();
        foreach ($allLiabilitiesAccounts as $account) {
            $account->balance = $this->getAccountBalance($account->id, 'credit - debit', $fromdate, $todate, '');
            $allLiabilitiesTotal += $account->balance;
            $allLiabilities[] = $account;
        }
        /**
         * all equity
         */
        $allEquityAccounts = DB::table('general_ledger_accounts')
            ->join('chart_of_accounts', 'general_ledger_accounts.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.id', '=', 3)
            ->select('general_ledger_accounts.*', 'chart_of_accounts.name as account_type_name')
            ->get();

        $allEquityTotal = 0;
        $allEquites = array();

        foreach ($allEquityAccounts as $account) {
            $account->balance = $this->getAccountBalance($account->id, 'credit - debit', $fromdate, $todate, '');
            $allEquityTotal += $account->balance;
            $allEquites[] = $account;
        }
        $data = $this->incomeStatment($fromdate, $todate);
        $data2 = array('allAssets' => $allAssets, 'allAssetsTotal' => $allAssetsTotal, 'allLiabilities' => $allLiabilities, 'allLiabilitiesTotal' => $allLiabilitiesTotal, 'allEquites' => $allEquites, 'allEquityTotal' => $allEquityTotal, 'netProfileLoss' => $data['netProfileLoss'], 'fromdate' => $fromdate, 'todate' => $todate);
        $pdf = PDF::loadView('ledgerAccounts.balanceSheetPdf', $data2);
        return $pdf->stream('pagePdf.pdf');
    }
    public function incomeList()
    {
        $from_date = Carbon::now()->subMonth()->format('d-m-Y');
        $to_date = date('d-m-Y');
        $data = $this->incomeStatment($from_date, $to_date);
        return view('ledgerAccounts.incomeStatement', $data);
    }
    public function incomePdf()
    {
        $from_date = Carbon::now()->subMonth()->format('d-m-Y');
        $to_date = date('d-m-Y');
        $data = $this->incomeStatment($from_date, $to_date);
        // return view('ledgerAccounts.incomeStatement',$data);
        $pdf = PDF::loadView('ledgerAccounts.incomeStatementPdf', $data);
        return $pdf->stream('pagePdf.pdf');
    }

    public function incomeListSearch(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $data = $this->incomeStatment($from_date, $to_date);
        return view('ledgerAccounts.incomeStatement', $data);
    }
    public function incomeStatment($fromdate, $todate)
    {


        /**
         * Revenu Accounts Start
         */
        $allSalesAccounts = array();
        $totalSale = 0;
        $totalOpeningStock = 0;
        $totalPurchasedStock = 0;
        $totalClosingStock = 0;
        $CostOfGoodsSold = 0;
        $totalExpense = 0;
        /**
         * Sale Invoices
         * Sale A/c Credit
         **/

        $allSalesAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.SALE_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => $this->getAccountBalance(Config::get('constants.SALE_ACCOUNT_GENERAL_LEDGER'), 'credit', $fromdate, $todate, ''),
        );
        $totalSale += $allSalesAccounts[0]['balance'];
        /**
         * Sale Return
         * Sale return A/c Debit
         **/
        $allSalesAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.SALE_RETURN_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => $this->getAccountBalance(Config::get('constants.SALE_RETURN_ACCOUNT_GENERAL_LEDGER'), 'debit', $fromdate, $todate, ''),

        );
        $totalSale -= $allSalesAccounts[1]['balance'];

        /**
         * Cost of Goods Sold Start
         */

        /**
         *
         * Opening Stock before this month
         *        Raw Material Inventory A/c
         *       +Work In process Inventory A/c
         *       +Finished Goods Inventory A/c
         *       +Other Goods Inventory A/c
         */
        $OpeningStockAccounts = array();
        $OpeningStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.RAW_MATERIALS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => ($this->getAccountBalance(Config::get('constants.RAW_MATERIALS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', $fromdate, '', '') > 0) ? ($this->getAccountBalance(Config::get('constants.RAW_MATERIALS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', $fromdate, '', '')) : 0,
        );
        $totalOpeningStock += $OpeningStockAccounts[0]['balance'];
        $OpeningStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.WORK_IN_PROCESS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => ($this->getAccountBalance(Config::get('constants.WORK_IN_PROCESS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', $fromdate, '', '') > 0) ? ($this->getAccountBalance(Config::get('constants.WORK_IN_PROCESS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', $fromdate, '', '')) : 0,
        );
        $totalOpeningStock += $OpeningStockAccounts[1]['balance'];
        $OpeningStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => ($this->getAccountBalance(Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', $fromdate, '', '') > 0) ? ($this->getAccountBalance(Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', $fromdate, '', '')) : 0,
        );
        $totalOpeningStock += $OpeningStockAccounts[2]['balance'];
        $OpeningStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.OTHER_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => ($this->getAccountBalance(Config::get('constants.OTHER_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', $fromdate, '', '') > 0) ? ($this->getAccountBalance(Config::get('constants.OTHER_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', $fromdate, '', '')) : 0,
        );
        $totalOpeningStock += $OpeningStockAccounts[3]['balance'];
        $OpeningStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.SPOILAGE_SCRAPE_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => ($this->getAccountBalance(Config::get('constants.SPOILAGE_SCRAPE_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', $fromdate, '', '') > 0) ? ($this->getAccountBalance(Config::get('constants.SPOILAGE_SCRAPE_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', $fromdate, '', '')) : 0,
        );
        $totalOpeningStock += $OpeningStockAccounts[4]['balance'];
        $PurchasedStockAccounts = array();
        /**
         *
         * Purchased with in Period
         *        Raw Material Inventory A/c
         *       +Work In process Inventory A/c
         *       +Finished Goods Inventory A/c
         *       +Other Goods Inventory A/c
         */
        $PurchasedStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.RAW_MATERIALS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => $this->getAccountBalance(Config::get('constants.RAW_MATERIALS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit', $fromdate, $todate, Config::get('constants.PURCHASE_INVOICE_PREFIX')),
        );
        $totalPurchasedStock += $PurchasedStockAccounts[0]['balance'];
        $PurchasedStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.WORK_IN_PROCESS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => $this->getAccountBalance(Config::get('constants.WORK_IN_PROCESS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit', $fromdate, $todate, Config::get('constants.PURCHASE_INVOICE_PREFIX')),
        );
        $totalPurchasedStock += $PurchasedStockAccounts[1]['balance'];
        $PurchasedStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => $this->getAccountBalance(Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit', $fromdate, $todate, Config::get('constants.PURCHASE_INVOICE_PREFIX')),
        );
        $totalPurchasedStock += $PurchasedStockAccounts[2]['balance'];
        $PurchasedStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.OTHER_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => $this->getAccountBalance(Config::get('constants.OTHER_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit', $fromdate, $todate, Config::get('constants.PURCHASE_INVOICE_PREFIX')),
        );
        $totalPurchasedStock += $PurchasedStockAccounts[3]['balance'];
        $PurchasedStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.SPOILAGE_SCRAPE_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => $this->getAccountBalance(Config::get('constants.SPOILAGE_SCRAPE_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit', $fromdate, $todate, Config::get('constants.PURCHASE_INVOICE_PREFIX')),
        );
        $totalPurchasedStock += $PurchasedStockAccounts[4]['balance'];


        $closingStockAccounts = array();
        /**
         *
         * Closing Stock End of the period
         *        Raw Material Inventory A/c
         *       +Work In process Inventory A/c
         *       +Finished Goods Inventory A/c
         *       +Other Goods Inventory A/c
         */

        $closingStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.RAW_MATERIALS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => ($this->getAccountBalance(Config::get('constants.RAW_MATERIALS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', '', $todate, '') > 0) ? ($this->getAccountBalance(Config::get('constants.RAW_MATERIALS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', '', $todate, '')) : 0,
        );
        $totalClosingStock += $closingStockAccounts[0]['balance'];
        $closingStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.WORK_IN_PROCESS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => ($this->getAccountBalance(Config::get('constants.WORK_IN_PROCESS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', '', $todate, '') > 0) ? ($this->getAccountBalance(Config::get('constants.WORK_IN_PROCESS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', '', $todate, '')) : 0,
        );
        $totalClosingStock += $closingStockAccounts[1]['balance'];
        $closingStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => ($this->getAccountBalance(Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', '', $todate, '') > 0) ? ($this->getAccountBalance(Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', '', $todate, '')) : 0,
        );
        $totalClosingStock += $closingStockAccounts[2]['balance'];
        $closingStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.OTHER_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => ($this->getAccountBalance(Config::get('constants.OTHER_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', '', $todate, '') > 0) ? ($this->getAccountBalance(Config::get('constants.OTHER_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', '', $todate, '')) : 0,
        );
        $totalClosingStock += $closingStockAccounts[3]['balance'];
        $closingStockAccounts[] = array(
            'name' => $this->getAccountInfo(Config::get('constants.SPOILAGE_SCRAPE_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'))->name,
            'balance' => ($this->getAccountBalance(Config::get('constants.SPOILAGE_SCRAPE_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', '', $todate, '') > 0) ? ($this->getAccountBalance(Config::get('constants.SPOILAGE_SCRAPE_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', '', $todate, '')) : 0,
        );
        $totalClosingStock += $closingStockAccounts[4]['balance'];


        $CostOfGoodsSold = $totalOpeningStock +  $totalPurchasedStock - $totalClosingStock;
        /**
         * all expenses
         */
        $expenseAccounts = DB::table('general_ledger_accounts')
            ->join('chart_of_accounts', 'general_ledger_accounts.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.id', '=', 5)
            ->where('general_ledger_accounts.account_type_id', '=', 4)
            ->whereNotIn('general_ledger_accounts.id',  [4, 10]) //don't include Purchase A/c and Purchase Return A/c
            ->select('general_ledger_accounts.*', 'chart_of_accounts.name as account_type_name')
            ->get();
        $allExpensesAccounts = array();
        foreach ($expenseAccounts as $account) {
            $allExpensesAccounts[] = array(
                'name' => $account->name,
                'balance' => $this->getAccountBalance($account->id, 'debit', $fromdate, $todate, ''),
            );
            $totalExpense += $this->getAccountBalance($account->id, 'debit', $fromdate, $todate, '');
        }
        $netProfileLoss = $totalSale -  $CostOfGoodsSold -  $totalExpense;
        return  array('allSalesAccounts' => $allSalesAccounts, 'totalSale' => $totalSale, 'OpeningStockAccounts' => $OpeningStockAccounts, 'totalOpeningStock' => $totalOpeningStock, 'PurchasedStockAccounts' => $PurchasedStockAccounts, 'totalPurchasedStock' => $totalPurchasedStock, 'closingStockAccounts' => $closingStockAccounts, 'totalClosingStock' => $totalClosingStock, 'CostOfGoodsSold' => $CostOfGoodsSold, 'allExpensesAccounts' => $allExpensesAccounts, 'totalExpense' => $totalExpense, 'netProfileLoss' => $netProfileLoss, 'from_date' => $fromdate, 'to_date' => $todate);
    }
    public function getAccountInfo($general_ledger_account_id)
    {
        $Account = DB::table('general_ledger_accounts')
            ->where('id', '=', $general_ledger_account_id)
            ->first();
        return $Account;
    }
    public function accountReceivable()
    {

        $listAccounts = DB::table('general_ledger_accounts')
            ->where('branch', Auth::user()->branch)
            ->where('chart_of_accounts_category_id', Config::get('constants.CUSTOMER_CHART_OF_ACCOUNT_CATEGORY_ID'))
            ->orderByDesc('id')->paginate(20);
        $receivables = array();
        if (!empty($listAccounts)) {
            foreach ($listAccounts as $account) {
                $journal_entry_rule = $this->getAccountjournalentryrule($account->id);
                $journal_sum_rule = 'debit - credit';
                if ($journal_entry_rule == 'credit') {
                    $journal_sum_rule = 'credit - debit';
                }
                $account->balance = $this->getAccountBalance($account->id, $journal_sum_rule, '', Carbon::now()->format('d-m-Y'), '');
                $chart_of_account = DB::table('chart_of_accounts')
                    ->where('id', $account->chart_of_account_id)->first();
                $account_type = DB::table('general_ledger_accounts_types')
                    ->where('id', $account->account_type_id)->first();
                $chart_of_accounts_category = DB::table('chart_of_accounts_category')
                    ->where('id', $account->chart_of_accounts_category_id)->first();
                $account->chart_name = $chart_of_account->name;
                $account->ledger_account_name = $account_type->name;
                $account->accounts_category_name = $chart_of_accounts_category->name;
                $receivables[] = $account;
            }
        }
        $netBalance = $this->getAccountBalanceByCategory(Config::get('constants.CUSTOMER_CHART_OF_ACCOUNT_CATEGORY_ID'), 'debit - credit');
        /**
         * listAccounts use to show pagination
         */
        return view('ledgerAccounts.accountReceivable', array('receivables' => $receivables, 'listAccounts' => $listAccounts, 'netBalance' => $netBalance));
    }

    // Account Receiveable search
    public function searchReceiveable(Request $request)
    {
        $listAccounts = DB::table('general_ledger_accounts')
            ->where('branch', Auth::user()->branch)
            ->where('name', 'like', "%$request->receiveable%")
            ->where('chart_of_accounts_category_id', Config::get('constants.CUSTOMER_CHART_OF_ACCOUNT_CATEGORY_ID'))
            ->orderByDesc('id')->paginate(20);
        $listAccounts->appends($request->receiveable);
        $receivables = array();
        if (!empty($listAccounts)) {
            foreach ($listAccounts as $account) {
                $journal_entry_rule = $this->getAccountjournalentryrule($account->id);
                $journal_sum_rule = 'debit - credit';
                if ($journal_entry_rule == 'credit') {
                    $journal_sum_rule = 'credit - debit';
                }
                $account->balance = $this->getAccountBalance($account->id, $journal_sum_rule, '', Carbon::now()->format('d-m-Y'), '');
                $chart_of_account = DB::table('chart_of_accounts')
                    ->where('id', $account->chart_of_account_id)->first();
                $account_type = DB::table('general_ledger_accounts_types')
                    ->where('id', $account->account_type_id)->first();
                $chart_of_accounts_category = DB::table('chart_of_accounts_category')
                    ->where('id', $account->chart_of_accounts_category_id)->first();
                $account->chart_name = $chart_of_account->name;
                $account->ledger_account_name = $account_type->name;
                $account->accounts_category_name = $chart_of_accounts_category->name;
                $receivables[] = $account;
            }
        }
        $netBalance = $this->getAccountBalanceByCategory(Config::get('constants.CUSTOMER_CHART_OF_ACCOUNT_CATEGORY_ID'), 'debit - credit');
        /**
         * listAccounts use to show pagination
         */
        return view('ledgerAccounts.accountReceivable', array('receivables' => $receivables, 'listAccounts' => $listAccounts, 'netBalance' => $netBalance, 'searchQuery' => $request->receiveable));
    }

    // Account Receiveable page pdf
    public function receiveablePagePdf($searchQuery)
    {
        $query = DB::table('general_ledger_accounts')
            ->where('branch', Auth::user()->branch);
        if ($searchQuery != 'none') {
            $query->where('name', 'like', "%$searchQuery%");
        }
        $listAccounts = $query->where('chart_of_accounts_category_id', Config::get('constants.CUSTOMER_CHART_OF_ACCOUNT_CATEGORY_ID'))
            ->orderByDesc('id')->paginate(20);
        $receivables = array();
        if (!empty($listAccounts)) {
            foreach ($listAccounts as $account) {
                $journal_entry_rule = $this->getAccountjournalentryrule($account->id);
                $journal_sum_rule = 'debit - credit';
                if ($journal_entry_rule == 'credit') {
                    $journal_sum_rule = 'credit - debit';
                }
                $account->balance = $this->getAccountBalance($account->id, $journal_sum_rule, '', Carbon::now()->format('d-m-Y'), '');
                $chart_of_account = DB::table('chart_of_accounts')
                    ->where('id', $account->chart_of_account_id)->first();
                $account_type = DB::table('general_ledger_accounts_types')
                    ->where('id', $account->account_type_id)->first();
                $chart_of_accounts_category = DB::table('chart_of_accounts_category')
                    ->where('id', $account->chart_of_accounts_category_id)->first();
                $account->chart_name = $chart_of_account->name;
                $account->ledger_account_name = $account_type->name;
                $account->accounts_category_name = $chart_of_accounts_category->name;
                $receivables[] = $account;
            }
        }
        $netBalance = $this->getAccountBalanceByCategory(Config::get('constants.CUSTOMER_CHART_OF_ACCOUNT_CATEGORY_ID'), 'debit - credit');
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;
        $data =  array('receivables' => $receivables, 'listAccounts' => $listAccounts, 'netBalance' => $netBalance, 'companyinfo' => $companyinfo);
        $pdf = PDF::loadView('ledgerAccounts.receiveablePdf', $data);
        // return $pdf->download('salePdf.pdf');
        return $pdf->stream('pagePdf.pdf');
    }

    public function accountPayable()
    {

        $listAccounts = DB::table('general_ledger_accounts')
            ->where('branch', Auth::user()->branch)
            ->where('chart_of_accounts_category_id', Config::get('constants.VENDOR_CHART_OF_ACCOUNT_CATEGORY_ID'))
            ->orderByDesc('id')->paginate(20);
        $payables = array();
        if (!empty($listAccounts)) {
            foreach ($listAccounts as $account) {
                $journal_entry_rule = $this->getAccountjournalentryrule($account->id);
                $journal_sum_rule = 'debit - credit';
                if ($journal_entry_rule == 'credit') {
                    $journal_sum_rule = 'credit - debit';
                }
                $account->balance = $this->getAccountBalance($account->id, $journal_sum_rule, '', Carbon::now()->format('d-m-Y'), '');
                $chart_of_account = DB::table('chart_of_accounts')
                    ->where('id', $account->chart_of_account_id)->first();
                $account_type = DB::table('general_ledger_accounts_types')
                    ->where('id', $account->account_type_id)->first();
                $chart_of_accounts_category = DB::table('chart_of_accounts_category')
                    ->where('id', $account->chart_of_accounts_category_id)->first();
                $account->chart_name = $chart_of_account->name;
                $account->ledger_account_name = $account_type->name;
                $account->accounts_category_name = $chart_of_accounts_category->name;
                $payables[] = $account;
            }
        }
        $netBalance = $this->getAccountBalanceByCategory(Config::get('constants.VENDOR_CHART_OF_ACCOUNT_CATEGORY_ID'), 'credit - debit');
        /**
         * listAccounts use to show pagination
         */
        return view('ledgerAccounts.accountPayable', array('payables' => $payables, 'listAccounts' => $listAccounts, 'netBalance' => $netBalance));
    }

    // Account Payable search
    public function searchAccountPayable(Request $request)
    {

        $listAccounts = DB::table('general_ledger_accounts')
            ->where('branch', Auth::user()->branch)
            ->where('name', 'like', "%$request->payableSearchQuery%")
            ->where('chart_of_accounts_category_id', Config::get('constants.VENDOR_CHART_OF_ACCOUNT_CATEGORY_ID'))
            ->orderByDesc('id')->paginate(20);
        $payables = array();
        if (!empty($listAccounts)) {
            foreach ($listAccounts as $account) {
                $journal_entry_rule = $this->getAccountjournalentryrule($account->id);
                $journal_sum_rule = 'debit - credit';
                if ($journal_entry_rule == 'credit') {
                    $journal_sum_rule = 'credit - debit';
                }
                $account->balance = $this->getAccountBalance($account->id, $journal_sum_rule, '', Carbon::now()->format('d-m-Y'), '');
                $chart_of_account = DB::table('chart_of_accounts')
                    ->where('id', $account->chart_of_account_id)->first();
                $account_type = DB::table('general_ledger_accounts_types')
                    ->where('id', $account->account_type_id)->first();
                $chart_of_accounts_category = DB::table('chart_of_accounts_category')
                    ->where('id', $account->chart_of_accounts_category_id)->first();
                $account->chart_name = $chart_of_account->name;
                $account->ledger_account_name = $account_type->name;
                $account->accounts_category_name = $chart_of_accounts_category->name;
                $payables[] = $account;
            }
        }
        $netBalance = $this->getAccountBalanceByCategory(Config::get('constants.VENDOR_CHART_OF_ACCOUNT_CATEGORY_ID'), 'credit - debit');
        /**
         * listAccounts use to show pagination
         */
        return view('ledgerAccounts.accountPayable', array('payables' => $payables, 'listAccounts' => $listAccounts, 'netBalance' => $netBalance, 'searchQuery' => $request->payableSearchQuery));
    }
    // Account Payable pdf
    public function payablePagePdf($searchQuery)
    {
        $query = DB::table('general_ledger_accounts')->where('branch', Auth::user()->branch);
        if ($searchQuery != 'none') {
            $query->where('name', 'like', "%$searchQuery%");
        }
        $listAccounts = $query->where('chart_of_accounts_category_id', Config::get('constants.VENDOR_CHART_OF_ACCOUNT_CATEGORY_ID'))
            ->orderByDesc('id')->paginate(20);
        $payables = array();
        if (!empty($listAccounts)) {
            foreach ($listAccounts as $account) {
                $journal_entry_rule = $this->getAccountjournalentryrule($account->id);
                $journal_sum_rule = 'debit - credit';
                if ($journal_entry_rule == 'credit') {
                    $journal_sum_rule = 'credit - debit';
                }
                $account->balance = $this->getAccountBalance($account->id, $journal_sum_rule, '', Carbon::now()->format('d-m-Y'), '');
                $chart_of_account = DB::table('chart_of_accounts')
                    ->where('id', $account->chart_of_account_id)->first();
                $account_type = DB::table('general_ledger_accounts_types')
                    ->where('id', $account->account_type_id)->first();
                $chart_of_accounts_category = DB::table('chart_of_accounts_category')
                    ->where('id', $account->chart_of_accounts_category_id)->first();
                $account->chart_name = $chart_of_account->name;
                $account->ledger_account_name = $account_type->name;
                $account->accounts_category_name = $chart_of_accounts_category->name;
                $payables[] = $account;
            }
        }
        $netBalance = $this->getAccountBalanceByCategory(Config::get('constants.VENDOR_CHART_OF_ACCOUNT_CATEGORY_ID'), 'credit - debit');
        /**
         * listAccounts use to show pagination
         */
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;
        $data =  array('payables' => $payables, 'listAccounts' => $listAccounts, 'netBalance' => $netBalance, 'companyinfo' => $companyinfo);
        $pdf = PDF::loadView('ledgerAccounts.payAblePdf', $data);
        return $pdf->stream('pagePdf.pdf');
    }
    public function getAccountBalance($general_ledger_account_id, $journal_sum_rule, $fromdate, $todate, $voucher_number_prefix)
    {

        /***
         * Default Begining Date will be last day from today
         * and Ending date will be today date
         */
        $query = DB::table('general_ledger_transactions')
            ->where('general_ledger_account_id', $general_ledger_account_id)
            ->where('branch', Auth::user()->branch);
        /***Begining Balance**/
        if (!empty($fromdate) && empty($todate)) {
            $query = $query->where('voucher_date', '<=', $fromdate);
        }
        /***Ending Balance**/
        if (empty($fromdate) && !empty($todate)) {
            $query = $query->where('voucher_date', '<=', $todate);
        }
        /***From date to End date Balance**/
        if (!empty($fromdate) && !empty($todate)) {
            $query = $query->where('voucher_date', '>=', $fromdate);
            $query = $query->where('voucher_date', '<=', $todate);
        }
        /***Specfic Voucher Type **/
        if (!empty($voucher_number_prefix)) {
            $query = $query->where('voucher_number', 'like', '' . $voucher_number_prefix . '%');
        }
        $endingBalance = $query->sum(\DB::raw($journal_sum_rule));

        return $endingBalance;
    }




    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
    public function getAccountBalanceByCategory($chart_of_accounts_category_id, $journal_sum_rule)
    {

        $accountBalance = DB::table('general_ledger_accounts')
            ->join('general_ledger_transactions', 'general_ledger_accounts.id', '=', 'general_ledger_transactions.general_ledger_account_id')
            ->select(DB::raw('SUM(' . $journal_sum_rule . ')  As balance'))
            ->where('general_ledger_accounts.chart_of_accounts_category_id', $chart_of_accounts_category_id)
            ->first();

        return $accountBalance->balance;
    }
}
