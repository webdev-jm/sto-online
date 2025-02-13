<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->string('path');
            $table->timestamps();

            $table->foreign('purchase_order_id')
                ->references('id')->on('purchase_orders')
                ->onDelete('cascade');

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
        Schema::dropIfExists('purchase_order_attachments');
    }
}
