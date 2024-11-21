<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnToVendorProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_to_vendor_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_to_vendor_id')->nullable();
            $table->string('sku_code')->nullable();
            $table->string('other_sku_code')->nullable();
            $table->string('description')->nullable();
            $table->string('uom')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->text('reason')->nullable();
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
        Schema::dropIfExists('return_to_vendor_products');
    }
}
