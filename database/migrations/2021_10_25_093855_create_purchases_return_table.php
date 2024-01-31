<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesReturnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases_return', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->double('net_total', 8, 2);
            $table->binary('items_detail');
            $table->string('invoice_number');
            $table->longText('note');
            $table->date('invoice_date');
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
        Schema::dropIfExists('purchases_return');
    }
}
