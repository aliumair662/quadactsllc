<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScrapSpoilageLossInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scrap_spoilage_loss_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number');
            $table->binary('item_detail');
            $table->date('voucher_date');
            $table->float('net_total');
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
        Schema::dropIfExists('scrap_spoilage_loss_inventory');
    }
}
