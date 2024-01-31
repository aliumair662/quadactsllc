<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

class departmentController extends Controller
{
    //
    public function departmentList(){
        $departmentlist=DB::table('department')->orderByDesc('id')->paginate(20);
         return view('department.departmentlist', array('department' => $departmentlist));
     }
     public function newDepartment(){

         return view('department.newdepartment');
     }
     public function storeDepartment(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $validator = Validator::make($request->all(),[
            'name' => 'required|min:3|max:500',

        ],
            [
                'name.required' => 'The Title of category field is required.',

            ]);

        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response,422);
        } else {

            $department = array(
                'name' => $request->name,
                'branch' => $request->branch,
                'created_at'=>date('Y-m-d H:i:s'),
            );

            $departmentId=DB::table('department')->insertGetId($department);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $departmentId,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($department),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Department',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'department added successfully..', 'redirectUrl' => '/department/departmentList'],200);
        }
    }
    public function editDepartment($id){
        $department=DB::table('department')->where('id',$id)->first();
        // return $menus;
        // echo $menus->title;
        // exit;
        return view('department.newdepartment', array('department' => $department));
    }
    public function updateDepartment(Request $request){
        $departmentinfo=DB::table('category')->where('id',$request->id)->first();
        $validator = Validator::make($request->all(),[
            'name' => 'required|min:3|max:500',
        ],
            [
                'name.required' => 'The Title of category field is required.',
            ]);

        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response,422);
        } else {
            $department = array(
                'name' => $request->name,
                'branch' => $request->branch

            );

            $department['updated_at']=date('Y-m-d H:i:s');
            $department=DB::table('department')->where('id',$request->id)->update($department);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->id,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($department),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Department',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'department update successfully..', 'redirectUrl' => '/department/departmentList'],200);
        }
    }
    public function deleteDepartment($id){
            $departmentData=DB::table('department')->where('id',$id)->first();
            $department=DB::table('department')->where('id',$id)->delete();
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $departmentData->id,
                'transaction_action' => 'Deleted',
                'transaction_detail' => serialize($departmentData),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Department',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            //    return redirect(menuList());
            return redirect('department/departmentList');
            // return response()->json(['success' => true, 'message' => 'Menue update successfully..', 'redirectUrl' => '/menu/menuList'],200);

    }

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
}
