<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RagIndexCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetTable();
    }

    protected function tearDown(): void
    {
        $this->resetTable();
        parent::tearDown();
    }

    private function resetTable(): void
    {
        Schema::connection('sqlite_reports')->dropIfExists('rag_document_chunks');
        Schema::connection('sqlite_reports')->create('rag_document_chunks', function (Blueprint $table) {
            $table->id();
            $table->string('source_table');
            $table->unsignedBigInteger('source_id');
            $table->string('account_code');
            $table->text('content');
            $table->string('content_hash', 64)->nullable();
            $table->json('embedding');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unique(['source_table', 'source_id']);
            $table->index('account_code');
        });
    }

    public function test_rag_index_succeeds_when_table_already_exists(): void
    {
        Http::fake(['*/api/embed' => Http::response(['embeddings' => [[0.1, 0.2, 0.3]]])]);

        $this->artisan('rag:index', ['--type' => 'docs'])
            ->assertExitCode(0);
    }

    public function test_rag_index_creates_table_if_missing(): void
    {
        Schema::connection('sqlite_reports')->dropIfExists('rag_document_chunks');

        Http::fake(['*/api/embed' => Http::response(['embeddings' => [[0.1, 0.2, 0.3]]])]);

        $this->artisan('rag:index', ['--type' => 'docs'])
            ->assertExitCode(0);

        $this->assertTrue(
            Schema::connection('sqlite_reports')->hasTable('rag_document_chunks'),
            'rag_document_chunks table should be created by rag:index'
        );
    }

    public function test_rag_index_adds_missing_content_hash_column(): void
    {
        Schema::connection('sqlite_reports')->dropIfExists('rag_document_chunks');
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

        Http::fake(['*/api/embed' => Http::response(['embeddings' => [[0.1, 0.2, 0.3]]])]);

        $this->artisan('rag:index', ['--type' => 'docs'])
            ->assertExitCode(0);

        $this->assertTrue(
            Schema::connection('sqlite_reports')->hasColumn('rag_document_chunks', 'content_hash'),
            'content_hash column should be added by rag:index'
        );
    }
}
