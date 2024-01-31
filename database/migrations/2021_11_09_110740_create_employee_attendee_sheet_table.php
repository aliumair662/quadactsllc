<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeAttendeeSheetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_attendee_sheet', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number');
            $table->integer('employee_id');
            $table->date('month_year');
            $table->double('total_present');
            $table->double('total_absent');
            $table->double('total_leave');
            $table->double('half_days');
            $table->double('holiday');
            $table->text('basic_salary');
            $table->text('net_working_days');
            $table->text('net_salary');
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
        Schema::dropIfExists('employee_attendee_sheet');
    }
}
