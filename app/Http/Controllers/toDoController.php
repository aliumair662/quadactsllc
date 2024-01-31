<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class toDoController extends Controller{
    public function list()
    {
        $list = DB::table('to_do')
        ->leftJoin('users','to_do.user_id','=','users.id')
        ->select('to_do.*','users.name')->where('to_do.branch',Auth::user()->branch)
        ->paginate(20);
        return view('todo.list',array('lists'=>$list,'queries'=>'some'));
    }
    public function new()
    {
        return view('todo.new');
    }
    public function store(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');

        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'priority' => 'required',
            'description' => 'required',
        ],
            [
                'title.required' => 'Title is required.',
                'priority.required' => 'Priority is required.',
                'description.required' => 'Description is required.',
            ]);
        if ($validator->fails()) {
            //$response['message'] = $validator->messages();
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ),422);
        } else {
            $todo = array(
                'title' => $request->title,
                'created_at'=>date('Y-m-d H:i:s'),
                'description' => $request->description,
                'priority' => $request->priority,
                'user_id' => Auth::user()->id,
                'branch' =>Auth::user()->branch,
            );
            $id=DB::table('to_do')->insertGetId($todo);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $id,
                'transaction_action' => 'Created',
                'transaction_detail' => serialize($todo),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'To Do',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'To Do added successfully..', 'redirectUrl' => '/todo/list'],200);
        }
    }


    public function edit($id)
    {
        $record = DB::table('to_do')
        ->where('id',$id)
        ->first();
        return view('todo.new',array('record'=>$record));
    }



    public function update(Request $request)
    {
        $response = array('success' => false, 'message' => '', 'redirectUrl' => '');

        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'priority' => 'required',
            'description' => 'required',
        ],
            [
                'title.required' => 'Title is required.',
                'priority.required' => 'Priority is required.',
                'description.required' => 'Description is required.',
            ]);
        if ($validator->fails()) {
            //$response['message'] = $validator->messages();
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()

            ),422);
        } else {
            $todo = array(
                'title' => $request->title,
                'created_at'=>date('Y-m-d H:i:s'),
                'description' => $request->description,
                'priority' => $request->priority,
                'user_id' => Auth::user()->id,
                'branch' =>Auth::user()->branch,
            );
            $sale=DB::table('to_do')->where('id',$request->id)->update($todo);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->id,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($todo),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'To Do',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'To Do updated successfully..', 'redirectUrl' => '/todo/list'],200);
        }
    }

    public function delete($id)
    {
        $recordD = DB::table('to_do')
        ->where('id',$id)
        ->first();
        $record = DB::table('to_do')
        ->where('id',$id)
        ->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $recordD->id,
            'transaction_action' => 'Deleted',
            'transaction_detail' => serialize($recordD),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'To Do',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        return response()->json(['success' => true, 'message' => 'To Do deleted successfully..', 'redirectUrl' => '/todo/list'], 200);
    }

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
}
?>
