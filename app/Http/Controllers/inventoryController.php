<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PDF;

class inventoryController extends Controller
{
    //
    public function categoryList()
    {
        $categorylist = DB::table('category')->orderByDesc('id')->paginate(20);
        return view('inventory.categorylist', array('category' => $categorylist));
    }
    public function newCategory()
    {

        return view('inventory.addcategory');
    }
    public function storeCategory(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|min:3|max:500',

            ],
            [
                'name.required' => 'The Title of category field is required.',

            ]
        );

        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response, 422);
        } else {

            $category = array(
                'name' => $request->name,
                'branch' => $request->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );

            $categoryID = DB::table('category')->insertGetId($category);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $categoryID,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($category),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Category',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Category added successfully..', 'redirectUrl' => '/category/categoryList'], 200);
        }
    }
    public function editCategory($id)
    {
        $category = DB::table('category')->where('id', $id)->first();
        // return $menus;
        // echo $menus->title;
        // exit;
        return view('inventory.addcategory', array('category' => $category));
    }
    public function updateCategory(Request $request)
    {
        $categoryinfo = DB::table('category')->where('id', $request->id)->first();
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|min:3|max:500',
            ],
            [
                'name.required' => 'The Title of category field is required.',
            ]
        );

        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response, 422);
        } else {
            $category = array(
                'name' => $request->name,
                'branch' => $request->branch

            );

            $category['updated_at'] = date('Y-m-d H:i:s');
            $category = DB::table('category')->where('id', $request->id)->update($category);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->id,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($category),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Category',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'category update successfully..', 'redirectUrl' => '/category/categoryList'], 200);
        }
    }
    public function deleteCategory($id)
    {
        $categoryData = DB::table('category')->where('id', $id)->first();
        $menu = DB::table('category')->where('id', $id)->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $id,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($categoryData),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Category',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);

        //    return redirect(menuList());
        return redirect('category/categoryList');
        // return response()->json(['success' => true, 'message' => 'Menue update successfully..', 'redirectUrl' => '/menu/menuList'],200);

    }

    // items crud(methods)
    public function itemData(Request $request)
    {

        $data = array();
        if (isset($request->code)) {
            $data = DB::table('items')->where('code', $request->code)->first();
        } else {
            $data = DB::table('items')->where('id', $request->id)->first();
        }
        return response()->json(['success' => true, 'data' => $data]);
    }
    public function itemList()
    {
        $query = DB::table('items');
        if (request()->input('query')) {
            $query->where('items.name', 'like', '%' . request()->input('query') . '%');
        }
        $itemlist = $query->join('category', 'items.category', '=', 'category.id')
            ->select('items.*', 'category.name as category_name')->where('items.branch', Auth::user()->branch)
            ->orderByDesc('items.id')
            ->paginate(20);
        foreach ($itemlist as $item) {
            $stockPlus = DB::table('general_inventory_transactions')->where('item_id', $item->id)->where('transaction_type', '+')->sum('item_qty');
            $stockMinus = DB::table('general_inventory_transactions')->where('item_id', $item->id)->where('transaction_type', '-')->sum('item_qty');
            $total_stock = $stockPlus - $stockMinus;
            $item->stock = $total_stock;
        }
        return view('inventory.itemList', array('items' => $itemlist));
    }
    public function itemPagePdf()
    {
        // $query = DB::table('items');
        // if (request()->input('query')) {
        //     $query->where('items.name', 'like', '%' . request()->input('query') . '%');
        // }
        $list =  DB::table('items')->join('category', 'items.category', '=', 'category.id')
            ->select('items.*', 'category.name as category_name')->where('items.branch', Auth::user()->branch)
            ->orderByDesc('items.id')
            ->get();
        foreach ($list as $item) {
            $stockPlus = DB::table('general_inventory_transactions')->where('item_id', $item->id)->where('transaction_type', '+')->sum('item_qty');
            $stockMinus = DB::table('general_inventory_transactions')->where('item_id', $item->id)->where('transaction_type', '-')->sum('item_qty');
            $total_stock = $stockPlus - $stockMinus;
            $item->stock = $total_stock;
        }
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;
        $data = array(
            'item_list' => $list,
            'companyinfo' => $companyinfo,
        );

        $pdf = PDF::loadView('inventory.itemPagePdf', $data);
        return $pdf->stream('itemPagePdf.pdf');
    }
    public function newItem()
    {
        $categorylist = DB::table('category')->where('branch', Auth::user()->branch)->get();
        $item_number = DB::table('items')->max('id') + 1;
        // $item_number = DB::table('items')->max('id') + 1;
        $items = DB::table('items')->where('category', 5)->where('item_type', 0)->get();
        return view('inventory.addItem', array('category' => $categorylist, 'item_number' => $item_number, 'itemss' => $items));
    }
    public function storeItem(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $validator = Validator::make(
            $request->all(),
            [
                'code' => 'required',
                'name' => 'required|min:3|max:500',
                'purchase_price' => ['required', 'numeric'],
                'sele_price' => ['required', 'numeric'],
                'item_type' => ['required', 'numeric'],

            ],
            [
                'code.required' => 'The code field is required.',
                'name.required' => 'The item name field is required.',
                'purchase_price.required' => 'The purchase Price field is required.',
                'sele_price.required' => 'The sale price field is required.',
                'item_type.required' => 'The item type field is required.',
            ]
        );

        if ($validator->fails()) {
            //$response['message'] = $validator->messages();
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {
            $file_path = Config::get('constants.ITEM_DEFAULT_PIC');
            if ($files = $request->file('pic')) {
                $destinationPath = public_path('/item_pic/'); // upload path
                $profileImage = date('YmdHis') . "." . $files->getClientOriginalExtension();
                $files->move($destinationPath, $profileImage);
                $file_path = '/item_pic/' . $profileImage;
            }

            $item_detail = array();
            if (isset($request->item_qty)) {
                $item_id = $request->item_id;
                $item_qtys = $request->item_qty;
                $i = 0;
                foreach ($item_id as $item) {
                    $general_ledger_account_id = $item_id[$i];
                    $item_qty = $item_qtys[$i];
                    if ($item_qty > 0) {
                        $item_detail[] = array(
                            'item_id' => $general_ledger_account_id,
                            'item_qty' => $item_qty,
                        );
                    }
                    $i++;
                }
            }

            $item = array(
                'code' => $request->code,
                'name' => $request->name,
                'pic' => $file_path,
                'purchase_price' => $request->purchase_price,
                'sele_price' => $request->sele_price,
                'stock' => 0,
                'item_type' => $request->item_type,
                'linked_items' => serialize($item_detail),
                'category' => $request->category,
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $itemId = DB::table('items')->insertGetId($item);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $itemId,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($item),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Items',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'item added successfully..', 'redirectUrl' => '/item/itemList'], 200);
        }
    }
    public function editItem($id)
    {
        $item = DB::table('items')->where('id', $id)->first();
        $categorylist = DB::table('category')->get();
        $items = DB::table('items')->where('category', 5)->where('item_type', 0)->get();
        return view('inventory.addItem', array('item' => $item, 'category' => $categorylist, 'itemss' => $items));
    }
    public function updateItem(Request $request)
    {
        $compinfo = DB::table('companyinfo')->where('id', $request->id)->first();
        // $emailexist=DB::table('users')->where('email',$request->email)->where('id','!=',$request->id)->first();
        // $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        // if(!empty($emailexist)){
        //     return response()->json(['success' => false, 'message' => 'The email has already been taken.Please try another one.', 'redirectUrl' => ''],200);
        // }
        $validator = Validator::make(
            $request->all(),
            [
                'code' => 'required',
                'name' => 'required|min:3|max:500',
                'purchase_price' => ['required', 'numeric'],
                'sele_price' => ['required', 'numeric'],
                'item_type' => ['required', 'numeric'],
            ],
            [
                'code.required' => 'The code field is required.',
                'name.required' => 'The item name field is required.',
                'purchase_price.required' => 'The purchase Price field is required.',
                'sele_price.required' => 'The sale price field is required.',


            ]
        );

        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {
            $file_path = Config::get('constants.ITEM_DEFAULT_PIC');
            if ($files = $request->file('pic')) {
                $destinationPath = public_path('/item_pic/'); // upload path
                $profileImage = date('YmdHis') . "." . $files->getClientOriginalExtension();
                $files->move($destinationPath, $profileImage);
                $file_path = '/item_pic/' . $profileImage;
            }

            $item_detail = array();
            if (isset($request->item_qty)) {
                $item_id = $request->item_id;
                $item_qtys = $request->item_qty;
                $i = 0;
                foreach ($item_id as $item) {
                    $general_ledger_account_id = $item_id[$i];
                    $item_qty = $item_qtys[$i];
                    if ($item_qty > 0) {
                        $item_detail[] = array(
                            'item_id' => $general_ledger_account_id,
                            'item_qty' => $item_qty,
                        );
                    }
                    $i++;
                }
            }
            $item = array(
                'code' => $request->code,
                'name' => $request->name,
                'pic' => $file_path,
                'purchase_price' => $request->purchase_price,
                'sele_price' => $request->sele_price,
                'stock' => 0,
                'item_type' => $request->item_type,
                'linked_items' => serialize($item_detail),
                'category' => $request->category,
                'branch' => Auth::user()->branch,


            );

            $item['updated_at'] = date('Y-m-d H:i:s');
            $item = DB::table('items')->where('id', $request->id)->update($item);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->id,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($item),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Items',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Item update successfully..', 'redirectUrl' => '/item/itemList'], 200);
        }
    }
    public function deleteItem($id)
    {
        $itemData = DB::table('items')->where('id', $id)->first();
        $item = DB::table('items')->where('id', $id)->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $itemData->id,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($itemData),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Items',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        return redirect('item/itemList');
    }

    public function itemLedgerEntries($id)
    {
        $list = DB::table('general_inventory_transactions')->leftJoin('items', 'general_inventory_transactions.item_id', '=', 'items.id')->where('item_id', $id)->get();
        $allTransactions = array();
        $netQuantity = 0;
        foreach ($list as $singleItem) {
            if ($singleItem->transaction_type == '+') {
                $netQuantity += $singleItem->item_qty;
                $singleItem->netQty = $netQuantity;
                $allTransactions[] = $singleItem;
            }
            if ($singleItem->transaction_type == '-') {
                $netQuantity -= $singleItem->item_qty;
                $singleItem->netQty = $netQuantity;
                $allTransactions[] = $singleItem;
            }
        }

        return view('inventory.itemLedger', array('lists' => $list, 'net' => $netQuantity));
    }

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }

    public function cancelItem($id)
    {
        $item = DB::table('items')->where('id', $id)->first();
        if (empty($item)) {
            return response()->json(['success' => false, 'message' => "Item doesn't exits..", 'redirectUrl' => 'item/itemList'], 200);
        }
        $item_cancel = array(
            'cancel_status' => true,
        );
        DB::table('items')->where('id', $id)->update($item_cancel);
        return redirect('item/itemList');
    }

    // public function searchItems(Request $request)
    // {
    //     $Queries = array();

    //     if (empty($request->from_date) && empty($request->to_date) && empty($request->invoice_number) && empty($request->user_id)) {
    //         return redirect('dailyVisits.list');
    //     }
    //     $query = DB::table('daily_visits');

    //     if (!empty($request->from_date) && !empty($request->to_date)) {
    //         $Queries['from_date'] = $request->from_date;
    //         $Queries['to_date'] = $request->to_date;
    //         $query->whereBetween('daily_visits.invoice_date', [$request->from_date, $request->to_date]);
    //     }
    //     if (!empty($request->invoice_number)) {
    //         $Queries['invoice_number'] = $request->invoice_number;
    //         $query->where('daily_visits.invoice_number', 'like', "%$request->invoice_number%");
    //     }
    //     if (!empty($request->user_id)) {
    //         $Queries['user_id'] = $request->user_id;
    //         $query->where('daily_visits.user_id', '=', $request->user_id);
    //     }
    //     $list = $query->orderByDesc('daily_visits.id')->paginate(20);
    //     $list->appends($Queries);
    //     $users = DB::table('users')->where('status', 1)->get();

    //     return view('dailyVisits.list', array('daily_visits' => $list, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'invoice_number' => $request->invoice_number, 'user_id' => $request->user_id, 'users' => $users));
    // }
}
