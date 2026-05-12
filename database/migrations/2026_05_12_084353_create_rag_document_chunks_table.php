<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlite_reports';

    public function up(): void
    {
        Schema::connection('sqlite_reports')->create('rag_document_chunks', function (Blueprint $table) {
            $table->id();
            $table->string('source_table');
            $table->unsignedBigInteger('source_id');
            $table->string('account_code');
            $table->text('content');
            $table->json('embedding');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['source_table', 'source_id']);
            $table->index('account_code');
        });
    }

    public function down(): void
    {
        Schema::connection('sqlite_reports')->dropIfExists('rag_document_chunks');
    }
};
