<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    //
    public function menuList(){
        $menulist=DB::table('menus')->orderByDesc('id')->paginate(20);
         return view('menu/menuList', array('menus' => $menulist));
     }
     public function newMenu(){
         
         return view('menu.newMenu');
     }
     public function storeMenu(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $validator = Validator::make($request->all(),[
            'title' => 'required|min:3|max:20',
            'route' => 'required',
            'module' => 'required',
        ],
            [
                'title.required' => 'The Title of Menue field is required.',
                'route.required' => 'The Route field is required.',
                'module.required' => 'The Module field is required.',
            ]);

        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response,422);
        } else {

            $menu = array(
                'title' => $request->title,
                'route' => $request->route,
                'module' => $request->module,
                'created_at'=>date('Y-m-d H:i:s'),
                'status'=>1
            );
            
            $menuId=DB::table('menus')->insertGetId($menu);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $menuId,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($menu),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Menu Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Menue added successfully..', 'redirectUrl' => '/menu/menuList'],200);
        }
    }
    public function editMenu($id){
        $menus=DB::table('menus')->where('id',$id)->first();
        // return $menus;
        // echo $menus->title;
        // exit;
        return view('menu.newmenu', array('menus' => $menus));
    }
    public function updateMenu(Request $request){
        $userinfo=DB::table('menus')->where('id',$request->id)->first();
        $validator = Validator::make($request->all(),[
            'title' => 'required|min:3|max:20',
            'route' => 'required',
            'module' => 'required',
        ],
            [
                'title.required' => 'The Title of Menue field is required.',
                'route.required' => 'The Route field is required.',
                'module.required' => 'The Module field is required.',
            ]);

        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response,422);
        } else {
            $menu = array(
                'title' => $request->title,
                'route' => $request->route,
                'module' => $request->module,
                // 'created_at'=>date('Y-m-d H:i:s'),
                // 'status'=>1
            );
            if($request->status=='')
            {
            $menu['status']=0;
            }
            else
            {
            $menu['status']=1;
            }
            $menu['updated_at']=date('Y-m-d H:i:s');
            $menu=DB::table('menus')->where('id',$request->id)->update($menu);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->id,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($menu),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Menu Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Menue update successfully..', 'redirectUrl' => '/menu/menuList'],200);
        }
    }
    public function deleteMenu($id){
        $menu = array(
            'status' => 0,
            
        );
        $menu['updated_at']=date('Y-m-d H:i:s');
            $menu=DB::table('menus')->where('id',$id)->update($menu);
            $menudata=DB::table('menus')->where('id',$id)->first();
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $id,
                'transaction_action' => 'Deleted',
                'transaction_detail' => serialize($menudata),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Menu Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return redirect('menu/menuList');
            // return response()->json(['success' => true, 'message' => 'Menue update successfully..', 'redirectUrl' => '/menu/menuList'],200);

    }
    public function activeMenu()
    {
        $menulist=DB::table('menus')->where('status',1)->get();
         return view('menu/menuList', array('menus' => $menulist));

    }
    public function inactiveMenu()
    {
        $menulist=DB::table('menus')->where('status',0)->get();
         return view('menu/menuList', array('menus' => $menulist));

    }
    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
}
