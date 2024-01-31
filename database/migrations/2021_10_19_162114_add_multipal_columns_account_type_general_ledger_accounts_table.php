<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultipalColumnsAccountTypeGeneralLedgerAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_ledger_accounts', function (Blueprint $table) {
            $table->integer('account_type_id');
            $table->integer('chart_of_account_id');
            $table->integer('chart_of_accounts_category_id');
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
