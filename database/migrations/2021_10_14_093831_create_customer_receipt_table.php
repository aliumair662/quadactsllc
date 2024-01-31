<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerReceiptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_receipt', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no');
            $table->integer('customer');
            $table->dateTime('received_date');
            $table->integer('payment_mode');
            $table->string('check_number')->nullable();
            $table->string('about_back')->nullable();
            $table->string('note');
            $table->float('amount');
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
        Schema::dropIfExists('customer_receipt');
    }
}
