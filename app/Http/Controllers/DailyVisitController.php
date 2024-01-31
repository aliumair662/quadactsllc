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
        return view('dailyVisits.list', array('daily_visits' => $list));
    }

    public function newDailyVisit()
    {
        $invoice_number = DB::table('daily_visits')->max('id') + 1;

        return view('dailyVisits.new', array('invoice_number' => $invoice_number));
    }

    public function editDailyVisit($id)
    {
        $daily_visit = DB::table('daily_visits')->where('id', $id)->first();
        if (empty($daily_visit)) {
            return response()->json(['success' => false, 'message' => 'Record not found..', 'redirectUrl' => '/dailyVisit/list'], 404);
        }
        $daily_visit->attachment = 'daily-visits\65ba442edefb5.png';
        Log::debug($daily_visit->attachment);
        return view('dailyVisits/new', array('dailyVisit' => $daily_visit));
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
            ],
            [
                'invoice_number.required' => 'The Visit # is required.',
                'invoice_date.required' => 'The Visit Date is required.',
                'name.required' => 'The name is required.',
                'email.required' => 'The email is required.',
                'phone.required' => 'The phone is required.',
                'address.required' => 'The address is required.',
                'latitude.required' => 'The latitude is required.',
                'longitute.required' => 'The latitude is required.',
                'location' => 'The location is required.',
                'description.required' => 'Notes is required.',
            ]
        );
        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 422);
        } else {

            if ($files = $request->file('attachment')) {
                $relativePath = 'daily-visits';
                $newFileName = uniqid() . "." . $files->getClientOriginalExtension();

                $img = Image::make($files->path());
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
                'created_at' => date('Y-m-d H:i:s'),
                'description' => $request->description,
                'location' => $request->location,
                'attachment' => $storedRelativePath,
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
            ],
            [
                'invoice_number.required' => 'The Visit # is required.',
                'invoice_date.required' => 'The Visit Date is required.',
                'name.required' => 'The name is required.',
                'email.required' => 'The email is required.',
                'phone.required' => 'The phone is required.',
                'address.required' => 'The address is required.',
                'latitude.required' => 'The latitude is required.',
                'longitute.required' => 'The latitude is required.',
                'location' => 'The location is required.',
                'description.required' => 'Notes is required.',
            ]
        );
        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 422);
        } else {

            if ($files = $request->file('attachment')) {
                $relativePath = 'daily-visits';
                $newFileName = uniqid() . "." . $files->getClientOriginalExtension();

                $img = Image::make($files->path());
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
                'attachment' => $storedRelativePath,
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

        // if ($companyinfo->auto_print_invoice == 0) {
        $pdf = PDF::loadView('dailyVisits.dailyVisitPdf', $data);
        // }
        // else {
        //     $customPaper = array(20, 0, 800.00, 280.80);
        //     $pdf = PDF::loadView('dailyVisits.dailyVisitThermalPdf', $data)->setPaper($customPaper, 'landscape');
        // }
        return $pdf->stream('dailyVisitPdf.pdf');
    }

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
}
