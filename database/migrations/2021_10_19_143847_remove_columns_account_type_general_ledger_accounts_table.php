<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsAccountTypeGeneralLedgerAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_ledger_accounts', function (Blueprint $table) {
            $table->dropForeign('general_ledger_accounts_account_type_id_foreign');
            $table->dropColumn('account_type_id');
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
