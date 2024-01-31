<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsGeneralLedgerAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_ledger_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('chart_of_accounts_category_id');
            $table->foreign('chart_of_accounts_category_id')->references('id')->on('chart_of_accounts_category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('general_ledger_accounts', function (Blueprint $table) {
            //
        });
    }
}
