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
use Image;

class UserController extends Controller
{
    public function list()
    {
        // ->where('branch', Auth::user()->branch)
        $list = DB::table('users')->where('email', '!=', 'info@quadacts.com')->orderByDesc('id')->paginate(20);
        return view('users.list', array('users' => $list));
    }
    public function new()
    {
        $menus = DB::table('menus')->get();

        return view('users.new', array('menus' => $menus));
    }

    public function store(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|min:3|max:20',
                'email' => 'required|email|unique:users',
                'phone' => ['required', 'numeric'],
                'branch' => ['required', 'integer'],
                // 'userrights' => 'required',
                'password' => ['required', 'string', 'min:6'],
                'address' => 'required',
            ],
            [
                'name.required' => 'The full name field is required.',
                'email.unique' => 'The email has already been taken.Please try another one.',
                'address.required' => 'The address field is required.',
                'phone.required' => 'The phone field is required.',
            ]
        );

        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response, 422);
        } else {
            $file_path = Config::get('constants.DEFAULT_AVATAR');
            if ($files = $request->file('avatar')) {
                $destinationPath = public_path('/users_avatar/'); // upload path
                $profileImage = date('YmdHis') . "." . $files->getClientOriginalExtension();
                $img = Image::make($files->path());
                $img->resize(64, 64, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $profileImage);
                // $files->move($destinationPath, $profileImage);
                $file_path = '/users_avatar/' . $profileImage;
            }
            $userrights = array();
            if (!empty($request->userrights)) {
                foreach ($request->userrights as $userright) {
                    $userrights[] = unserialize($userright);
                }
            }
            $user = array(
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'branch' => $request->branch,
                'address' => $request->address,
                'avatar' => $file_path,
                'userrights' => serialize($userrights),
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 1,
                'is_admin' => 0,
            );
            $userId = DB::table('users')->insertGetId($user);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $userId,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($user),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'User Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'user added successfully..', 'redirectUrl' => '/user/list'], 200);
        }
    }
    public function edit($id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        $user->userrights = unserialize($user->userrights);


        $menuids = [];
        if ($user->userrights !== false) {
            $menuids = array_column($user->userrights, 'id');
        };
        $menus = DB::table('menus')->get();
        foreach ($menus as $menu) {
            $menu->active = 0;
            if (in_array($menu->id, $menuids, true)) {
                $menu->active = 1;
            }
        }
        return view('users.new', array('user' => $user, 'menus' => $menus));
    }
    public function update(Request $request)
    {
        Log::debug("hello from updatre");
        Log::debug($request->all());

        $userinfo = DB::table('users')->where('id', $request->id)->first();
        $emailexist = DB::table('users')->where('email', $request->email)->where('id', '!=', $request->id)->first();
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');
        if (!empty($emailexist)) {
            return response()->json(['success' => false, 'message' => 'The email has already been taken.Please try another one.', 'redirectUrl' => ''], 200);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|min:3|max:20',
                'phone' => ['required', 'numeric'],
                'branch' => ['required', 'integer'],
                // 'userrights' => 'required',
                'address' => 'required',
            ],
            [
                'name.required' => 'The full name field is required.',
                'address.required' => 'The address field is required.',
                'phone.required' => 'The phone field is required.',
            ]
        );

        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response, 422);
        } else {
            $file_path = Config::get('constants.DEFAULT_AVATAR');
            $userrights = array();
            if (!empty($request->userrights)) {
                foreach ($request->userrights as $userright) {
                    $userrights[] = unserialize($userright);
                }
            }
            $user = array(
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'branch' => $request->branch,
                'address' => $request->address,
                'userrights' => serialize($userrights),
            );
            if ($files = $request->file('avatar')) {
                $destinationPath = public_path('/users_avatar/'); // upload path
                $profileImage = date('YmdHis') . "." . $files->getClientOriginalExtension();
                $img = Image::make($files->path());
                $img->resize(64, 64, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $profileImage);
                // $files->move($destinationPath, $profileImage);
                $file_path = '/users_avatar/' . $profileImage;
                if ($userinfo->avatar != Config::get('constants.DEFAULT_AVATAR')) {
                    $this->removeImage($userinfo->avatar);
                }
                $user['avatar'] = $file_path;
            }

            if (!empty($request->password)) {
                $user['password'] = Hash::make($request->password);
            }
            if ($request->status == '') {
                $user['status'] = 0;
            } else {
                $user['status'] = 1;
            }
            $user['updated_at'] = date('Y-m-d H:i:s');
            $user = DB::table('users')->where('id', $request->id)->update($user);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->id,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($user),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'User Invoice',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'user update successfully..', 'redirectUrl' => '/user/list'], 200);
        }
    }
    public function delete($id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $user->id,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($user),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'User Invoice',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        $user = DB::table('users')->where('id', $id)->delete();
        return redirect('user/list');
    }

    public function search(Request $request)
    {
        $Queries = array();
        if (isset($request->username)) {
            $Queries['username'] = $request->username;
        }
        $list = DB::table('users')
            ->where('name', 'like', "%$request->username%")
            ->where('email', '!=', 'info@quadacts.com')->where('branch', Auth::user()->branch)
            ->orderByDesc('id')
            ->paginate(20);
        $list->appends($Queries);
        return view('users.list', array('users' => $list, 'searchQuery' => $request->username));
    }


    public function removeImage($path)
    {
        if (File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }

    public function menuList()
    {
        $menulist = DB::table('menus')->get();
        return view('users/menuList', array('menus' => $menulist));
    }
    public function newMenu()
    {
        $menus = DB::table('menus')->get();
        return view('users.newMenu', array('menus' => $menus));
    }
    public function active()
    {
        $list = DB::table('users')->where('status', 1)->get();
        return view('users.list', array('users' => $list));
    }
    public function inactive()
    {
        $list = DB::table('users')->where('status', 0)->get();
        return view('users.list', array('users' => $list));
    }

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
}
