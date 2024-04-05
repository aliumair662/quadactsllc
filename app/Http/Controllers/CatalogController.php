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

class CatalogController extends Controller
{
    public function catalogList()
    {
        $items = DB::table('items')
            // ->select('id', 'pic', 'name', 'code', 'note_html')
            ->orderByDesc('id')
            ->get();
        return view('catalog.list', array('catalogs' => $items));
    }
}
