<?php

namespace App\Console\Commands;

use App\Services\RagService;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RagIndex extends Command
{
    protected $signature = 'rag:index
        {--account_code= : Only index chunks for this account code}
        {--type=all      : Which source to index: sales, inventory, inventory_aging, docs, or all}
        {--resume        : Skip records that are already indexed (resume an interrupted run)}
        {--batch=50      : Number of records to embed per Ollama request}';

    protected $description = 'Index sales.sqlite data and docs into rag_document_chunks for RAG retrieval';

    public function handle(RagService $rag): int
    {
        $this->ensureTableExists();

        $accountCode = $this->option('account_code');
        $type        = $this->option('type');

        $this->info("Starting RAG indexing (type={$type}" . ($accountCode ? ", account={$accountCode}" : '') . ')');

        if (in_array($type, ['all', 'sales'])) {
            $this->indexSalesData($rag, $accountCode);
        }

        if (in_array($type, ['all', 'inventory'])) {
            $this->indexInventoryData($rag, $accountCode);
        }

        if (in_array($type, ['all', 'inventory_aging'])) {
            $this->indexInventoryAging($rag, $accountCode);
        }

        if (in_array($type, ['all', 'docs'])) {
            $this->indexDocs($rag);
        }

        $this->info('RAG indexing complete.');
        return self::SUCCESS;
    }

    private function ensureTableExists(): void
    {
        $schema = Schema::connection('sqlite_reports');

        if (!$schema->hasTable('rag_document_chunks')) {
            $schema->create('rag_document_chunks', function (Blueprint $table) {
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

            $this->info('Created rag_document_chunks table.');
            return;
        }

        $schema->table('rag_document_chunks', function (Blueprint $table) use ($schema) {
            if (!$schema->hasColumn('rag_document_chunks', 'content_hash')) {
                $table->string('content_hash', 64)->nullable();
                $this->info('Added missing column: content_hash.');
            }
        });
    }

    /**
     * Pre-load all indexed (source_id => content_hash) pairs for a source table.
     *
     * @return array<int, string>
     */
    private function loadExistingHashes(string $sourceTable): array
    {
        return DB::connection('sqlite_reports')
            ->table('rag_document_chunks')
            ->where('source_table', $sourceTable)
            ->whereNotNull('content_hash')
            ->pluck('content_hash', 'source_id')
            ->all();
    }

    /**
     * Embed a batch of pending chunks and bulk-upsert them.
     *
     * @param array<int, array<string, mixed>> $batch
     */
    private function flushBatch(RagService $rag, array $batch): void
    {
        $embeddings = $rag->embedBatch(array_column($batch, 'content'));

        $records = [];
        foreach ($batch as $i => $item) {
            $records[] = [
                'source_table' => $item['source_table'],
                'source_id'    => $item['source_id'],
                'account_code' => $item['account_code'],
                'content'      => $item['content'],
                'content_hash' => $item['content_hash'],
                'embedding'    => json_encode($embeddings[$i] ?? []),
                'metadata'     => $item['metadata'],
                'created_at'   => now(),
            ];
        }

        DB::connection('sqlite_reports')->table('rag_document_chunks')->upsert(
            $records,
            ['source_table', 'source_id'],
            ['account_code', 'content', 'content_hash', 'embedding', 'metadata', 'created_at']
        );
    }

    private function indexSalesData(RagService $rag, ?string $accountCode): void
    {
        $existing  = $this->loadExistingHashes('sales_data');
        $resume    = $this->option('resume');
        $batchSize = (int) $this->option('batch');

        $query = DB::connection('sqlite_reports')->table('sales_data');

        if ($accountCode) {
            $query->where('account_code', $accountCode);
        }

        if ($resume) {
            $query->whereNotIn('id', function ($sub) {
                $sub->select('source_id')->from('rag_document_chunks')->where('source_table', 'sales_data');
            });
        }

        $total   = (clone $query)->count();
        $bar     = $this->output->createProgressBar($total);
        $indexed = 0;
        $batch   = [];
        $bar->setFormat("Sales     %current%/%max% [%bar%] %percent%%");
        $bar->start();

        foreach ($query->lazyById(1000) as $row) {
            $content = sprintf(
                'Sales %d-%02d: Customer %s (%s, %s), Salesman: %s (%s), Channel: %s, Area: %s. '
                . 'Product: %s %s (Brand: %s), Qty: %s %s, Sales Amount: PHP %s.',
                $row->year, $row->month,
                $row->customer_name, $row->city, $row->province,
                $row->salesman_name, $row->salesman_type,
                $row->channel_name, $row->area,
                $row->description, $row->size, $row->brand,
                number_format((float) $row->quantity, 2), $row->uom,
                number_format((float) $row->sales, 2)
            );

            $bar->advance();
            $hash = hash('sha256', $content);

            if (isset($existing[(int) $row->id]) && $existing[(int) $row->id] === $hash) {
                continue;
            }

            $batch[] = [
                'source_table' => 'sales_data',
                'source_id'    => (int) $row->id,
                'account_code' => $row->account_code,
                'content'      => $content,
                'content_hash' => $hash,
                'metadata'     => json_encode(['year' => $row->year, 'month' => $row->month]),
            ];

            if (\count($batch) >= $batchSize) {
                $this->flushBatch($rag, $batch);
                $indexed += \count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->flushBatch($rag, $batch);
            $indexed += \count($batch);
        }

        $bar->finish();
        $this->newLine();
        $this->line("  Indexed {$indexed} of {$total} sales records.");
    }

    private function indexInventoryData(RagService $rag, ?string $accountCode): void
    {
        $existing  = $this->loadExistingHashes('inventory_data');
        $resume    = $this->option('resume');
        $batchSize = (int) $this->option('batch');

        $query = DB::connection('sqlite_reports')->table('inventory_data');

        if ($accountCode) {
            $query->where('account_code', $accountCode);
        }

        if ($resume) {
            $query->whereNotIn('id', function ($sub) {
                $sub->select('source_id')->from('rag_document_chunks')->where('source_table', 'inventory_data');
            });
        }

        $total   = (clone $query)->count();
        $bar     = $this->output->createProgressBar($total);
        $indexed = 0;
        $batch   = [];
        $bar->setFormat("Inventory %current%/%max% [%bar%] %percent%%");
        $bar->start();

        foreach ($query->lazyById(1000) as $row) {
            $content = sprintf(
                'Inventory %d-%02d: Location %s (%s). Product: %s %s (%s), Total: %s %s.',
                $row->year, $row->month,
                $row->location_name, $row->location_code,
                $row->description, $row->size, $row->stock_code,
                number_format((float) $row->total, 2), $row->uom
            );

            $bar->advance();
            $hash = hash('sha256', $content);

            if (isset($existing[(int) $row->id]) && $existing[(int) $row->id] === $hash) {
                continue;
            }

            $batch[] = [
                'source_table' => 'inventory_data',
                'source_id'    => (int) $row->id,
                'account_code' => $row->account_code,
                'content'      => $content,
                'content_hash' => $hash,
                'metadata'     => json_encode(['year' => $row->year, 'month' => $row->month]),
            ];

            if (\count($batch) >= $batchSize) {
                $this->flushBatch($rag, $batch);
                $indexed += \count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->flushBatch($rag, $batch);
            $indexed += \count($batch);
        }

        $bar->finish();
        $this->newLine();
        $this->line("  Indexed {$indexed} of {$total} inventory records.");
    }

    private function indexInventoryAging(RagService $rag, ?string $accountCode): void
    {
        $existing  = $this->loadExistingHashes('inventory_aging');
        $resume    = $this->option('resume');
        $batchSize = (int) $this->option('batch');

        $query = DB::connection('sqlite_reports')->table('inventory_aging');

        if ($accountCode) {
            $query->where('account_code', $accountCode);
        }

        if ($resume) {
            $query->whereNotIn('id', function ($sub) {
                $sub->select('source_id')->from('rag_document_chunks')->where('source_table', 'inventory_aging');
            });
        }

        $total   = (clone $query)->count();
        $bar     = $this->output->createProgressBar($total);
        $indexed = 0;
        $batch   = [];
        $bar->setFormat("Aging     %current%/%max% [%bar%] %percent%%");
        $bar->start();

        foreach ($query->lazyById(1000) as $row) {
            $content = sprintf(
                'Inventory Aging at %s: Product %s %s (%s), Stock: %s %s, Expiry Date: %s.',
                $row->location_name,
                $row->description, $row->size, $row->stock_code,
                number_format((float) $row->inventory, 2), $row->uom,
                $row->expiry_date
            );

            $bar->advance();
            $hash = hash('sha256', $content);

            if (isset($existing[(int) $row->id]) && $existing[(int) $row->id] === $hash) {
                continue;
            }

            $batch[] = [
                'source_table' => 'inventory_aging',
                'source_id'    => (int) $row->id,
                'account_code' => $row->account_code,
                'content'      => $content,
                'content_hash' => $hash,
                'metadata'     => json_encode(['expiry_date' => $row->expiry_date]),
            ];

            if (\count($batch) >= $batchSize) {
                $this->flushBatch($rag, $batch);
                $indexed += \count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->flushBatch($rag, $batch);
            $indexed += \count($batch);
        }

        $bar->finish();
        $this->newLine();
        $this->line("  Indexed {$indexed} of {$total} inventory aging records.");
    }

    private function indexDocs(RagService $rag): void
    {
        $docsPath = storage_path('app/docs');

        if (!is_dir($docsPath)) {
            $this->line('  No docs directory found at storage/app/docs — skipping.');
            return;
        }

        $files = glob($docsPath . '/*.{md,txt}', GLOB_BRACE);

        if (empty($files)) {
            $this->line('  No .md or .txt files found in storage/app/docs — skipping.');
            return;
        }

        $existing    = $this->loadExistingHashes('docs');
        $batchSize   = (int) $this->option('batch');
        $totalChunks = 0;

        foreach ($files as $file) {
            $filename = basename($file);
            $content  = file_get_contents($file);
            $sections = $this->splitIntoSections($content);
            $bar      = $this->output->createProgressBar(\count($sections));
            $batch    = [];
            $bar->setFormat("Docs [{$filename}] %current%/%max% [%bar%] %percent%%");
            $bar->start();

            foreach ($sections as $index => $section) {
                $bar->advance();

                if (empty(trim($section))) {
                    continue;
                }

                $sourceId = crc32($filename . ':' . $index);
                $hash     = hash('sha256', $section);

                if (isset($existing[$sourceId]) && $existing[$sourceId] === $hash) {
                    continue;
                }

                $batch[] = [
                    'source_table' => 'docs',
                    'source_id'    => $sourceId,
                    'account_code' => 'global',
                    'content'      => $section,
                    'content_hash' => $hash,
                    'metadata'     => json_encode(['file' => $filename, 'section' => $index]),
                ];

                if (\count($batch) >= $batchSize) {
                    $this->flushBatch($rag, $batch);
                    $totalChunks += \count($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                $this->flushBatch($rag, $batch);
                $totalChunks += \count($batch);
            }

            $bar->finish();
            $this->newLine();
        }

        $this->line("  Indexed {$totalChunks} doc chunk(s) from " . \count($files) . " file(s).");
    }

    /** @return string[] */
    private function splitIntoSections(string $content): array
    {
        $sections = preg_split('/\n(?=#{1,3} |\n---)/u', $content);
        return array_values(array_filter($sections, fn($s) => strlen(trim($s)) > 20));
    }
}
