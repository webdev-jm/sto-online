<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountUploadTemplateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_upload_template_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_upload_template_id')->nullable();
            $table->unsignedBigInteger('upload_template_field_id')->nullable();
            $table->integer('number');
            $table->string('file_column_name')->nullable();
            $table->integer('file_column_number')->nullable();
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
        Schema::dropIfExists('account_upload_template_fields');
    }
}
