<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlite_reports';

    public function up(): void
    {
        Schema::connection('sqlite_reports')->table('rag_document_chunks', function (Blueprint $table) {
            $table->string('content_hash', 64)->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::connection('sqlite_reports')->table('rag_document_chunks', function (Blueprint $table) {
            $table->dropColumn('content_hash');
        });
    }
};
