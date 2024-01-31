<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number');
            $table->date('voucher_date');
            $table->binary('voucher_detail');
            $table->longText('note');
            $table->float('net_total');
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
        Schema::dropIfExists('general_receipts');
    }
}
