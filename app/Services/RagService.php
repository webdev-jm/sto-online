<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RagService
{
    /**
     * Generate an embedding vector for the given text using Ollama's /api/embed endpoint.
     *
     * @return float[]
     */
    public function embed(string $text): array
    {
        $response = Http::timeout(60)->post(config('services.ollama.url') . '/api/embed', [
            'model' => config('services.ollama.embed_model', config('services.ollama.model')),
            'input' => $text,
        ]);

        $embeddings = $response->json('embeddings');

        return $embeddings[0] ?? [];
    }

    /**
     * Generate embeddings for multiple texts in a single Ollama request.
     *
     * @param  string[]  $texts
     * @return float[][]
     */
    public function embedBatch(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $response = Http::timeout(120)->post(config('services.ollama.url') . '/api/embed', [
            'model' => config('services.ollama.embed_model', config('services.ollama.model')),
            'input' => $texts,
        ]);

        return $response->json('embeddings') ?? [];
    }

    /**
     * Upsert a document chunk with its embedding into the rag_document_chunks table.
     * Skips re-embedding if content has not changed since the last index run.
     *
     * @param array<string, mixed> $metadata
     */
    public function indexChunk(
        string $sourceTable,
        int $sourceId,
        string $accountCode,
        string $content,
        array $metadata = []
    ): void {
        $hash = hash('sha256', $content);

        $existing = DB::connection('sqlite_reports')
            ->table('rag_document_chunks')
            ->where('source_table', $sourceTable)
            ->where('source_id', $sourceId)
            ->value('content_hash');

        if ($existing === $hash) {
            return;
        }

        DB::connection('sqlite_reports')->table('rag_document_chunks')->updateOrInsert(
            ['source_table' => $sourceTable, 'source_id' => $sourceId],
            [
                'account_code' => $accountCode,
                'content'      => $content,
                'content_hash' => $hash,
                'embedding'    => json_encode($this->embed($content)),
                'metadata'     => json_encode($metadata),
                'created_at'   => now(),
            ]
        );
    }

    /**
     * Retrieve the top-K most relevant chunks for a query and account.
     *
     * Uses a cursor + running min-heap so only topK entries are held in memory
     * regardless of how many chunks are indexed.
     *
     * @return string[]
     */
    public function retrieve(string $query, string $accountCode, int $topK = 5): array
    {
        $queryVector = $this->embed($query);

        if (empty($queryVector)) {
            return [];
        }

        $top = [];

        $cursor = DB::connection('sqlite_reports')
            ->table('rag_document_chunks')
            ->where(fn($q) => $q->where('account_code', $accountCode)->orWhere('account_code', 'global'))
            ->select(['content', 'embedding'])
            ->cursor();

        foreach ($cursor as $chunk) {
            $score = $this->cosineSimilarity($queryVector, json_decode($chunk->embedding, true) ?? []);

            if (\count($top) < $topK) {
                $top[] = ['content' => $chunk->content, 'score' => $score];
                if (\count($top) === $topK) {
                    usort($top, fn($a, $b) => $a['score'] <=> $b['score']);
                }
            } elseif ($score > $top[0]['score']) {
                $top[0] = ['content' => $chunk->content, 'score' => $score];
                usort($top, fn($a, $b) => $a['score'] <=> $b['score']);
            }
        }

        usort($top, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_column($top, 'content');
    }

    /**
     * @param float[] $a
     * @param float[] $b
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b) || \count($a) !== \count($b)) {
            return 0.0;
        }

        $dot  = array_sum(array_map(fn($x, $y) => $x * $y, $a, $b));
        $magA = sqrt(array_sum(array_map(fn($x) => $x ** 2, $a)));
        $magB = sqrt(array_sum(array_map(fn($x) => $x ** 2, $b)));

        return ($magA && $magB) ? $dot / ($magA * $magB) : 0.0;
    }
}
