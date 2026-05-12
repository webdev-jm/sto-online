<?php

namespace Tests\Feature;

use App\Services\RagService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RagServiceTest extends TestCase
{
    use DatabaseTransactions;

    /** Test-only source IDs — deleted before and after each test. */
    private const TEST_SOURCE_IDS = [6001, 6002, 7001, 7002, 8888, 9999];

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanTestRows();
    }

    protected function tearDown(): void
    {
        $this->cleanTestRows();
        parent::tearDown();
    }

    private function cleanTestRows(): void
    {
        DB::connection('sqlite_reports')
            ->table('rag_document_chunks')
            ->whereIn('source_id', self::TEST_SOURCE_IDS)
            ->delete();
    }

    /** @param float[] $vector */
    private function ollamaEmbedResponse(array $vector = null): array
    {
        $vector ??= [0.1, 0.2, 0.3, 0.4, 0.5];
        return ['embeddings' => [$vector]];
    }

    private function makeService(): RagService
    {
        return app(RagService::class);
    }

    // ---------------------------------------------------------------------------
    // embed()
    // ---------------------------------------------------------------------------

    public function test_embed_returns_vector_from_ollama(): void
    {
        Http::fake(['*/api/embed' => Http::response($this->ollamaEmbedResponse([0.1, 0.9, 0.3]))]);

        $vector = $this->makeService()->embed('test text');

        $this->assertSame([0.1, 0.9, 0.3], $vector);
    }

    public function test_embed_returns_empty_array_on_missing_response(): void
    {
        Http::fake(['*/api/embed' => Http::response(['embeddings' => []])]);

        $vector = $this->makeService()->embed('test text');

        $this->assertSame([], $vector);
    }

    // ---------------------------------------------------------------------------
    // indexChunk() + retrieve()
    // ---------------------------------------------------------------------------

    public function test_index_chunk_stores_content_in_sqlite(): void
    {
        Http::fake(['*/api/embed' => Http::response($this->ollamaEmbedResponse())]);

        $this->makeService()->indexChunk('sales_data', 9999, 'TEST001', 'Sales 2025-07: Kojie.San Soap.', ['year' => 2025]);

        $this->assertDatabaseHas('rag_document_chunks', [
            'source_table' => 'sales_data',
            'source_id'    => 9999,
            'account_code' => 'TEST001',
            'content'      => 'Sales 2025-07: Kojie.San Soap.',
        ], 'sqlite_reports');
    }

    public function test_index_chunk_is_idempotent(): void
    {
        Http::fake(['*/api/embed' => Http::response($this->ollamaEmbedResponse())]);

        $service = $this->makeService();
        $service->indexChunk('sales_data', 8888, 'TEST001', 'Original content.');
        $service->indexChunk('sales_data', 8888, 'TEST001', 'Updated content.');

        $count = DB::connection('sqlite_reports')
            ->table('rag_document_chunks')
            ->where('source_table', 'sales_data')
            ->where('source_id', 8888)
            ->count();

        $this->assertSame(1, $count);

        $this->assertDatabaseHas('rag_document_chunks', [
            'source_id' => 8888,
            'content'   => 'Updated content.',
        ], 'sqlite_reports');
    }

    public function test_retrieve_returns_top_k_chunks_by_similarity(): void
    {
        $closeVector = [1.0, 0.0, 0.0];
        $farVector   = [0.0, 0.0, 1.0];
        $queryVector = [1.0, 0.0, 0.0];

        DB::connection('sqlite_reports')->table('rag_document_chunks')->insert([
            ['source_table' => 'sales_data', 'source_id' => 7001, 'account_code' => 'ACCT1',
             'content' => 'Close chunk', 'embedding' => json_encode($closeVector), 'metadata' => null, 'created_at' => now()],
            ['source_table' => 'sales_data', 'source_id' => 7002, 'account_code' => 'ACCT1',
             'content' => 'Far chunk', 'embedding' => json_encode($farVector), 'metadata' => null, 'created_at' => now()],
        ]);

        Http::fake(['*/api/embed' => Http::response($this->ollamaEmbedResponse($queryVector))]);

        $results = $this->makeService()->retrieve('my query', 'ACCT1', 1);

        $this->assertCount(1, $results);
        $this->assertSame('Close chunk', $results[0]);
    }

    public function test_retrieve_filters_by_account_code(): void
    {
        DB::connection('sqlite_reports')->table('rag_document_chunks')->insert([
            ['source_table' => 'sales_data', 'source_id' => 6001, 'account_code' => 'ACCT_A',
             'content' => 'Account A chunk', 'embedding' => json_encode([1.0, 0.0]), 'metadata' => null, 'created_at' => now()],
            ['source_table' => 'sales_data', 'source_id' => 6002, 'account_code' => 'ACCT_B',
             'content' => 'Account B chunk', 'embedding' => json_encode([1.0, 0.0]), 'metadata' => null, 'created_at' => now()],
        ]);

        Http::fake(['*/api/embed' => Http::response($this->ollamaEmbedResponse([1.0, 0.0]))]);

        $results = $this->makeService()->retrieve('query', 'ACCT_A', 10);

        $this->assertContains('Account A chunk', $results);
        $this->assertNotContains('Account B chunk', $results);
    }

    public function test_retrieve_returns_empty_when_embed_fails(): void
    {
        Http::fake(['*/api/embed' => Http::response(['embeddings' => []])]);

        $results = $this->makeService()->retrieve('query', 'ACCT1');

        $this->assertSame([], $results);
    }
}
