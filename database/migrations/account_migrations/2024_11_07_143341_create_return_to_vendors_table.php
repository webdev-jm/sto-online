<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnToVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_to_vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('account_branch_id')->nullable();
            $table->string('rtv_number');
            $table->string('document_number')->nullable();
            $table->date('ship_date')->nullable();
            $table->date('entry_date')->nullable();
            $table->text('reason')->nullable();
            $table->string('ship_to_name')->nullable();
            $table->string('ship_to_address')->nullable();
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
        Schema::dropIfExists('return_to_vendors');
    }
}
