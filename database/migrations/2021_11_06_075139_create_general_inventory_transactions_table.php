<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralInventoryTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number');
            $table->integer('general_ledger_account_id');
            $table->date('voucher_date');
            $table->integer('item_id');
            $table->double('item_qty');
            $table->text('transaction_type');
            $table->integer('branch');
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
        Schema::dropIfExists('general_inventory_transactions');
    }
}
