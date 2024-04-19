<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerUboDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_ubo_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('account_branch_id');
            $table->unsignedBigInteger('customer_ubo_id');
            $table->unsignedBigInteger('customer_id');
            $table->bigInteger('ubo_id');
            $table->string('name');
            $table->string('address');
            $table->decimal('similarity', 10, 2);
            $table->decimal('address_similarity', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_ubo_details');
    }
}
