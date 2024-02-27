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


class transactionLogController extends Controller
{
    public function list()
    {
        $list = DB::table('transactions_log')
            ->leftJoin('users', 'transactions_log.user_id', '=', 'users.id')
            // ->where('transactions_log.branch', Auth::user()->branch)
            ->select('transactions_log.*', 'users.name')
            ->orderByDesc('transactions_log.id')
            ->paginate(20);
        return view('transactionLog.list', array('lists' => $list));
    }

    public function logSearch(Request $request)
    {
        $Queries = array();
        if (isset($request->from_date) || isset($request->to_date) || isset($request->username)) {
            $query = DB::table('transactions_log');
            $query->join('users', 'transactions_log.user_id', '=', 'users.id');

            if (isset($request->username)) {
                $Queries['name'] = $request->username;
                $query->where('users.name', 'like', "%$request->username%");
            }
            if (isset($request->from_date) && isset($request->to_date)) {
                $Queries['from'] = $request->from_date;
                $Queries['to'] = $request->to_date;
                $query->whereBetween('transactions_log.created_at', [$request->from_date, $request->to_date]);
            }
            $result = $query
                // ->where('transactions_log.branch', Auth::user()->branch)
                ->orderByDesc('transactions_log.id')->paginate(20);

            return view('transactionLog.list', array('lists' => $result, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'customer_name' => $request->username));
        } else {
            $list = DB::table('transactions_log')
                ->leftJoin('users', 'transactions_log.user_id', '=', 'users.id')
                // ->where('transactions_log.branch', Auth::user()->branch)
                ->paginate(20);
            return view('transactionLog.list', array('lists' => $list));
        }
    }
}
