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



class journalVoucherController extends Controller
{
    public function list()
    {
        $list = DB::table('journal_voucher')
            ->where('branch', Auth::user()->branch)
            ->orderByDesc('id')
            ->paginate(20);
        return view('journalVoucher.list', array('lists' => $list));
    }

    public function new()
    {
        $ledgerAccounts = DB::table('general_ledger_accounts')
            //->where('default_account',0)
            ->where('status', 1)
            // ->where('account_type_id',4)
            ->where('branch', Auth::user()->branch)
            ->get();
        $invoice_number = DB::table('journal_voucher')->max('id') + 1;
        return view('journalVoucher.new', array('invoice_number' => $invoice_number, 'accounts' => $ledgerAccounts));
    }

    public function store(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');

        $validator = Validator::make(
            $request->all(),
            [
                'voucher_number' => 'required',
                'voucher_date' => 'required',
                'debit_general_ledger_account_id' => 'required',
                'debit_description' => 'required',
                'debit' => 'required|numeric|min:0|not_in:0|same:credit',
                'credit_general_ledger_account_id' => 'required',
                'credit_description' => 'required',
                'credit' => 'required|numeric|min:0|not_in:0|same:debit',
            ],
            [
                'voucher_number.required' => 'The Invoice #  is required.',
                'voucher_date.required' => 'The Invoice Date  is required.',
                'debit_general_ledger_account_id' => 'The Debit Account   is required.',
                'debit_description' => 'The Debit Description   is required.',
                'debit' => 'The Debit Amount should be equal Credit and is required.',
                'credit_general_ledger_account_id' => 'The Credit Account   is required.',
                'credit_description' => 'The Credit description   is required.',
                'credit' => 'The Credit Amount should be equal to debit  and   is required.',
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
            $debit = array(
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => $request->debit_general_ledger_account_id,
                'note' => $request->debit_description,
                'debit' => $request->debit,
                'credit' => 0,
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $voucher_detail[] = $debit;
            $this->insertDoubleEntry($debit);

            $credit = array(
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => $request->credit_general_ledger_account_id,
                'note' => $request->credit_description,
                'debit' => 0,
                'credit' => $request->credit,
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $voucher_detail[] = $credit;
            $this->insertDoubleEntry($credit);
            $journal_voucher = array(
                'created_at' => date('Y-m-d H:i:s'),
                'voucher_detail' => serialize($voucher_detail),
                'voucher_number' => $request->voucher_number,
                'net_toal' => $request->debit,
                'note' => $request->debit_description,
                'voucher_date' => $request->voucher_date,
                'branch' => Auth::user()->branch,
            );
            $sale = DB::table('journal_voucher')->insert($journal_voucher);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->voucher_number,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($journal_voucher),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Journal Voucher',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Journal Voucher added successfully..', 'redirectUrl' => '/journalVoucher/list'], 200);
        }
    }

    public function edit($id)
    {
        $journalVoucher = DB::table('journal_voucher')->where('id', $id)->first();
        /**
         * Get Only Accounts thoes have type General Ledger
         * but not default accounts
         */
        $ledgerAccounts = DB::table('general_ledger_accounts')
            // ->where('default_account',0)
            ->where('status', 1)
            // ->where('account_type_id',4)
            ->where('branch', Auth::user()->branch)
            ->get();
        return view('journalVoucher.new', array('journalVoucher' => $journalVoucher, 'accounts' => $ledgerAccounts, 'invoice_number' => '0'));
    }


    public function update(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');

        $validator = Validator::make(
            $request->all(),
            [
                'voucher_number' => 'required',
                'voucher_date' => 'required',
                'debit_general_ledger_account_id' => 'required',
                'debit_description' => 'required',
                'debit' => 'required|numeric|min:0|not_in:0|same:credit',
                'credit_general_ledger_account_id' => 'required',
                'credit_description' => 'required',
                'credit' => 'required|numeric|min:0|not_in:0|same:debit',
            ],
            [
                'voucher_number.required' => 'The Invoice #  is required.',
                'voucher_date.required' => 'The Invoice Date  is required.',
                'debit_general_ledger_account_id' => 'The Debit Account   is required.',
                'debit_description' => 'The Debit Description   is required.',
                'debit' => 'The Debit Amount should be equal Credit and is required.',
                'credit_general_ledger_account_id' => 'The Credit Account   is required.',
                'credit_description' => 'The Credit description   is required.',
                'credit' => 'The Credit Amount should be equal to debit  and   is required.',
            ]
        );
        if ($validator->fails()) {
            //$response['message'] = $validator->messages();
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {
            $this->deleteDoubleEntry($request->voucher_number);
            $voucher_detail = array();
            $debit = array(
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => $request->debit_general_ledger_account_id,
                'note' => $request->debit_description,
                'debit' => $request->debit,
                'credit' => 0,
                'branch' => Auth::user()->branch,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            $voucher_detail[] = $debit;
            $this->insertDoubleEntry($debit);

            $credit = array(
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => $request->credit_general_ledger_account_id,
                'note' => $request->credit_description,
                'debit' => 0,
                'credit' => $request->credit,
                'branch' => Auth::user()->branch,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            $voucher_detail[] = $credit;
            $this->insertDoubleEntry($credit);

            $journal_voucher = array(
                'updated_at' => date('Y-m-d H:i:s'),
                'voucher_detail' => serialize($voucher_detail),
                'voucher_number' => $request->voucher_number,
                'net_toal' => $request->debit,
                'note' => $request->debit_description,
                'voucher_date' => $request->voucher_date,
                'branch' => Auth::user()->branch,
            );
            $sale = DB::table('journal_voucher')->where('id', $request->id)->update($journal_voucher);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->voucher_number,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($journal_voucher),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Journal Voucher',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Journal Voucher updated successfully..', 'redirectUrl' => '/journalVoucher/list'], 200);
        }
    }

    public function delete($id)
    {
        $gReceipt = DB::table('journal_voucher')->where('id', $id)->first();
        $this->deleteDoubleEntry($gReceipt->voucher_number);
        DB::table('journal_voucher')->where('id', $id)->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $gReceipt->voucher_number,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($gReceipt),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Journal Voucher',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        return response()->json(['success' => true, 'message' => 'Journal Voucher deleted successfully..', 'redirectUrl' => '/journalVoucher/list'], 200);
    }


    // Record Pdf
    public function recordPdf($id)
    {
        $journalVoucher = DB::table('journal_voucher')->where('id', $id)->first();
        /**
         * Get Only Accounts thoes have type General Ledger
         * but not default accounts
         */
        $ledgerAccounts = DB::table('general_ledger_accounts')
            //->where('default_account',0)
            ->where('status', 1)
            // ->where('account_type_id',4)
            ->where('branch', Auth::user()->branch)
            ->get();
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;
        $data = array('journalVoucher' => $journalVoucher, 'accounts' => $ledgerAccounts, 'invoice_number' => '0', 'companyinfo' => $companyinfo);
        $pdf = PDF::loadView('journalVoucher.recordPdf', $data);
        return $pdf->stream('recordPdf.pdf');
    }

    public function jvoucherPagePdf($from_date, $to_date, $invoice_number)
    {
        $query = DB::table('journal_voucher')
            ->where('branch', Auth::user()->branch);
        if ($from_date != 'none' && $to_date != 'none') {
            $query->whereBetween('journal_voucher.voucher_date', [$from_date, $to_date]);
        }
        if ($invoice_number != 'none') {
            $query->where('journal_voucher.voucher_number', 'like', "%$invoice_number%");
        }
        $list = $query->orderByDesc('id')->get();


        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;
        $data = array('companyinfo' => $companyinfo, 'lists' => $list);
        $pdf = PDF::loadView('journalVoucher.pagePdf', $data);
        return $pdf->stream('pagePdf.pdf');
    }

    public function searchJVoucher(Request $request)
    {
        $Queries = array();
        if (empty($request->from_date) && empty($request->to_date) && empty($request->invoice_number)) {
            return redirect('journalVoucher/list');
        }

        $query = DB::table('journal_voucher');
        if (isset($request->invoice_number) && !empty($request->invoice_number)) {
            $Queries['invoice_number'] = $request->invoice_number;
            $query->where('journal_voucher.voucher_number', 'like', "%$request->invoice_number%");
        }
        if (isset($request->from_date) && isset($request->to_date)) {
            $Queries['from_date'] = $request->from_date;
            $Queries['to_date'] = $request->to_date;
            $query->whereBetween('journal_voucher.voucher_date', [$request->from_date, $request->to_date]);
        }

        $result = $query->orderByDesc('id')->paginate(1);
        $result->appends($Queries);
        return view('journalVoucher.list', array('lists' => $result, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'invoice_number' => $request->invoice_number));
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
