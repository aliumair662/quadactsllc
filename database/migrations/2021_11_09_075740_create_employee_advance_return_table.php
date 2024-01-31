<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeAdvanceReturnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_advance_return', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->text('amount');
            $table->longText('note');
            $table->date('voucher_date');
            $table->string('voucher_number');
            $table->integer('payment_type');
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
        Schema::dropIfExists('employee_advance_return');
    }
}
