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

class generalReceiptsController extends Controller
{
    public function list()
    {
        $list = DB::table('general_receipts')->where('branch', Auth::user()->branch)->orderByDesc('id')->paginate(20);
        return view('generalreceipts.list', array('lists' => $list));
    }

    public function new()
    {
        /**
         * Get Only Accounts thoes have type General Ledger
         * but not default accounts
         */
        $ledgerAccounts = DB::table('general_ledger_accounts')
            ->where('account_type_id', 4)
            ->where('branch', Auth::user()->branch)
            ->where('default_account', 1)->get();
        $invoice_number = DB::table('general_receipts')->max('id') + 1;
        return view('generalreceipts.new', array('invoice_number' => $invoice_number, 'accounts' => $ledgerAccounts));
    }

    public function store(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');


        $validator = Validator::make(
            $request->all(),
            [
                'voucher_number' => 'required',
                'voucher_date' => 'required',
                'net_total' => 'required|numeric|min:0|not_in:0',
                'note' => 'required'
            ],
            [
                'voucher_number.required' => 'The Invoice #  is required.',
                'voucher_date.required' => 'The Invoice Date  is required.',
                'net_total.required' => 'Net Total   is required.',
                'note.required' => 'Notes   is required.'
            ]
        );
        if ($validator->fails()) {
            //$response['message'] = $validator->messages();
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {

            $voucher_detail = array();
            $general_ledger_account_ids = $request->general_ledger_account_id;
            $descriptions = $request->description;
            $account_amounts = $request->amount;
            $i = 0;
            foreach ($general_ledger_account_ids as $item) {
                $general_ledger_account_id = $general_ledger_account_ids[$i];
                $description = $descriptions[$i];
                $amount = $account_amounts[$i];
                if ($amount > 0) {
                    $voucher_detail[] = array(
                        'general_ledger_account_id' => $general_ledger_account_id,
                        'description' => $description,
                        'amount' => $amount,
                    );
                }

                $i++;
            }
            if (!empty($voucher_detail)) {
                foreach ($voucher_detail as $detail) {
                    /**
                     * Insert Double entry
                     *Cash A/c Debit
                     *General Ledger A/c  Credit
                     */
                    $debit = array(
                        'voucher_date' => $request->voucher_date,
                        'voucher_number' => $request->voucher_number,
                        'general_ledger_account_id' => Config::get('constants.CASH_ACCOUNT_GENERAL_LEDGER'),
                        'note' => $detail['description'],
                        'debit' => $detail['amount'],
                        'credit' => 0,
                        'branch' => Auth::user()->branch,
                        'created_at' => date('Y-m-d H:i:s'),
                    );
                    $this->insertDoubleEntry($debit);
                    $credit = array(
                        'voucher_date' => $request->voucher_date,
                        'voucher_number' => $request->voucher_number,
                        'general_ledger_account_id' => $detail['general_ledger_account_id'],
                        'note' => $detail['description'],
                        'debit' => 0,
                        'credit' => $detail['amount'],
                        'branch' => Auth::user()->branch,
                        'created_at' => date('Y-m-d H:i:s'),
                    );
                    $this->insertDoubleEntry($credit);
                }
            }

            $general_receipts = array(
                'net_total' => $request->net_total,
                'created_at' => date('Y-m-d H:i:s'),
                'voucher_detail' => serialize($voucher_detail),
                'voucher_number' => $request->voucher_number,
                'note' => $request->note,
                'voucher_date' => $request->voucher_date,
                'branch' => Auth::user()->branch,
            );
            DB::table('general_receipts')->insert($general_receipts);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->voucher_number,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($general_receipts),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'General Receipts',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'General Receipts added successfully..', 'redirectUrl' => '/generalReciepts/list'], 200);
        }
    }


    public function edit($id)
    {
        $generalReceipts = DB::table('general_receipts')->where('id', $id)->first();
        $ledgerAccounts = DB::table('general_ledger_accounts')
            ->where('account_type_id', 4)
            ->where('branch', Auth::user()->branch)
            ->where('default_account', 1)->get();
        return view('generalreceipts.new', array('generalReceipts' => $generalReceipts, 'accounts' => $ledgerAccounts));
    }



    public function update(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $sale = DB::table('sales')->where('invoice_number', $request->invoice_number)->first();
        if (!empty($sale)) {
            return response()->json(['success' => false, 'message' => 'Sale Invoice already exits..', 'redirectUrl' => '/sales/list'], 200);
        }


        $validator = Validator::make(
            $request->all(),
            [
                'voucher_number' => 'required',
                'voucher_date' => 'required',
                'net_total' => 'required|numeric|min:0|not_in:0',
                'note' => 'required'
            ],
            [
                'voucher_number.required' => 'The Invoice #  is required.',
                'voucher_date.required' => 'The Invoice Date  is required.',
                'net_total.required' => 'Net Total   is required.',
                'note.required' => 'Notes   is required.'
            ]
        );
        if ($validator->fails()) {
            //$response['message'] = $validator->messages();
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {

            $voucher_detail = array();
            $general_ledger_account_ids = $request->general_ledger_account_id;
            $descriptions = $request->description;
            $account_amounts = $request->amount;
            $i = 0;
            foreach ($general_ledger_account_ids as $item) {
                $general_ledger_account_id = $general_ledger_account_ids[$i];
                $description = $descriptions[$i];
                $amount = $account_amounts[$i];
                if ($amount > 0) {
                    $voucher_detail[] = array(
                        'general_ledger_account_id' => $general_ledger_account_id,
                        'description' => $description,
                        'amount' => $amount,
                    );
                }

                $i++;
            }
            /**
             *  Delete all general entries first then Insert new
             */
            $this->deleteDoubleEntry($request->voucher_number);
            if (!empty($voucher_detail)) {

                foreach ($voucher_detail as $detail) {
                    /**
                     *
                     * Insert Double entry
                     *Cash A/c Debit
                     *General Ledger A/c  Credit
                     */
                    $debit = array(
                        'voucher_date' => $request->voucher_date,
                        'voucher_number' => $request->voucher_number,
                        'general_ledger_account_id' => Config::get('constants.CASH_ACCOUNT_GENERAL_LEDGER'),
                        'note' => $detail['description'],
                        'debit' => $detail['amount'],
                        'credit' => 0,
                        'branch' => Auth::user()->branch,
                        'created_at' => date('Y-m-d H:i:s'),
                    );
                    $this->insertDoubleEntry($debit);
                    $credit = array(
                        'voucher_date' => $request->voucher_date,
                        'voucher_number' => $request->voucher_number,
                        'general_ledger_account_id' => $detail['general_ledger_account_id'],
                        'note' => $detail['description'],
                        'debit' => 0,
                        'credit' => $detail['amount'],
                        'branch' => Auth::user()->branch,
                        'created_at' => date('Y-m-d H:i:s'),
                    );
                    $this->insertDoubleEntry($credit);
                }
            }
            $general_receipts = array(
                'net_total' => $request->net_total,
                'created_at' => date('Y-m-d H:i:s'),
                'voucher_detail' => serialize($voucher_detail),
                'voucher_number' => $request->voucher_number,
                'note' => $request->note,
                'voucher_date' => $request->voucher_date,
                'branch' => Auth::user()->branch,
            );
            DB::table('general_receipts')->where('id', $request->id)->update($general_receipts);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->voucher_number,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($general_receipts),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'General Receipts',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'General Receipts updated successfully..', 'redirectUrl' => '/generalReciepts/list'], 200);
        }
    }


    public function delete($id)
    {
        $gReceipt = DB::table('general_receipts')->where('id', $id)->first();
        $this->deleteDoubleEntry($gReceipt->voucher_number);
        DB::table('general_receipts')->where('id', $id)->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $gReceipt->voucher_number,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($gReceipt),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'General Receipts',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        return response()->json(['success' => true, 'message' => 'General Receipt deleted successfully..', 'redirectUrl' => '/generalReciepts/list'], 200);
    }


    public function search(Request $request)
    {
        $Queries = array();
        if (empty($request->from_date) && empty($request->to_date) && empty($request->customer_name) && empty($request->invoice_number)) {
            return redirect('generalReceipts/list');
        }
        $query = DB::table('general_receipts');
        if (isset($request->invoice_number) && !empty($request->invoice_number)) {
            $Queries['invoice_number'] = $request->invoice_number;
            $query->where('general_receipts.voucher_number', 'like', "%$request->invoice_number%");
        }
        if (isset($request->from_date) && isset($request->to_date)) {
            $Queries['from_date'] = $request->from_date;
            $Queries['to_date'] = $request->to_date;
            $query->whereBetween('general_receipts.voucher_date', [$request->from_date, $request->to_date]);
        }

        $result = $query->where('branch', Auth::user()->branch)->orderByDesc('id')->paginate(20);
        $result->appends($Queries);
        return view('generalreceipts.list', array('lists' => $result, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'invoice_number' => $request->invoice_number));
    }



    public function recordPdf($id)
    {
        $generalReceipts = DB::table('general_receipts')->where('id', $id)->first();
        $ledgerAccounts = DB::table('general_ledger_accounts')
            ->where('account_type_id', 4)
            ->where('branch', Auth::user()->branch)
            ->where('default_account', 1)->get();
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;
        $data = array('generalReceipts' => $generalReceipts, 'accounts' => $ledgerAccounts, 'companyinfo' => $companyinfo);
        $pdf = PDF::loadView('generalreceipts.recordPdf', $data);
        return $pdf->stream('pagePdf.pdf');
    }

    public function pagePdf($from_date, $to_date, $invoice_number)
    {
        $query = DB::table('general_receipts');

        if ($from_date != 'none' && $to_date != 'none') {
            $query->whereBetween('general_receipts.voucher_date', [$from_date, $to_date]);
        }
        if ($invoice_number != 'none') {
            $query->where('general_receipts.voucher_number', 'like', "%$invoice_number%");
        }
        $list = $query->get();


        $net = $query->sum('net_total');
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;
        $data = array('lists' => $list, 'net' => $net, 'companyinfo' => $companyinfo);
        $pdf = PDF::loadView('generalreceipts.generalRecPagePdf', $data);
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
