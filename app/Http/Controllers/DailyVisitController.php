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

class DailyVisitController extends Controller
{
    public function dailyVisitList()
    {
        if (Auth::user()->is_admin) {
            $list = DB::table('daily_visits')
                ->orderByDesc('id')
                ->paginate(20);
        } else {
            $list = DB::table('daily_visits')
                ->where('user_id', Auth::user()->id)
                ->orderByDesc('id')
                ->paginate(20);
        }
        $users = DB::table('users')->where('status', 1)->get();
        $visit_status_data = config('constants.daily_visit_status');
        $status = [];
        foreach ($visit_status_data as $id => $name) {
            $statusObject = new \stdClass();
            $statusObject->id = $id;
            $statusObject->name = $name;
            $status[] = $statusObject;
        }
        foreach ($list as $record) {
            for ($i = 0; $i < count($visit_status_data); $i++) {
                $id = array_keys($visit_status_data)[$i];
                $name = $visit_status_data[$id];
                if ($id == $record->status_id) {
                    $record->status_name = $name;
                    if ($id == 1) $record->badge = 'badge-warning';
                    elseif ($id == 2) $record->badge = 'badge-danger';
                    elseif ($id == 3) $record->badge = 'badge-success';
                } else {
                    if ($id == 1) {
                        $record->status_name = $name;
                        $record->badge = 'badge-warning';
                    }
                }
            }
        }
        return view('dailyVisits.list', array('daily_visits' => $list, 'users' => $users, 'visit_status' => $status));
    }

    public function newDailyVisit()
    {
        $invoice_number = DB::table('daily_visits')->max('id') + 1;
        $visit_status_data = config('constants.daily_visit_status');
        $status = [];
        foreach ($visit_status_data as $id => $name) {
            $statusObject = new \stdClass();
            $statusObject->id = $id;
            $statusObject->name = $name;
            $status[] = $statusObject;
        }
        return view('dailyVisits.new', array('invoice_number' => $invoice_number, 'visit_status' => $status));
    }

    public function editDailyVisit($id)
    {
        $daily_visit = DB::table('daily_visits')->where('id', $id)->first();
        if (empty($daily_visit)) {
            return response()->json(['success' => false, 'message' => 'Record not found..', 'redirectUrl' => '/dailyVisit/list'], 404);
        }
        $visit_status_data = config('constants.daily_visit_status');
        $status = [];
        foreach ($visit_status_data as $id => $name) {
            $statusObject = new \stdClass();
            $statusObject->id = $id;
            $statusObject->name = $name;
            $status[] = $statusObject;
        }
        return view('dailyVisits/new', array('dailyVisit' => $daily_visit, 'visit_status' => $status));
    }

    public function saveDailyVisit(Request $request)
    {
        $daily_visit = DB::table('daily_visits')->where('invoice_number', $request->invoice_number)->first();
        if (!empty($daily_visit)) {
            return response()->json(['success' => false, 'message' => 'Visit ID already exits..', 'redirectUrl' => '/dailyVisit/list'], 200);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'invoice_number' => 'required',
                'invoice_date' => 'required',
                'name' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'address' => 'required',
                'latitude' => 'required',
                'longitute' => 'required',
                'location' => 'required',
                'description' => 'required',
                // 'status_id' => 'required'
            ],
            [
                'invoice_number.required' => ' Visit # is required.',
                'invoice_date.required' => ' Visit Date is required.',
                'name.required' => ' Business name is required.',
                'email.required' => ' email is required.',
                'phone.required' => ' phone is required.',
                'address.required' => ' address is required.',
                'latitude.required' => ' latitude is required.',
                'longitute.required' => ' latitude is required.',
                'location' => ' location is required.',
                'description.required' => 'Notes is required.',
                // 'status_id.required' => 'Status is required.'
            ]
        );
        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 422);
        } else {
            if ($file = $request->file('attachment')) {
                $relativePath = 'daily-visits';
                $newFileName = uniqid() . "." . $file->getClientOriginalExtension();

                $img = Image::make($file->path())->resize(800, 600, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize(false);
                });
                $img->save(public_path($relativePath) . DIRECTORY_SEPARATOR . $newFileName);

                $storedRelativePath = $relativePath . DIRECTORY_SEPARATOR . $newFileName;
            }
            $daily_visit = array(
                'invoice_number' => $request->invoice_number,
                'invoice_date' => now()->format('Y-m-d H:i:s'),
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitute' => $request->longitute,
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->name,
                'created_at' => date('Y-m-d H:i:s'),
                'description' => $request->description,
                'location' => $request->location,
                'attachment' => isset($storedRelativePath) ? $storedRelativePath : null,
                'status_id' => $request->status_id ?? 1
            );
            $idForPdf = DB::table('daily_visits')->insertGetId($daily_visit);
            /***
             * add entry to transaction log
             *
             */
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->invoice_number,
                'transaction_action' => 'Add',
                'transaction_detail' => serialize($daily_visit),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Daily Visits',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            // 'print' => "/quotation/pdf/{$idForPdf}
            return response()->json(['success' => true, 'message' => 'Visit Details added successfully..', 'redirectUrl' => "/dailyVisit/list"], 200);
        }
    }

    public function updatedailyVisit(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'invoice_number' => 'required',
                'invoice_date' => 'required',
                'name' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'address' => 'required',
                'latitude' => 'required',
                'longitute' => 'required',
                'location' => 'required',
                'description' => 'required',
                // 'status_id' => 'required'
            ],
            [
                'invoice_number.required' => 'The Visit # is required.',
                'invoice_date.required' => 'The Visit Date is required.',
                'name.required' => 'The business name is required.',
                'email.required' => 'The email is required.',
                'phone.required' => 'The phone is required.',
                'address.required' => 'The address is required.',
                'latitude.required' => 'The latitude is required.',
                'longitute.required' => 'The latitude is required.',
                'location' => 'The location is required.',
                'description.required' => 'Notes is required.',
                // 'status_id.required' => 'Status is required.'
            ]
        );
        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 422);
        } else {

            if ($file = $request->file('attachment')) {
                $record = DB::table('daily_visits')->where('id', $request->id)->first();
                $imagePath = public_path($record->attachment);

                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }
                $relativePath = 'daily-visits';
                $newFileName = uniqid() . "." . $file->getClientOriginalExtension();

                $img = Image::make($file->path())->resize(800, 600, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize(false);
                });
                $img->save(public_path($relativePath) . DIRECTORY_SEPARATOR . $newFileName);

                $storedRelativePath = $relativePath . DIRECTORY_SEPARATOR . $newFileName;
            }
            $daily_visit = array(
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitute' => $request->longitute,
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->name,
                'updated_at' => date('Y-m-d H:i:s'),
                'description' => $request->description,
                'location' => $request->location,
                'attachment' => isset($storedRelativePath) ? $storedRelativePath : null,
                'status_id' => $request->status_id ?? 1
            );

            DB::table('daily_visits')->where('id', $request->id)->update($daily_visit);
            /***
             * add entry to transaction log
             *
             */
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->invoice_number,
                'transaction_action' => 'Update',
                'transaction_detail' => serialize($daily_visit),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Daily Visits',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            // 'print' => "/quotation/pdf/{$idForPdf}
            return response()->json(['success' => true, 'message' => 'Visit Details edited successfully..', 'redirectUrl' => "/dailyVisit/list"], 200);
        }
    }

    public function deleteDailyVisit($id)
    {
        $daily_visit = DB::table('daily_visits')->where('id', $id)->first();
        if (empty($daily_visit)) {
            return response()->json(['success' => false, 'message' => 'Record not found..', 'redirectUrl' => '/dailyVisit/list'], 404);
        }
        $imagePath = public_path($daily_visit->attachment);
        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }
        DB::table('daily_visits')->where('invoice_number', $daily_visit->invoice_number)->delete();
        $log = array(
            'user_id' => Auth::user()->id,
            'voucher_number' => $daily_visit->invoice_number,
            'transaction_action' => 'Delete',
            'transaction_detail' => serialize($daily_visit),
            'branch' => Auth::user()->branch,
            'transaction_type' => 'Daily Visits',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->addTransactionLog($log);
        return response()->json(['success' => true, 'message' => 'Record deleted successfully..', 'redirectUrl' => '/dailyVisit/list'], 200);
    }

    public function dailyVisitRecordPdf(Request $request, $id)
    {
        $daily_visit = DB::table('daily_visits')->where('id', $id)->first();
        $companyinfo = DB::table('companyinfo')->first();
        $companyinfo->logo = url('/') . $companyinfo->logo;

        // $qrCodeString = $this->generateQrCode($request->url());
        // 'qrCodeString' => $qrCodeString
        $data =  array('dailyVisit' => $daily_visit, 'companyinfo' => $companyinfo);

        $pdf = PDF::loadView('dailyVisits.dailyVisitPdf', $data);
        return $pdf->stream('dailyVisitPdf.pdf');
    }

    public function searchDailyVisit(Request $request)
    {
        $Queries = array();

        if (empty($request->from_date) && empty($request->to_date) && empty($request->invoice_number) && empty($request->user_id) && empty($request->status_id)) {
            return redirect('dailyVisits.list');
        }
        $query = DB::table('daily_visits');

        if (!empty($request->from_date) && !empty($request->to_date)) {
            $Queries['from_date'] = $request->from_date;
            $Queries['to_date'] = $request->to_date;
            $from = date('Y-m-d 00:00:00', strtotime($request->from_date));
            $to = date('Y-m-d 23:59:59', strtotime($request->to_date));
            $query->whereBetween('daily_visits.invoice_date', [$from, $to]);
        }
        if (!empty($request->invoice_number)) {
            $Queries['invoice_number'] = $request->invoice_number;
            $query->where('daily_visits.invoice_number', 'like', "%$request->invoice_number%");
        }
        if (!empty($request->user_id)) {
            $Queries['user_id'] = $request->user_id;
            $query->where('daily_visits.user_id', '=', $request->user_id);
        }
        if (!empty($request->status_id)) {
            $Queries['status_id'] = $request->status_id;
            $query->where('daily_visits.status_id', '=', $request->status_id);
        }
        $list = $query->orderByDesc('daily_visits.id')->paginate(20);

        $visit_status_data = config('constants.daily_visit_status');
        $status = [];
        foreach ($visit_status_data as $id => $name) {
            $statusObject = new \stdClass();
            $statusObject->id = $id;
            $statusObject->name = $name;
            $status[] = $statusObject;
        }
        foreach ($list as $record) {
            for ($i = 0; $i < count($visit_status_data); $i++) {
                $id = array_keys($visit_status_data)[$i];
                $name = $visit_status_data[$id];
                if ($id == $record->status_id) {
                    $record->status_name = $name;
                    if ($id == 1) $record->badge = 'badge-warning';
                    elseif ($id == 2) $record->badge = 'badge-danger';
                    elseif ($id == 3) $record->badge = 'badge-success';
                } else {
                    if ($id == 1) {
                        $record->status_name = $name;
                        $record->badge = 'badge-warning';
                    }
                }
            }
        }


        $list->appends($Queries);
        $users = DB::table('users')->where('status', 1)->get();
        return view('dailyVisits.list', array('daily_visits' => $list, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'invoice_number' => $request->invoice_number, 'user_id' => $request->user_id, 'users' => $users, 'visit_status' => $status, 'status_id' => $request->status_id));
    }

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
}
