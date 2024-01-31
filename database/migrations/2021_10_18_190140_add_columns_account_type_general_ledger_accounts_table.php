<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsAccountTypeGeneralLedgerAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_ledger_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('account_type_id');
            $table->foreign('account_type_id')->references('id')->on('general_ledger_accounts_types');


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
