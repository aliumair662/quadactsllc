<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;
use Illuminate\Support\Facades\DB;
class CompanyInfoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        View::composer('*', function($view){

            $companyinfo = DB::table('companyinfo')->first();
            $companyinfo->logo=url('/').$companyinfo->logo;
            $companyinfo->nav_logo=url('/').$companyinfo->nav_logo;
            $companyinfo->report_logo=url('/').$companyinfo->report_logo;
            $view->with('companyinfo', $companyinfo);
        });
    }
}
