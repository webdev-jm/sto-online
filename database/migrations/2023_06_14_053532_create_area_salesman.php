<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreaSalesman extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area_salesman', function (Blueprint $table) {
            $table->unsignedBigInteger('salesman_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();

            $table->primary(['salesman_id', 'area_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('area_salesman');
    }
}
