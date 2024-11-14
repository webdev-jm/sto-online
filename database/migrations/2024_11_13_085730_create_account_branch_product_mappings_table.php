<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountBranchProductMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_branch_product_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_branch_id')->nullable();
            $table->string('sku_code');
            $table->string('other_sku_code')->nullable();
            $table->string('description')->nullable();
            $table->string('uom')->nullable();
            $table->string('brand')->nullable();
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
        Schema::dropIfExists('account_branch_product_mappings');
    }
}
