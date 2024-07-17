<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockOnHandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_on_hands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_branch_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->integer('year');
            $table->integer('month');
            $table->integer('total_inventory');
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_on_hands');
    }
}
