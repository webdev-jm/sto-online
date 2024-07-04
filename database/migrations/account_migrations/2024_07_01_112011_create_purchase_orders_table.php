<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_branch_id')->nullable();
            $table->string('po_number');
            $table->date('order_date');
            $table->date('ship_date');
            $table->string('shipping_instruction')->nullable();
            $table->string('ship_to_name')->nullable();
            $table->string('ship_to_address')->nullable();
            $table->string('status')->nullable();
            $table->integer('total_quantity')->default(0);
            $table->decimal('total_sales', 15,2)->default(0.00);
            $table->decimal('grand_total', 15, 2)->default(0.00);
            $table->decimal('po_value', 15, 2)->default(0.00);
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
        Schema::dropIfExists('purchase_orders');
    }
}
