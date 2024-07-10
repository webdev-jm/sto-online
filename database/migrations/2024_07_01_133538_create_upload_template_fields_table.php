<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadTemplateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upload_template_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upload_template_id')->nullable();
            $table->integer('number');
            $table->string('column_name');
            $table->string('column_name_alt')->nullable();
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
        Schema::dropIfExists('upload_template_fields');
    }
}
