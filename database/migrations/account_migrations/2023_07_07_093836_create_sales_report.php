<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_report', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('account_branch_id');
            $table->unsignedBigInteger('customer_id');
            $table->integer('year');
            $table->integer('month');
            $table->unsignedBigInteger('product_id');
            $table->string('stock_code');
            $table->string('description')->nullable();
            $table->string('size')->nullable();
            $table->string('brand_classification')->nullable();
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->string('uom')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('sales', 10, 2)->nullable();
            $table->integer('fg_quantity')->nullable();
            $table->decimal('fg_sales', 10, 2)->nullable();
            $table->integer('promo_quantity')->nullable();
            $table->decimal('promo_sales', 10, 2)->nullable();
            $table->decimal('credit_memo', 10, 2)->nullable();
            $table->decimal('parked_quantity', 10, 2)->nullable();
            $table->decimal('parked_amount', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_report');
    }
}
