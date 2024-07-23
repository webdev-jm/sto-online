<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTransferProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_transfer_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_transfer_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('sku_code');
            $table->string('sku_code_other')->nullable();
            $table->integer('transfer_ty')->default(0);
            $table->integer('transfer_ly')->default(0);
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
        Schema::dropIfExists('stock_transfer_products');
    }
}
