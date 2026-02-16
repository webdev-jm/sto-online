<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consolidated_sales_reports', function (Blueprint $table) {
            $table->id();
            $table->string('account_code');
            $table->string('account_name');
            $table->string('account_description');
            $table->string('customer_code');
            $table->string('customer_name');
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('brgy')->nullable();
            $table->integer('year');
            $table->integer('month');
            $table->string('stock_code');
            $table->string('description');
            $table->string('size')->nullable();
            $table->string('brand_classification')->nullable();
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->string('uom');
            $table->double('quantity');
            $table->double('sales');
            $table->double('fg_quantity')->nullable();
            $table->double('fg_sales')->nullable();
            $table->double('promo_quantity')->nullable();
            $table->double('promo_sales')->nullable();
            $table->double('credit_memo')->nullable();
            $table->double('parked_quantity')->nullable();
            $table->double('parked_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consolidated_sales_reports');
    }
};
