<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('gross_amount',500);
            $table->double('discount_percentage');
            $table->string('discount_amount',500);
            $table->string('additional_charges',500);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('gross_amount');
            $table->dropColumn('discount_percentage');
            $table->dropColumn('discount_amount');
            $table->dropColumn('additional_charges');
        });
    }
}
