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
use PDF;
class productionController extends Controller
{



    public function productionList(){
        $productionlist = DB::table('production')
            ->join('items', 'production.itemid', '=', 'items.id')->where('production.branch',Auth::user()->branch)
            ->select('production.*', 'items.name')
            ->orderByDesc('production.id')
            ->paginate(20);

         return view('production.productionlist', array('productionlist' => $productionlist));
     }
     public function new(){
        $items=DB::table('items')->where('branch',Auth::user()->branch)->get();
         $production_number=DB::table('production')->max('id') + 1;
         $employees=DB::table('employee')->where('production_method',1)->where('employee_type',0)->where('branch',Auth::user()->branch)->get();
         $lastproduction=DB::table('production')->orderBy('id','desc')->first();
         $lastproduction_date=date('Y-m-d');
         if(!empty($lastproduction)){
             $lastproduction_date=$lastproduction->production_date;
            }

        return view('production.addproduction', array('items'=> $items,'production_number'=>$production_number,'production_date'=>$lastproduction_date,'employees'=>$employees));
    }

    public function store(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $validator = Validator::make($request->all(),[
            'production_date' => 'required',
            'itemid' => 'required',
            'production_qty' => 'required|numeric|min:0|not_in:0',
            'net_total' => 'required|numeric|min:0|not_in:0',
            "amount"    => "required|array|min:1",
        ],
            [
                'production_date.required' => 'The production date field is required.',
                'itemid.required' => 'The production Item field is required.',
                'production_qty.required' => 'The production Qty field is required.',
                'net_total.required' => 'The Net Total field is required.',
                'amount.required' => 'The  Amount field is required.',
            ]);
        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {
            $production=array();
            $employee_ids=$request->employee_id;
            $rates=$request->rate;
            $additional_rates=$request->additional_rate;
            $prices=$request->item_price;
            $amounts=$request->amount;
            $item_qtys=$request->item_qty;
            $employees_detail='';
            $i=0;
            foreach($employee_ids as $employeeid){
                $rate=$rates[$i];
                $additional_rate=$additional_rates[$i];
                $item_price=$prices[$i];
                $amount=$amounts[$i];
                $item_qty=$item_qtys[$i];
                $employee_id=$employee_ids[$i];
                $employees_detail.='~'.$employee_ids[$i];
                if($amount >0){
                    $production[]=array(
                        'itemid'=>$request->itemid,
                        'rate'=>$rate,
                        'item_price'=>$item_price,
                        'additional_rate'=>$additional_rate,
                        'item_qty'=>$item_qty,
                        'amount'=>$amount,
                        'employee_id'=>$employee_id,
                    );
                }

                $i++;
            }
            /**
             * check if auto production post is enable then post else not
             * Insert Double entry
             *Salary Expense A/c Debit
             *Employee A/c  Credit
             */
            $companyinfo = DB::table('companyinfo')->first();
            if($companyinfo->auto_post_production == 1){
                if(!empty($production)){
                    foreach ($production as $product){
                        $debit = array(
                            'voucher_date' => $request->production_date,
                            'voucher_number' => $request->production_number,
                            'general_ledger_account_id' => Config::get('constants.OVERHEAD_COST_POOL_INVENTORY_EXPENSE_ACCOUNT_GENERAL_LEDGER'),
                            'note' => $request->note,
                            'debit' => $product['amount'],
                            'credit' => 0,
                            'branch' => Auth::user()->branch,
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                        $this->insertDoubleEntry($debit);
                        $employee = DB::table('employee')->where('id', $product['employee_id'])->first();
                        $credit = array(
                            'voucher_date' => $request->production_date,
                            'voucher_number' => $request->production_number,
                            'general_ledger_account_id' => $employee->general_ledger_account_id,
                            'note' => $request->note,
                            'debit' =>0 ,
                            'credit' => $product['amount'],
                            'branch' => Auth::user()->branch,
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                        $this->insertDoubleEntry($credit);
                    }


                }
            }

            /**
             * Add Stock Amount For Profile Loss
             */
            $company = DB::table('companyinfo')->first();
            if(!empty($production)){
                foreach ($production as $product){
                    $item = DB::table('items')->where('id', $product['itemid'])->first();
                    $category = DB::table('category')->where('id', $item->category)->first();
                    if ($company->stock_calculation == 0) {
                        $item = DB::table('items')->where('id', $product['itemid'])->first();
                        $debit = array(
                            'voucher_date' => $request->production_date,
                            'voucher_number' => $request->production_number,
                            'general_ledger_account_id' => Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'),
                            'note' => $item->name . ' ' . $product['item_qty'] . ' @ ' . $item->sele_price,
                            'debit' => $product['item_qty']*$item->sele_price,
                            'credit' => 0,
                            'branch' => Auth::user()->branch,
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                        $this->insertDoubleEntry($debit);
                        $credit = array(
                            'voucher_date' => $request->production_date,
                            'voucher_number' => $request->production_number,
                            'general_ledger_account_id' => Config::get('constants.WORK_IN_PROCESS_INVENTORY_ACCOUNT_GENERAL_LEDGER'),
                            //'note' =>$item->name.' '.$_detail['item_qty'].' @ '.$_detail['item_price'],
                            'note' => $item->name . ' ' . $product['item_qty'] . ' @ ' . $item->purchase_price,
                            'debit' => 0,
                            //'credit' => $_detail['amount'],
                            'credit' => $product['item_qty'] * $item->purchase_price,
                            'branch' => Auth::user()->branch,
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                        $this->insertDoubleEntry($credit);
                    }
                    $stock  = array(
                        'voucher_date' => $request->production_date,
                        'voucher_number' => $request->production_number,
                        'transaction_type' => '+',
                        'general_ledger_account_id' => $category->general_ledger_account_id,
                        'item_qty' => $product['item_qty'],
                        'item_id' => $product['itemid'],
                        'branch' => Auth::user()->branch,
                        'created_at' => date('Y-m-d H:i:s'),
                    );
                    $this->stockManagementEntry($stock);
                }
            }



            $production = array(
                'production_number' => $request->production_number,
                'production_date' => $request->production_date,
                'itemid' => $request->itemid,
                'production_qty' => $request->production_qty,
                'production_detail' => serialize($production),
                'employees_detail' => $employees_detail,
                'net_total' => $request->net_total,
                'note' => $request->note,
                'employee_id' => $request->single_employee_id,
                'branch' => Auth()->user()->branch,
                'created_at'=>date('Y-m-d H:i:s'),
            );
            $id = DB::table('production')->insertGetId($production);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $id,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($production),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Production',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Production added successfully..', 'redirectUrl' => '/production/newproduction'],200);
        }
    }
    public function edit($id){
        $production=DB::table('production')->where('id',$id)->first();
        $items=DB::table('items')->where('branch',Auth::user()->branch)->get();
        $employees=DB::table('employee')->where('production_method',1)->where('employee_type',0)->where('branch',Auth::user()->branch)->get();
        $production_details=array();
        foreach (unserialize($production->production_detail) as $detail){

           $employee=DB::table('employee')->where('id',$detail['employee_id'])->first();
            $detail['employee_name']=$employee->name;
            $production_details[]=$detail;
        }
        return view('production.addproduction', array('production' => $production,'production_details' => $production_details,'items'=>$items,'production_date'=>$production->production_date,'employees'=>$employees));
    }
    public function update(Request $request){
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $validator = Validator::make($request->all(),[
            'production_date' => 'required',
            'itemid' => 'required',
            'production_qty' => 'required|numeric|min:0|not_in:0',
            'net_total' => 'required|numeric|min:0|not_in:0',
            "amount"    => "required|array|min:1",
        ],
            [
                'production_date.required' => 'The production date field is required.',
                'itemid.required' => 'The production Item field is required.',
                'production_qty.required' => 'The production Qty field is required.',
                'net_total.required' => 'The Net Total field is required.',
                'amount.required' => 'The  Amount field is required.',
            ]);
        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {
            $production=array();
            $employee_ids=$request->employee_id;
            $rates=$request->rate;
            $additional_rates=$request->additional_rate;
            $prices=$request->item_price;
            $amounts=$request->amount;
            $item_qtys=$request->item_qty;
            $employees_detail='';
            $i=0;
            foreach($employee_ids as $employeeid){
                $item_price=$prices[$i];
                $rate=$rates[$i];
                $additional_rate=$additional_rates[$i];
                $amount=$amounts[$i];
                $item_qty=$item_qtys[$i];
                $employee_id=$employee_ids[$i];
                $employees_detail.='~'.$employee_ids[$i];
                if($amount >0){
                    $production[]=array(
                        'itemid'=>$request->itemid,
                        'rate'=>$rate,
                        'item_price'=>$item_price,
                        'additional_rate'=>$additional_rate,
                        'item_qty'=>$item_qty,
                        'amount'=>$amount,
                        'employee_id'=>$employee_id,
                    );
                }

                $i++;
            }
            /**Delete First then add new **/
            $this->deleteDoubleEntry($request->production_number);
            /**check if auto production post is enable then post else not
             * Insert Double entry
             *Salary Expense A/c Debit
             *Employee A/c  Credit
             */
            $this->stockManagementEntryDelete($request->production_number);
            $companyinfo = DB::table('companyinfo')->first();
            if($companyinfo->auto_post_production == 1){
                if(!empty($production)){
                    foreach ($production as $product){

                        $debit = array(
                            'voucher_date' => $request->production_date,
                            'voucher_number' => $request->production_number,
                            'general_ledger_account_id' => Config::get('constants.OVERHEAD_COST_POOL_INVENTORY_EXPENSE_ACCOUNT_GENERAL_LEDGER'),
                            'note' => $request->note,
                            'debit' => $product['amount'],
                            'credit' => 0,
                            'branch' => Auth::user()->branch,
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                        $this->insertDoubleEntry($debit);
                        $employee = DB::table('employee')->where('id', $product['employee_id'])->first();
                        $credit = array(
                            'voucher_date' => $request->production_date,
                            'voucher_number' => $request->production_number,
                            'general_ledger_account_id' => $employee->general_ledger_account_id,
                            'note' => $request->note,
                            'debit' =>0 ,
                            'credit' => $product['amount'],
                            'branch' => Auth::user()->branch,
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                        $this->insertDoubleEntry($credit);
                    }
                }
            }
            /**
             * Add Stock Amount For Profile Loss
             */
            $company = DB::table('companyinfo')->first();
            if(!empty($production)){
                foreach ($production as $product){
                    $item = DB::table('items')->where('id', $product['itemid'])->first();
                    $category = DB::table('category')->where('id', $item->category)->first();
                    if ($company->stock_calculation == 0) {
                        $item = DB::table('items')->where('id', $product['itemid'])->first();
                        $debit = array(
                            'voucher_date' => $request->production_date,
                            'voucher_number' => $request->production_number,
                            'general_ledger_account_id' => Config::get('constants.FINISHED_GOODS_INVENTORY_ACCOUNT_GENERAL_LEDGER'),
                            'note' => $item->name . ' ' . $product['item_qty'] . ' @ ' . $item->sele_price,
                            'debit' => $product['item_qty']*$item->sele_price,
                            'credit' => 0,
                            'branch' => Auth::user()->branch,
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                        $this->insertDoubleEntry($debit);
                        $credit = array(
                            'voucher_date' => $request->production_date,
                            'voucher_number' => $request->production_number,
                            'general_ledger_account_id' => Config::get('constants.WORK_IN_PROCESS_INVENTORY_ACCOUNT_GENERAL_LEDGER'),
                            //'note' =>$item->name.' '.$_detail['item_qty'].' @ '.$_detail['item_price'],
                            'note' => $item->name . ' ' . $product['item_qty'] . ' @ ' . $item->purchase_price,
                            'debit' => 0,
                            //'credit' => $_detail['amount'],
                            'credit' => $product['item_qty'] * $item->purchase_price,
                            'branch' => Auth::user()->branch,
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                        $this->insertDoubleEntry($credit);
                    }
                    $stock  = array(
                        'voucher_date' => $request->production_date,
                        'voucher_number' => $request->production_number,
                        'transaction_type' => '+',
                        'general_ledger_account_id' => $category->general_ledger_account_id,
                        'item_qty' => $product['item_qty'],
                        'item_id' => $product['itemid'],
                        'branch' => Auth::user()->branch,
                        'created_at' => date('Y-m-d H:i:s'),
                    );
                    $this->stockManagementEntry($stock);
                }
            }


            $production = array(
                'production_number' => $request->production_number,
                'production_date' => $request->production_date,
                'itemid' => $request->itemid,
                'production_qty' => $request->production_qty,
                'production_detail' => serialize($production),
                'employees_detail' => $employees_detail,
                'net_total' => $request->net_total,
                'note' => $request->note,
                'employee_id' => $request->single_employee_id,
                'branch' => Auth()->user()->branch,
                'updated_at'=>date('Y-m-d H:i:s'),
            );
            DB::table('production')->where('id',$request->id)->update($production);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->id,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($production),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Production',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Production updated successfully..', 'redirectUrl' => '/production/productionList'],200);
        }
    }
    public function delete($id){
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $production=DB::table('production')->where('id',$id)->first();
        $this->deleteDoubleEntry($production->production_number);
        DB::table('production')->where('id',$id)->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $production->id,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($production),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Production',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        $this->stockManagementEntryDelete($production->production_number);
        return response()->json(['success' => true, 'message' => 'Production deleted successfully..', 'redirectUrl' => '/production/productionList'],200);
    }

    public function deletpostproduction($id){
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $postProduction=DB::table('post_production_manually')->where('id',$id)->first();
        $this->deleteDoubleEntry($postProduction->voucher_number);
        DB::table('post_production_manually')->where('id',$id)->delete();
        return response()->json(['success' => true, 'message' => 'Post Production deleted successfully..', 'redirectUrl' => '/production/productionReport'],200);
    }


    public function searchproductionReport(Request $request)
    {
        $voucher_number=DB::table('post_production_manually')->max('id') + 1;
        $query = DB::table('production');
        if(!empty($request->from_date) && !empty($request->to_date)){
            $query=$query->whereBetween('production_date',[$request->from_date,$request->to_date]);
        }
        if(!empty($request->employeeid)){
            $query=$query->where('employees_detail', 'like', '%~'.$request->employeeid.'%');
        }

        if(!empty($request->itemid)){
            $query=$query->where('itemid', '=', $request->itemid);
        }
        $productions=$query->where('production.branch',Auth::user()->branch)->get();

        $net_total=0;
        $net_qty=0;
        $allProduction=array();
        if(!empty($productions)){
            foreach ($productions as $production) {
                $production_details = unserialize($production->production_detail);
                foreach ($production_details as $_details) {
                    $item=DB::table('items')->where('id',$_details['itemid'])->first();
                    $_details['item_name'] = $item->name;
                    $employee=DB::table('employee')->where('id',$_details['employee_id'])->first();
                    $_details['employee_name'] = $employee->name;
                    $_details['production_number'] = $production->production_number;
                    $_details['production_date'] = $production->production_date;
                    /**
                     * if employee id filter apply then only include the data of specifc employee else all
                     */
                    if(!empty($request->employeeid)){
                        if($_details['employee_id'] == $request->employeeid){
                            $allProduction[] = $_details;
                            $net_total+=$_details['amount'];
                            $net_qty+=$_details['item_qty'];
                        }
                    }else{
                        $allProduction[] = $_details;
                        $net_total+=$_details['amount'];
                        $net_qty+=$_details['item_qty'];
                    }

                }
            }
        }


        $employees=DB::table('employee')->where('branch',Auth::user()->branch)->get();
        $employee_name='';
        /**
         * Employee Previous Production Posted list
         */
        $EmployeePostPorductionList=array();
        $EmployeeAdvanceReturnList=array();

        if(!empty($request->employeeid)){
            $employee=DB::table('employee')->where('branch',Auth::user()->branch)->where('id',$request->employeeid)->first();
            $employee_name=$employee->name;
            $EmployeePostPorductionList=$this->getEmployeeProductionPosted($request);
            /**
             * Employee Previous Payment  list
             */
            $EmployeePaymentList=array();
            $EmployeePaymentSum=array();
            $query = DB::table('general_ledger_transactions')->where('branch',Auth::user()->branch)->orderBy('id','DESC')->where('voucher_number', 'like', ''.Config::get('constants.EMPLOYEE_PAYMENT_PREFIX').'%');
            if(!empty($request->from_date) && !empty($request->to_date)){
                $query=$query->whereBetween('voucher_date',[$request->from_date,$request->to_date]);
            }
            if(!empty($request->employeeid)){
                $query=$query ->where('general_ledger_account_id',$employee->general_ledger_account_id);
            }
            $EmployeePaymentList=$query->get();
            $EmployeePaymentSum=$query->sum('debit');
            $EmployeeAdvanceReturnSum=DB::table('employee_advance_return')->where('branch',Auth::user()->branch)->where('employee_id',$request->employeeid)->sum('amount');
            $EmployeeAdvanceReturnList=$this->getEmployeeAdvanceReturn($request);
            $EmployeeAdvanceSum= DB::table('employee')->where('branch',Auth::user()->branch)->where('id',$request->employeeid)->sum('advance');
            /**
             * Employee Advance Amount Payment  list
             */
        }
        $items=DB::table('items')->where('branch',Auth::user()->branch)->get();

        return view('production/productionReportList',array('allProductionList'=>$allProduction,'net_total'=>$net_total,'net_qty'=>$net_qty,'employees'=>$employees,'items'=>$items,'employeeid'=>$request->employeeid,'itemid'=>$request->itemid,'from_date'=>$request->from_date,'to_date'=>$request->to_date,'employee_name'=>$employee_name,'EmployeePostPorductionList'=>$EmployeePostPorductionList,'EmployeePaymentList'=>$EmployeePaymentList,'EmployeePaymentSum'=>$EmployeePaymentSum,'EmployeeAdvanceReturnSum'=>$EmployeeAdvanceReturnSum,'EmployeeAdvanceSum'=>$EmployeeAdvanceSum,'EmployeeAdvanceReturnList'=>$EmployeeAdvanceReturnList,'EmployeeAdvanceReturnList'=>$EmployeeAdvanceReturnList,'voucher_number'=>$voucher_number));

    }
    public function getEmployeeProductionPosted($request){
        $employee=DB::table('employee')->where('id',$request->employeeid)->first();
        $query =DB::table('post_production_manually')->orderBy('id','DESC');
        if(!empty($request->from_date) && !empty($request->to_date)){
            $query=$query->whereBetween('voucher_date',[$request->from_date,$request->to_date]);
        }
        if(!empty($request->employeeid)){
            $query=$query ->where('employee_id',$request->employeeid);
        }
        $EmployeePostPorductionList=$query->get();
        return $EmployeePostPorductionList;

    }
    public function postProductionPdf($id){
       $postProduction=DB::table('post_production_manually')->where('id',$id)->first();
       $employee=DB::table('employee')->where('id',$postProduction->employee_id)->first();
        $postProduction->employee_name=$employee->name;
        $production_details=unserialize($postProduction->production_details);
        $itemWisePorduction=array();
        foreach ($production_details as $production){
            if(array_key_exists($production['itemid'],$itemWisePorduction)){
                $itemWisePorduction[$production['itemid']]['item_qty']= $itemWisePorduction[$production['itemid']]['item_qty'] + $production['item_qty'];
                $itemWisePorduction[$production['itemid']]['amount']= $itemWisePorduction[$production['itemid']]['item_qty'] + $production['amount'];
            }else{
                $itemWisePorduction[$production['itemid']]=$production ;
            }
        }
        $data =  array('postProduction' => $postProduction,'itemWisePorduction'=>$itemWisePorduction);
        $pdf = PDF::loadView('production.postProductionPdf', $data);
        // return $pdf->download('salePdf.pdf');
        return $pdf->stream('EmployeeProductionPosted.pdf');
    }
    public function getEmployeeAdvanceReturn($request){

        $employee=DB::table('employee')->where('id',$request->employeeid)->first();
        $query =DB::table('employee_advance_return')->leftJoin('employee', 'employee_advance_return.employee_id', '=', 'employee.id')->select('employee_advance_return.*','employee.name');
        if(!empty($request->from_date) && !empty($request->to_date)){
            $query=$query->whereBetween('employee_advance_return.voucher_date',[$request->from_date,$request->to_date]);
        }
        if(!empty($request->employeeid)){
            $query=$query ->where('employee_advance_return.employee_id',$request->employeeid);
        }
        $EmployeeAdvanceReturnList=$query->get();
        return $EmployeeAdvanceReturnList;

    }
    public function searchGroupItemproductionReport(Request $request)
    {

        $query = DB::table('production');
        if(!empty($request->from_date) && !empty($request->to_date)){
            $query=$query->whereBetween('production_date',[$request->from_date,$request->to_date]);
        }
        if(!empty($request->employeeid)){
            $query=$query->where('employees_detail', 'like', '%~'.$request->employeeid.'%');
        }
        /*if(!empty($request->itemid)){
            $query=$query->where('itemid', '=', $request->itemid);
        }*/
        $productions=$query->get();
        $net_total=0;
        $net_qty=0;
        $allProduction=array();
        $sameRateItems=array();
        $sameRateItemsQty=array();
        if(!empty($productions)){
            foreach ($productions as $production) {
                $production_details = unserialize($production->production_detail);
                foreach ($production_details as $_details) {
                    $item=DB::table('items')->where('id',$_details['itemid'])->first();
                    $_details['item_name'] = $item->name;
                    $employee=DB::table('employee')->where('id',$_details['employee_id'])->first();
                    $_details['employee_name'] = $employee->name;
                    $_details['production_number'] = $production->production_number;
                    /**
                     * filter the recerord for searching employee
                     * Group the qty of item which have the same rate
                     */
                    if($_details['employee_id'] == $request->employeeid){
                        if(array_key_exists($_details['item_price'],$sameRateItemsQty)){
                            $sameRateItemsQty[$_details['item_price']]=$sameRateItemsQty[$_details['item_price']] + $_details['item_qty'];
                            $sameRateItems[$_details['item_price']][]=$item->name;
                        }else{
                            $sameRateItemsQty[$_details['item_price']]=$_details['item_qty'] ;
                            $sameRateItems[$_details['item_price']][]=$item->name;
                            //$sameRateItems[$_details['item_price']][]=$item->name ;
                        }
                    }

                }
            }
        }
        $finaldata=array();
        if(!empty($sameRateItemsQty)){
            foreach ($sameRateItemsQty  as $key => $RateItemsQty){
                $finaldata[]=array(
                    'item_price'=>$key,
                    'item_qty'=>$RateItemsQty,
                    'items'=>array_unique($sameRateItems[$key]),
                    'amount'=>$key * $RateItemsQty,
                );
                $net_total+=$key * $RateItemsQty;
            }
        }
        $employees=DB::table('employee')->where('branch',Auth::user()->branch)->get();
        $items=DB::table('items')->where('branch',Auth::user()->branch)->get();


        $EmployeePostPorductionList=array();
        if(!empty($request->employeeid)){
            $employee=DB::table('employee')->where('id',$request->employeeid)->first();
            $employee_name=$employee->name;
            $query =DB::table('general_ledger_transactions')->orderBy('id','DESC')->where('voucher_number', 'like', ''.Config::get('constants.PRODUCTION_POST_INVOICE_PREFIX').'%');
            if(!empty($request->from_date) && !empty($request->to_date)){
                $query=$query->whereBetween('voucher_date',[$request->from_date,$request->to_date]);
            }
            if(!empty($request->employeeid)){
                $query=$query ->where('general_ledger_account_id',$employee->general_ledger_account_id);
            }
            $EmployeePostPorductionList=$query->limit(20)->get();
        }

        return view('production/groupItemproductionReport',array('finaldata'=>$finaldata,'net_total'=>$net_total,'employees'=>$employees,'items'=>$items,'employeeid'=>$request->employeeid,'from_date'=>$request->from_date,'to_date'=>$request->to_date,'EmployeePostPorductionList'=>$EmployeePostPorductionList,'employee_name'=>$employee_name));

    }
    // Production Report List
    public function productionReport()
    {
        $from_date=Carbon::now()->subMonth(1)->format('Y-m-d');
        $end_date=date('Y-m-d');
        $net_total=0;
        $net_qty=0;
        $allProduction = array();
        $productions=DB::table('production')->where('branch',Auth::user()->branch)->whereBetween('production_date',[$from_date,$end_date])->get();
        $employees=DB::table('employee')->where('branch',Auth::user()->branch)->get();
        $items=DB::table('items')->where('branch',Auth::user()->branch)->get();
        foreach ($productions as $production) {
            $production_details = unserialize($production->production_detail);
            foreach ($production_details as $_details) {
                $item=DB::table('items')->where('id',$_details['itemid'])->first();
                $_details['item_name'] =(!empty($item)) ? $item->name : '';
                $employee=DB::table('employee')->where('id',$_details['employee_id'])->first();
                $_details['employee_name'] = $employee->name;
                $_details['production_number'] = $production->production_number;
                $allProduction[] = $_details;
                $net_total+=$_details['amount'];
                $net_qty+=$_details['item_qty'];

            }
        }
        $allProduction=array();
        return view('production/productionReportList',array('allProductionList'=>$allProduction,'net_total'=>$net_total,'employees'=>$employees,'items'=>$items,'net_qty'=>$net_qty));
    }
    public function groupItemproductionReport()
    {
        $from_date=Carbon::now()->subMonth(1)->format('Y-m-d');
        $end_date=date('Y-m-d');
        $employees=DB::table('employee')->where('branch',Auth::user()->branch)->get();
        $items=DB::table('items')->where('branch',Auth::user()->branch)->get();
        return view('production/groupItemproductionReport',array('employees'=>$employees,'items'=>$items));
    }




    // Production Report List Search Results Handle
    public function search(Request $request)
    {

            if (isset($request->from_date) && isset($request->to_date)) {

                $query = DB::table('production');
                $query->join('items', 'production.itemid', '=', 'items.id');
                $query->select('production.*', 'items.name');
                if (isset($request->from_date) && isset($request->to_date)) {
                        $query->whereBetween('production.production_date', [$request->from_date, $request->to_date]);
                 }
                $result = $query->orderByDesc('production.id')->paginate(20);
                return view('production.productionlist', array('productionlist' => $result, 'from_date' => $request->from_date, 'to_date' => $request->to_date));
            } else {
                $productionlist = DB::table('production')
                ->join('items', 'production.itemid', '=', 'items.id')
                ->select('production.*', 'items.name')
                ->orderByDesc('production.id')
                ->paginate(20);
             return view('production.productionlist', array('productionlist' => $productionlist));
            }
    }

    function postEmployeeProductionManually(Request $request){
        $post_production_manually=DB::table('post_production_manually')
            ->where('branch',Auth::user()->branch)
            ->where('employee_id',$request->employeeid)
            //->whereBetween('voucher_date', [$request->from_date, $request->to_date])
            ->where('voucher_date','=',$request->production_date)
            ->first();

        if(!empty($post_production_manually)){
            return response()->json(array(
                'success' => false,
                'errors' => array('already'=>'Employee Production for This Date is already added..')

            ), 422);
            exit();

        }

        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $validator = Validator::make($request->all(),[
            'employeeid' => 'required',
            'net_total' => 'required|numeric|min:0|not_in:0',
        ],
            [
                'employeeid.required' => 'The Employee Name  field is required.',
                'net_total.required' => 'The Net Total field is required.',
            ]);
        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ), 422);
        } else {

            /**
             * Insert Double entry
             *Salary Expense A/c Debit
             *Employee A/c  Credit
             */

            $debit = array(
                'voucher_date' => $request->production_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => Config::get('constants.SALARY_EXPENSE_ACCOUNT_GENERAL_LEDGER'),
                'note' => $request->employee_name.' Production Posted ',
                'debit' => $request->net_total,
                'credit' => 0,
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($debit);
            $employee = DB::table('employee')->where('id', $request->employeeid)->first();
            $credit = array(
                'voucher_date' => $request->production_date,
                'voucher_number' => $request->voucher_number,
                'general_ledger_account_id' => $employee->general_ledger_account_id,
                'note' => $request->employee_name.' Production Posted ',
                'debit' =>0 ,
                'credit' => $request->net_total,//add actual production amount
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->insertDoubleEntry($credit);
            if(!empty($request->cash_paid) && $request->cash_paid > 0){
                /**
                 * Insert Double entry
                 *Employee A/c Debit
                 *Cash A/c  Credit
                 */
                $debit = array(
                    'voucher_date' =>$request->production_date,
                    'voucher_number' => $request->voucher_number,
                    'general_ledger_account_id' => $employee->general_ledger_account_id,
                    'note' =>$employee->name .' Cash Paid ',
                    'debit' => $request->cash_paid,
                    'credit' => 0,
                    'branch' => Auth::user()->branch,
                    'updated_at' => date('Y-m-d H:i:s'),
                );
                $this->insertDoubleEntry($debit);
                $credit = array(
                    'voucher_date' => date('Y-m-d'),
                    'voucher_number' => $request->voucher_number,
                    'general_ledger_account_id' => Config::get('constants.CASH_ACCOUNT_GENERAL_LEDGER'),
                    'note' => $employee->name .' Cash Paid ',
                    'debit' => 0,
                    'credit' => $request->cash_paid,
                    'branch' => Auth::user()->branch,
                    'updated_at' => date('Y-m-d H:i:s'),
                );
                $this->insertDoubleEntry($credit);
            }


            $post_production = array(
                'employee_id' => $request->employeeid,
                'voucher_number' => $request->voucher_number,
                'voucher_date' => $request->production_date,
                'amount_type' => $request->amount_type,
                'actual_production_amount' => $request->actual_production_amount,
                'gross_total' => $request->gross_total,
                'deduction_amount' => $request->deduction_amount,
                'total_Advance' => $request->total_Advance,
                'total_payment_received_period' => $request->total_payment_received_period,
                'additional_amount' => $request->additional_amount,
                'production_details' => $request->production_details,
                'net_total' => $request->net_total,
                'cash_paid' => $request->cash_paid,
                'branch' => Auth::user()->branch,
                'created_at' => date('Y-m-d H:i:s'),
            );
             DB::table('post_production_manually')->insertGetId($post_production);
            return response()->json(['success' => true, 'message' => 'Production Post successfully..', 'redirectUrl' => '/production/productionReport'],200);
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

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
    public function stockManagementEntryDelete($voucher_number)
    {
        try {
            DB::table('general_inventory_transactions')->where('voucher_number', $voucher_number)->delete();
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
}




