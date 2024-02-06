<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Image;

class companyController extends Controller
{
    //
    public function comList()
    {
        $comlist = DB::table('companyinfo')->get();
        return view('company.companyList', array('company' => $comlist));
    }
    public function editCompany($id)
    {
        $com = DB::table('companyinfo')->where('id', $id)->first();
        $currency_data = config('constants.currency');
        $currency = [];

        foreach ($currency_data as $id => $name) {
            $currencyObject = new \stdClass();
            $currencyObject->id = $id;
            $currencyObject->name = $name;
            $currency[] = $currencyObject;
        }
        return view('company.companyUpdate', array('company' => $com, 'currency' => $currency));
    }
    public function updateCompany(Request $request)
    {
        $compinfo = DB::table('companyinfo')->where('id', $request->id)->first();
        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required|min:3|max:500',
                'phone' => ['required', 'numeric'],
                'email' => 'required',
                'address' => 'required',
                'web' => 'required'
            ],
            [
                'title.required' => 'The full name field is required.',
                'phone.required' => 'The Phone No field is required.',
                'email.required' => 'The email field is required.',
                'address.required' => 'The address field is required.',
                'web.required' => 'The Web field is required.',
            ]
        );

        if ($validator->fails()) {
            $response['message'] = $validator->messages();
            return response()->json($response, 422);
        } else {
            $logo = Config::get('constants.COMPANU_DEFAULT_LOGO');
            $nav_logo = Config::get('constants.COMPANU_DEFAULT_LOGO');
            $report_logo = Config::get('constants.COMPANU_DEFAULT_LOGO');
            $comp = array(
                'title' => $request->title,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'stock_calculation' => $request->status,
                'auto_print_invoice' => $request->auto_print_invoice,
                'web' => $request->web,
                'currency_id' => $request->currency_id

            );
            if ($files = $request->file('logo')) {
                $destinationPath = public_path('/company_logo/'); // upload path
                $nav_logo = date('YmdHis') . "_nav." . $files->getClientOriginalExtension();
                $report_logo = date('YmdHis') . "_report." . $files->getClientOriginalExtension();
                $logo = date('YmdHis') . "." . $files->getClientOriginalExtension();

                $img = Image::make($files->path());
                $img->resize(100, 100, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $nav_logo);

                $img->resize(300, 168, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $report_logo);

                $img->resize(300, 100, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $logo);
                if ($compinfo->logo != Config::get('constants.COMPANU_DEFAULT_LOGO')) {
                    $this->removeImage($compinfo->logo);
                    $this->removeImage($compinfo->nav_logo);
                    $this->removeImage($compinfo->report_logo);
                }
                $comp['logo'] = '/company_logo/' . $logo;
                $comp['nav_logo'] = '/company_logo/' . $nav_logo;
                $comp['report_logo'] = '/company_logo/' . $report_logo;
            }



            $comp['updated_at'] = date('Y-m-d H:i:s');
            $user = DB::table('companyinfo')->where('id', $request->id)->update($comp);
            $log = array(
                'user_id' => Auth::user()->id,
                'voucher_number' => $request->id,
                'transaction_action' => 'Updated',
                'transaction_detail' => serialize($comp),
                'branch' => Auth::user()->branch,
                'transaction_type' => 'Company Information',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->addTransactionLog($log);
            return response()->json(['success' => true, 'message' => 'Company update successfully..', 'redirectUrl' => '/company/comlist'], 200);
        }
    }
    public function removeImage($path)
    {
        if (File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }

    public function addTransactionLog($data)
    {
        DB::table('transactions_log')->insertGetId($data);
    }
    public function databaseBackup()
    {
        // ENTER THE RELEVANT INFO BELOW
        $mysqlHostName = config('database.connections.mysql.host');
        $mysqlUserName = config('database.connections.mysql.username');
        $mysqlPassword = config('database.connections.mysql.password');
        $DbName = config('database.connections.mysql.database');
        $backup_name = "mybackup.sql";

        try {
            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $output = '';

            foreach ($tables as $table) {
                $table = get_object_vars($table);
                $tableName = reset($table);

                // Get create table statement
                $showTableResult = DB::select("SHOW CREATE TABLE $tableName");
                $createTableStatement = $showTableResult[0]->{"Create Table"};
                $output .= "\n\n$createTableStatement;\n\n";

                // Get table data
                $tableData = DB::table($tableName)->get();

                foreach ($tableData as $singleResult) {
                    $tableColumnArray = array_keys(get_object_vars($singleResult));
                    $tableValueArray = array_map(function ($value) {
                        return "'" . addslashes($value) . "'";
                    }, get_object_vars($singleResult));

                    $output .= "\nINSERT INTO $tableName(";
                    $output .= implode(", ", $tableColumnArray) . ") VALUES (";
                    $output .= implode(", ", $tableValueArray) . ");\n";
                }
            }

            // Write to file
            $fileName = 'database_backup_on_' . date('y-m-d') . '.sql';
            Storage::put($fileName, $output);

            // Download the file
            return response()->download(storage_path("app/$fileName"))
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
