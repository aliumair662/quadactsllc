<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostProductionManuallyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_production_manually', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->string('voucher_number');
            $table->date('voucher_date');
            $table->text('actual_production_amount');
            $table->text('gross_total');
            $table->text('total_Advance');
            $table->text('deduction_amount');
            $table->text('total_payment_received_period');
            $table->text('additional_amount');
            $table->text('net_total');
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
        Schema::dropIfExists('post_production_manually');
    }
}
