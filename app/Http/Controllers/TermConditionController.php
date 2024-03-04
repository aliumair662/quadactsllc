<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use PDF;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Image;

class TermConditionController extends Controller
{
    public function termConditionList()
    {
        // if (Auth::user()->is_admin) {
        $list = DB::table('term_condition')
            ->orderByDesc('id')
            ->paginate(20);
        // } else {
        //     $list = DB::table('term_condition')
        //         ->where('user_id', Auth::user()->id)
        //         ->orderByDesc('id')
        //         ->paginate(20);
        // }
        // $users = DB::table('users')->where('status', 1)->get();
        // $visit_status_data = config('constants.daily_visit_status');
        // $status = [];
        // foreach ($visit_status_data as $id => $name) {
        //     $statusObject = new \stdClass();
        //     $statusObject->id = $id;
        //     $statusObject->name = $name;
        //     $status[] = $statusObject;
        // }
        // foreach ($list as $record) {
        //     for ($i = 0; $i < count($visit_status_data); $i++) {
        //         $id = array_keys($visit_status_data)[$i];
        //         $name = $visit_status_data[$id];
        //         if ($id == $record->status_id) {
        //             $record->status_name = $name;
        //             if ($id == 1) $record->badge = 'badge-warning';
        //             elseif ($id == 2) $record->badge = 'badge-danger';
        //             elseif ($id == 3) $record->badge = 'badge-success';
        //         } else {
        //             if ($id == 1) {
        //                 $record->status_name = $name;
        //                 $record->badge = 'badge-warning';
        //             }
        //         }
        //     }
        // }
        return view('termCondition.list', array('term_condition' => $list));
        // , 'users' => $users, 'visit_status' => $status
    }
    public function newTermCondition()
    {
        $t_c_number = DB::table('term_condition')->max('id') + 1;
        // $visit_status_data = config('constants.daily_visit_status');
        // $status = [];
        // foreach ($visit_status_data as $id => $name) {
        //     $statusObject = new \stdClass();
        //     $statusObject->id = $id;
        //     $statusObject->name = $name;
        //     $status[] = $statusObject;
        // }
        return view('termCondition.new', array('t_c_number' => $t_c_number));
        // , 'visit_status' => $status
    }

    public function saveTermCondition(Request $request)
    {
        // die("stop here");
        $term_condition = DB::table('term_condition')->where('t_c_number', $request->t_c_number)->first();
        if (!empty($term_condition)) {
            return response()->json(['success' => false, 'message' => 'T&C ID already exits..', 'redirectUrl' => '/termCondition/list'], 200);
        }
        $validator = Validator::make(
            $request->all(),
            [
                't_c_number' => 'required',
                'html_semantic' => 'required',
                'name' => 'required',
                'note' => 'required',
            ],
            [
                't_c_number.required' => ' T&C ID is required.',
                'html_semantic.required' => 'Something is Missing',
                'name.required' => 'name is required.',
                'note.required' => ' email is required.',
            ]
        );
        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 422);
        } else {
            $term_condition = array(
                't_c_number' => $request->t_c_number,
                'name' => $request->name,
                'note_html' => $request->html_semantic,
                'note' => $request->note,
                // 'user_id' => Auth::user()->id,
                // 'user_name' => Auth::user()->name,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $idForPdf = DB::table('term_condition')->insertGetId($term_condition);
            /***
             * add entry to transaction log
             *
             */
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->t_c_number,
                'transaction_action' => 'Add',
                'transaction_detail' => serialize($term_condition),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Terms & Conditions',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            // 'print' => "/quotation/pdf/{$idForPdf}
            return response()->json(['success' => true, 'message' => 'T&C Details added successfully..', 'redirectUrl' => "/termCondition/list"], 200);
        }
    }
    public function editTermCondition($id)
    {
        $term_condition = DB::table('term_condition')->where('id', $id)->first();
        if (empty($term_condition)) {
            return response()->json(['success' => false, 'message' => 'Record not found..', 'redirectUrl' => '/termCondition/list'], 404);
        }
        // $visit_status_data = config('constants.term_condition_status');
        // $status = [];
        // foreach ($visit_status_data as $id => $name) {
        //     $statusObject = new \stdClass();
        //     $statusObject->id = $id;
        //     $statusObject->name = $name;
        //     $status[] = $statusObject;
        // }
        return view('termCondition/new', array('term_condition' => $term_condition));
        // , 'visit_status' => $status
    }

    public function updateTermCondition(Request $request)
    {
        // die("stop here");
        $term_condition = DB::table('term_condition')->where('id', $request->id)->first();
        if (empty($term_condition)) {
            return response()->json(['success' => false, 'message' => 'Record does not exist..', 'redirectUrl' => '/termCondition/list'], 200);
        }
        $validator = Validator::make(
            $request->all(),
            [
                't_c_number' => 'required',
                'html_semantic' => 'required',
                'name' => 'required',
                'note' => 'required',
            ],
            [
                't_c_number.required' => ' T&C ID is required.',
                'html_semantic.required' => 'Something is Missing',
                'name.required' => 'name is required.',
                'note.required' => ' email is required.',
            ]
        );
        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 422);
        } else {
            $term_condition = array(
                't_c_number' => $request->t_c_number,
                'name' => $request->name,
                'note_html' => $request->html_semantic,
                'note' => $request->note,
                // 'user_id' => Auth::user()->id,
                // 'user_name' => Auth::user()->name,
                'updated_at' => date('Y-m-d H:i:s'),
            );

            DB::table('term_condition')->where('id', $request->id)->update($term_condition);
            /***
             * add entry to transaction log
             *
             */
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->t_c_number,
                'transaction_action' => 'Update',
                'transaction_detail' => serialize($term_condition),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Terms & Conditions',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            // 'print' => "/quotation/pdf/{$idForPdf}
            return response()->json(['success' => true, 'message' => 'T&C Details edited successfully..', 'redirectUrl' => "/termCondition/list"], 200);
        }
    }

    public function deleteTermCondition($id)
    {
        $term_condition = DB::table('term_condition')->where('id', $id)->first();
        if (empty($term_condition)) {
            return response()->json(['success' => false, 'message' => 'Record not found..', 'redirectUrl' => '/termCondition/list'], 404);
        }
        DB::table('term_condition')->where('t_c_number', $term_condition->t_c_number)->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $term_condition->t_c_number,
            'transaction_action' => 'Delete',
            'transaction_detail' => serialize($term_condition),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Daily Visits',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        return response()->json(['success' => true, 'message' => 'Record deleted successfully..', 'redirectUrl' => '/termCondition/list'], 200);
    }

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
    public function termConditionData(Request $request)
    {
        $data = array();
        if (isset($request->code)) {
            $data = DB::table('term_condition')->where('code', $request->code)->first();
        } else {
            $data = DB::table('term_condition')->where('id', $request->id)->first();
        }
        return response()->json(['success' => true, 'data' => $data]);
    }
}
