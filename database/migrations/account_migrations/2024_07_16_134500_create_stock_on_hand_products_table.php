<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockOnHandProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_on_hand_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_on_hand_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('sku_code');
            $table->string('sku_code_other')->nullable();
            $table->string('inventory');
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
        Schema::dropIfExists('stock_on_hand_products');
    }
}
