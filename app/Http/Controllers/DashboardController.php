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

class DashboardController extends Controller
{

    public function welcome()
    {
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;
        return view('welcome', array('companyinfo' => $companyinfo));
    }
    public function index()
    {
        /**
         * Calculate Net Sales
         **/
        // if (Auth::user()->is_admin === 0) {
        //     return redirect('quotation/list');
        // };
        $now = now();
        $Sales = DB::table('general_ledger_transactions')
            ->where('general_ledger_account_id', Config::get('constants.SALE_ACCOUNT_GENERAL_LEDGER'))
            ->where('branch', Auth::user()->branch)
            ->where('created_at', '>=', $now->subDay())
            ->sum(\DB::raw('credit'));
        $SalesReturn = DB::table('general_ledger_transactions')
            ->where('general_ledger_account_id', Config::get('constants.SALE_RETURN_ACCOUNT_GENERAL_LEDGER'))
            ->where('branch', Auth::user()->branch)
            ->where('created_at', '>=', $now->subDay())
            ->sum(\DB::raw('debit'));
        $netSales = $Sales - $SalesReturn;
        /**
         * Active Customers
         **/
        if (Auth::user()->is_admin === 0) {
            $customers = DB::table('customers')->where('status', 1)->where('user_id', Auth::user()->id)->count('*');
            $quotation = DB::table('quotation')->where('user_id', Auth::user()->id)->count('*');
            $daily_visits = DB::table('daily_visits')->where('user_id', Auth::user()->id)->count('*');
        } else {
            $customers = DB::table('customers')->where('status', 1)->count('*');
            $quotation = DB::table('quotation')->count('*');
            $daily_visits = DB::table('daily_visits')->count('*');
        }
        /**
         * Active Vendors
         **/
        $vendors = DB::table('vendors')->where('status', 1)->count('*');

        /**
         * Sale Invoices
         * Sale A/c Credit
         **/

        /**
         * Last 7 Month
         **/
        $fromdate = Carbon::now()->subMonth(6)->format('Y-m-d');
        $todate = date('Y-m-d');
        $MonthWiseSales = $this->getMontlyAccountBalance(Config::get('constants.SALE_ACCOUNT_GENERAL_LEDGER'), 'credit', $fromdate, $todate, '');
        $MonthWiseSalesReturn = $this->getMontlyAccountBalance(Config::get('constants.SALE_RETURN_ACCOUNT_GENERAL_LEDGER'), 'debit', $fromdate, $todate, '');
        $filterPurchaseMonth = array();
        $filterSalesMonth = array();
        $MonthData = array();
        $SalesData = array();
        $PurchaseData = array();
        $Expensesdata = array();

        if (!empty($MonthWiseSales)) {

            /**
             * create new array set month  as key
             */
            foreach ($MonthWiseSales as $sale) {

                if (property_exists($sale, 'month')) {
                    $filterSalesMonth[$sale->month] = $sale->balance;
                }
            }

            /**
             * subtract the sale return for each month
             */
            if (!empty($MonthWiseSalesReturn)) {
                foreach ($MonthWiseSalesReturn as $return) {
                    if (array_key_exists($return->month, $filterSalesMonth)) {
                        $filterSalesMonth[$return->month] = $filterSalesMonth[$return->month] - $return->balance;
                    }
                }
            }
            foreach ($filterSalesMonth as $key => $sale) {
                //$Month[]=$this->getMonthName($key);
                $MonthData[] = $key;
                $SalesData[] = $sale;
            }

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


            $filterExpensesMonth = array();
            $ExpensesData = array();

            foreach ($expenseAccounts as $account) {
                $monthwiseExpense = $this->getMontlyAccountBalance($account->id, 'debit', $fromdate, $todate, '');
                if (!empty($monthwiseExpense)) {
                    foreach ($monthwiseExpense as $expense) {
                        if (property_exists($expense, 'month')) {
                            $filterExpensesMonth[$expense->month] = $expense->balance;
                        }
                    }
                }
            }
            if (!empty($filterExpensesMonth)) {
                foreach ($MonthData as $month) {
                    if (array_key_exists($month, $filterExpensesMonth)) {
                        $ExpensesData[] = $filterExpensesMonth[$month];
                    }
                }
            }



            $MonthWisePurchases = $this->getMontlyAccountBalance(Config::get('constants.PURCHASE_ACCOUNT_GENERAL_LEDGER'), 'debit', $fromdate, $todate, '');
            $MonthWisePurchasesReturn = $this->getMontlyAccountBalance(Config::get('constants.PURCHASE_RETURN_ACCOUNT_GENERAL_LEDGER'), 'credit', $fromdate, $todate, '');
            foreach ($MonthWisePurchases as $purchase) {
                if (property_exists($purchase, 'month')) {
                    $filterPurchaseMonth[$purchase->month] = $purchase->balance;
                }
            }
            /**
             * subtract the Purchase return for each month
             */
            if (!empty($MonthWisePurchasesReturn)) {
                foreach ($MonthWisePurchasesReturn as $return) {
                    if (array_key_exists($return->month, $filterPurchaseMonth)) {
                        $filterPurchaseMonth[$return->month] = $filterPurchaseMonth[$return->month] - $return->balance;
                    }
                }
            }
            if (!empty($filterPurchaseMonth)) {
                foreach ($MonthData as $month) {
                    if (array_key_exists($month, $filterPurchaseMonth)) {
                        $PurchaseData[] = $filterPurchaseMonth[$month];
                    }
                }
            }
        }
        /***
         * top 10 sellers
         */
        $top10SalesCustomersBalances = $this->getTopSaleCustomers();
        $top10SalesCustomers = array();
        if (!empty($top10SalesCustomersBalances)) {
            foreach ($top10SalesCustomersBalances as $key => $CustomersSales) {
                $Customer = DB::table('customers')
                    ->where('id', '=', $key)
                    ->first();
                $Customer->sale = $CustomersSales;
                $top10SalesCustomers[] = $Customer;
            }
        }
        $users = DB::table('users')->where('email', '!=', 'info@quadacts.com')->orderByDesc('id')->paginate(5);
        $data = array(
            'netSales' => $netSales,
            'customers' => $customers,
            'vendors' => $vendors,
            'month' => $MonthData,
            'sales' => $SalesData,
            'expense' => $ExpensesData,
            'purchase' => $PurchaseData,
            'top10SalesCustomers' => $top10SalesCustomers,
            'totalCash' => $this->getAccountBalance(Config::get('constants.CASH_ACCOUNT_GENERAL_LEDGER'), 'debit - credit', '', '', ''),
            'accountReceivable' => $this->getAccountBalanceByCategory(Config::get('constants.CUSTOMER_CHART_OF_ACCOUNT_CATEGORY_ID'), 'debit - credit'),
            'accountPayable' => $this->getAccountBalanceByCategory(Config::get('constants.VENDOR_CHART_OF_ACCOUNT_CATEGORY_ID'), 'credit - debit'),
            'users' => $users,
            'quotation' => $quotation,
            'daily_visits' => $daily_visits
        );

        $todo = DB::table('to_do')->where('user_id', Auth::user()->id)->where('priority', '1')->get();
        $inProgress = DB::table('to_do')->where('user_id', Auth::user()->id)->where('priority', '2')->get();
        $ready = DB::table('to_do')->where('user_id', Auth::user()->id)->where('priority', '3')->get();
        $complete = DB::table('to_do')->where('user_id', Auth::user()->id)->where('priority', '4')->get();

        $currentDate = Carbon::now()->startOfDay();
        // $threeDaysFromNow = $currentDate->copy()->addDays(3)->endOfDay();

        $customerReceipts = DB::table('customer_receipt')
            ->join('customers', 'customers.id', '=', 'customer_receipt.customer')
            ->where('customer_receipt.payment_mode', 3)
            ->whereDate('customer_receipt.received_date', '>=', $currentDate)
            // ->whereBetween('customer_receipt.received_date', [$currentDate, $threeDaysFromNow])
            ->get();
        return view('dashboard', array('data' => $data, 'todo' => $todo, 'inprogress' => $inProgress, 'ready' => $ready, 'complete' => $complete, 'customerReceipts' => $customerReceipts));
    }
    public function getMontlyAccountBalance($general_ledger_account_id, $journal_sum_rule, $fromdate, $todate, $voucher_number_prefix)
    {
        /***
         * Default Begining Date will be last day from today
         * and Ending date will be today date
         */

        $groupedBalance = DB::table('general_ledger_transactions')
            ->where('voucher_date', '>=', $fromdate)
            ->where('voucher_date', '<=', $todate)
            ->where('general_ledger_account_id', '=', $general_ledger_account_id)
            ->selectRaw(
                'MONTH(voucher_date) as month,
        SUM(' . $journal_sum_rule . ') AS balance',
            )->groupByRaw('MONTH(voucher_date)')->get();


        return $groupedBalance;
    }
    public function getTopSaleCustomers()
    {
        /***
         * Default Begining Date will be last day from today
         * and Ending date will be today date
         */

        $CustomerSales = array();
        $CustomerSalesGroup = DB::table('customers')
            ->join('general_ledger_transactions', 'customers.general_ledger_account_id', '=', 'general_ledger_transactions.general_ledger_account_id')
            ->select('customers.id as customerid', DB::raw('SUM(general_ledger_transactions.debit) As sale'))
            ->groupBy('customerid')
            ->orderBy('sale', 'desc')->limit(10)
            ->get();
        if (!empty($CustomerSalesGroup)) {
            foreach ($CustomerSalesGroup as $SalesGroup) {
                $CustomerSalesReturn = DB::table('general_ledger_transactions')
                    ->where('general_ledger_account_id', '=', $SalesGroup->customerid)
                    ->where('general_ledger_transactions.voucher_number', 'LIKE', '' . Config::get('constants.SALE_INVOICE_RETURN_PREFIX') . '%')
                    ->sum('credit');
                $CustomerSales[$SalesGroup->customerid] = $SalesGroup->sale - $CustomerSalesReturn;
            }
        }

        return $CustomerSales;
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
    public function getAccountBalanceByCategory($chart_of_accounts_category_id, $journal_sum_rule)
    {

        $accountBalance = DB::table('general_ledger_accounts')
            ->join('general_ledger_transactions', 'general_ledger_accounts.id', '=', 'general_ledger_transactions.general_ledger_account_id')
            ->select(DB::raw('SUM(' . $journal_sum_rule . ')  As balance'))
            ->where('general_ledger_accounts.chart_of_accounts_category_id', $chart_of_accounts_category_id)
            ->first();

        return $accountBalance->balance;
    }

    function getMonthName($monthNumber)
    {
        return date("F", mktime(0, 0, 0, $monthNumber, 1));
    }

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
}
