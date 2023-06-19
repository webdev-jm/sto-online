<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('sku_count')->nullable();
            $table->integer('total_quantity')->default(0);
            $table->decimal('total_price_vat', 10, 2)->default(0);
            $table->decimal('total_amount_vat', 10, 2)->default(0);
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
        Schema::dropIfExists('sales_uploads');
    }
}
