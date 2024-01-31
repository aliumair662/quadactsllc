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
use Illuminate\Support\Arr;

class workinProcessController extends Controller
{
    public function list()
    {
        $lists = DB::table('working_process_inventory')->orderByDesc('id')->paginate(20);
        $total = DB::table('working_process_inventory')->orderByDesc('id')->paginate(20)->sum('net_total');
        return view('workinProcess.list', array('lists' => $lists, 'total' => $total));
    }

    public function new()
    {
        $items = DB::table('items')->where('branch', Auth()->user()->branch)->whereIn('category', [3])->get();
        $invoice_number = DB::table('working_process_inventory')->max('id') + 1;
        return view('workinProcess.new', array('items' => $items, 'invoice_number' => $invoice_number));
    }

    public function store(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        /*$sale = DB::table('sales')->where('invoice_number', $request->invoice_number)->first();
        if (!empty($sale)) {
            return response()->json(['success' => false, 'message' => 'Sale Invoice already exits..', 'redirectUrl' => '/sales/list'], 200);
        }*/


        $validator = Validator::make(
            $request->all(),
            [
                'invoice_number' => 'required',
                'invoice_date' => 'required',
                'net_total' => 'required|numeric|min:0|not_in:0',
            ],
            [
                'invoice_number.required' => 'The Voucher #  is required.',
                'invoice_date.required' => 'The Voucher Date  is required.',
                'net_total.required' => 'Net Total is required.',
            ]
        );
        if ($validator->fails()) {
            //$response['message'] = $validator->messages();
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {

            $items_detail = array();
            $item_ids = $request->item_id;
            $item_prices = $request->item_price;
            $item_qtys = $request->item_qty;
            $amounts = $request->amount;
            $i = 0;
            foreach ($item_ids as $item) {
                $itemid = $item_ids[$i];
                $price = $item_prices[$i];
                $qty = $item_qtys[$i];
                $amount = $amounts[$i];
                if ($amount > 0) {
                    $items_detail[] = array(
                        'item_id' => $itemid,
                        'item_price' => $price,
                        'item_qty' => $qty,
                        'amount' => $amount,
                    );
                }

                $i++;
            }

            /**
             * Insert Stock Entry for each time
             * 1.get category of item
             *2.get linked general ledger account id from category table
             * Work-in-process Inventory A/c Debit
             *Material Category Inventory   A/c Credit
             */
            foreach ($items_detail as $_detail) {
                $item = DB::table('items')->where('id', $_detail['item_id'])->first();
                $category = DB::table('category')->where('id', $item->category)->first();
                $company = DB::table('companyinfo')->first();
                if ($company->stock_calculation == 0) {

                    $debit = array(
                        'voucher_date' => $request->invoice_date,
                        'voucher_number' => $request->invoice_number,
                        'general_ledger_account_id' => Config::get('constants.WORK_IN_PROCESS_INVENTORY_ACCOUNT_GENERAL_LEDGER'),
                        'note' => $item->name . ' ' . $_detail['item_qty'] . ' @ ' . $_detail['item_price'],
                        'debit' => $_detail['amount'],
                        'credit' => 0,
                        'branch' => Auth::user()->branch,
                        'created_at' => date('Y-m-d H:i:s'),
                    );
                    $this->insertDoubleEntry($debit);
                    $credit = array(
                        'voucher_date' => $request->invoice_date,
                        'voucher_number' => $request->invoice_number,
                        'general_ledger_account_id' => $category->general_ledger_account_id,
                        'note' => $item->name . ' ' . $_detail['item_qty'] . ' @ ' . $_detail['item_price'],
                        'debit' => 0,
                        'credit' => $_detail['amount'],
                        'branch' => Auth::user()->branch,
                        'created_at' => date('Y-m-d H:i:s'),
                    );
                    $this->insertDoubleEntry($credit);
                }
                $stock  = array(
                    'voucher_date' => $request->invoice_date,
                    'voucher_number' => $request->invoice_number,
                    'transaction_type' => '-',
                    'general_ledger_account_id' => $category->general_ledger_account_id,
                    'item_qty' => $_detail['item_qty'],
                    'item_id' => $_detail['item_id'],
                    'branch' => Auth::user()->branch,
                    'created_at' => date('Y-m-d H:i:s'),
                );
                $this->stockManagementEntry($stock);
            }
            $add = array(
                'net_total' => $request->net_total,
                'created_at' => date('Y-m-d H:i:s'),
                'item_detail' => serialize($items_detail),
                'voucher_number' => $request->invoice_number,
                'voucher_date' => $request->invoice_date,
            );
            $sale = DB::table('working_process_inventory')->insert($add);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->invoice_number,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($add),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Work in Process',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Working Process added successfully..', 'redirectUrl' => '/workinprocess/list'], 200);
        }
    }

    public function edit($id)
    {
        $record = DB::table('working_process_inventory')
            ->where('id', $id)
            ->first();
        $items = DB::table('items')->get();
        return view('workinProcess.new', array('record' => $record, 'items' => $items));
    }

    public function update(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $sale = DB::table('working_process_inventory')->where('voucher_number', $request->invoice_number)->first();
        if (!empty($sale)) {
            return response()->json(['success' => false, 'message' => 'Voucher already exits..', 'redirectUrl' => '/workinprocess/list'], 200);
        }


        $validator = Validator::make(
            $request->all(),
            [
                'invoice_number' => 'required',
                'invoice_date' => 'required',
                'net_total' => 'required|numeric|min:0|not_in:0',
            ],
            [
                'invoice_number.required' => 'The Voucher #  is required.',
                'invoice_date.required' => 'The Voucher Date  is required.',
                'net_total.required' => 'Net Total is required.',
            ]
        );
        if ($validator->fails()) {
            //$response['message'] = $validator->messages();
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {

            $items_detail = array();
            $item_ids = $request->item_id;
            $item_prices = $request->item_price;
            $item_qtys = $request->item_qty;
            $amounts = $request->amount;
            $i = 0;
            foreach ($item_ids as $item) {
                $itemid = $item_ids[$i];
                $price = $item_prices[$i];
                $qty = $item_qtys[$i];
                $amount = $amounts[$i];
                if ($amount > 0) {
                    $items_detail[] = array(
                        'item_id' => $itemid,
                        'item_price' => $price,
                        'item_qty' => $qty,
                        'amount' => $amount,
                    );
                }

                $i++;
            }

            /**
             * delete all  and Insert new Stock Entry for each time
             * 1.get category of item
             *2.get linked general ledger account id from category table
             * Work-in-process Inventory A/c Debit
             *Material Category Inventory   A/c Credit
             */
            $this->deleteDoubleEntry($request->invoice_number);
            $this->stockManagementEntryDelete($request->invoice_number);
            foreach ($items_detail as $_detail) {
                $item = DB::table('items')->where('id', $_detail['item_id'])->first();
                $category = DB::table('category')->where('id', $item->category)->first();
                $company = DB::table('companyinfo')->first();
                if ($company->stock_calculation == 0) {
                    $debit = array(
                        'voucher_date' => $request->invoice_date,
                        'voucher_number' => $request->invoice_number,
                        'general_ledger_account_id' => Config::get('constants.WORK_IN_PROCESS_INVENTORY_ACCOUNT_GENERAL_LEDGER'),
                        'note' => $item->name . ' ' . $_detail['item_qty'] . ' @ ' . $_detail['item_price'],
                        'debit' => $_detail['amount'],
                        'credit' => 0,
                        'branch' => Auth::user()->branch,
                        'updated_at' => date('Y-m-d H:i:s'),
                    );
                    $this->insertDoubleEntry($debit);
                    $credit = array(
                        'voucher_date' => $request->invoice_date,
                        'voucher_number' => $request->invoice_number,
                        'general_ledger_account_id' => $category->general_ledger_account_id,
                        'note' => $item->name . ' ' . $_detail['item_qty'] . ' @ ' . $_detail['item_price'],
                        'debit' => 0,
                        'credit' => $_detail['amount'],
                        'branch' => Auth::user()->branch,
                        'updated_at' => date('Y-m-d H:i:s'),
                    );
                    $this->insertDoubleEntry($credit);
                }
                $stock  = array(
                    'voucher_date' => $request->invoice_date,
                    'voucher_number' => $request->invoice_number,
                    'transaction_type' => '-',
                    'general_ledger_account_id' => $category->general_ledger_account_id,
                    'item_qty' => $_detail['item_qty'],
                    'item_id' => $_detail['item_id'],
                    'branch' => Auth::user()->branch,
                    'created_at' => date('Y-m-d H:i:s'),
                );
                $this->stockManagementEntry($stock);
            }

            $update = array(
                'net_total' => $request->net_total,
                'created_at' => date('Y-m-d H:i:s'),
                'item_detail' => serialize($items_detail),
                'voucher_number' => $request->invoice_number,
                'voucher_date' => $request->invoice_date,
            );
            $sale = DB::table('working_process_inventory')->where('id', $request->id)->update($update);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->invoice_number,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($update),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Work in Process',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Working Process updated successfully..', 'redirectUrl' => '/workinprocess/list'], 200);
        }
    }


    public function delete($id)
    {
        $process = DB::table('working_process_inventory')->where('id', $id)->first();
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $this->deleteDoubleEntry($process->voucher_number);
        DB::table('working_process_inventory')->where('voucher_number', $process->voucher_number)->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $process->invoice_number,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($process),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Work in Process',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        $this->stockManagementEntryDelete($process->voucher_number);
        return response()->json(['success' => true, 'message' => 'Working Process deleted successfully..', 'redirectUrl' => '/workinprocess/list'], 200);
    }


    public function search(Request $request)
    {
        if (isset($request->from_date) || isset($request->to_date) || isset($request->invoice_number)) {

            $query = DB::table('working_process_inventory');
            if (isset($request->invoice_number) && !empty($request->invoice_number)) {
                $query->where('working_process_inventory.voucher_number', 'like', "%$request->invoice_number%");
            } else {
                if (isset($request->from_date) && isset($request->to_date)) {
                    $query->whereBetween('working_process_inventory.voucher_date', [$request->from_date, $request->to_date]);
                }
            }
            $result = $query->orderByDesc('working_process_inventory.id')->paginate(20);
            $total =  $query->orderByDesc('working_process_inventory.id')->paginate(20)->sum('net_total');
            return view('workinProcess.list', array('lists' => $result, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'invoice_number' => $request->invoice_number));
        } else {
            $list = DB::table('working_process_inventory')
                ->orderByDesc('working_process_inventory.id')
                ->paginate(20);
            $total = DB::table('working_process_inventory')->orderByDesc('id')->paginate(20)->sum('net_total');
            return view('workinProcess.list', array('lists' => $list, 'total' => $total));
        }
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

    public function stockManagementEntry($data)
    {
        try {
            DB::table('general_inventory_transactions')->insertGetId($data);
        } catch (\Exception $e) {
            DB::table('general_inventory_transactions')->where('voucher_number', $data->voucher_number)->delete();
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'redirectUrl' => '/sales/list'], 200);
        }
    }

    public function stockManagementEntryDelete($voucher_number)
    {
        try {
            DB::table('general_inventory_transactions')->where('voucher_number', $voucher_number)->delete();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'redirectUrl' => '/sales/list'], 200);
        }
    }
    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
}
