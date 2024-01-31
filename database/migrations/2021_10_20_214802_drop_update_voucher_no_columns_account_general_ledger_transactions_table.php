<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUpdateVoucherNoColumnsAccountGeneralLedgerTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_ledger_transactions', function (Blueprint $table) {
            $table->dropColumn('voucher_no');
            $table->text('voucher_number');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('general_ledger_transactions', function (Blueprint $table) {
            //
        });
    }
}
