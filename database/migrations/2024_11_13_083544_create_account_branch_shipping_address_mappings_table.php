<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountBranchShippingAddressMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_branch_shipping_address_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_branch_id')->nullable();
            $table->string('ship_to_code');
            $table->string('ship_to_name')->nullable();
            $table->string('bevi_ship_to_code');
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
        Schema::dropIfExists('account_branch_shipping_address_mappings');
    }
}
