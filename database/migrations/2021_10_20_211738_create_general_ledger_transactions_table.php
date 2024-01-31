<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralLedgerTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->text('voucher_no');
            $table->text('voucher_date');
            $table->integer('general_ledger_account_id');
            $table->longText('note')->nullable();
            $table->float('debit')->default(0);
            $table->float('credit')->default(0);
            $table->integer('branch')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('general_ledger_transactions');
    }
}
