<?php

namespace App\Console\Commands;

use App\Services\RagService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RagIndex extends Command
{
    protected $signature = 'rag:index
        {--account_code= : Only index chunks for this account code}
        {--type=all      : Which source to index: sales, inventory, inventory_aging, docs, or all}';

    protected $description = 'Index sales.sqlite data and docs into rag_document_chunks for RAG retrieval';

    public function handle(RagService $rag): int
    {
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

    private function indexSalesData(RagService $rag, ?string $accountCode): void
    {
        $query = DB::connection('sqlite_reports')->table('sales_data');

        if ($accountCode) {
            $query->where('account_code', $accountCode);
        }

        $rows  = $query->get();
        $bar   = $this->output->createProgressBar($rows->count());
        $bar->setFormat("Sales     %current%/%max% [%bar%] %percent%%");
        $bar->start();

        foreach ($rows as $row) {
            $content = sprintf(
                'Sales %d-%02d: Customer %s (%s, %s), Salesman: %s (%s), Channel: %s, Area: %s. '
                . 'Product: %s %s (Brand: %s), Qty: %s %s, Sales Amount: PHP %s.',
                $row->year,
                $row->month,
                $row->customer_name,
                $row->city,
                $row->province,
                $row->salesman_name,
                $row->salesman_type,
                $row->channel_name,
                $row->area,
                $row->description,
                $row->size,
                $row->brand,
                number_format((float) $row->quantity, 2),
                $row->uom,
                number_format((float) $row->sales, 2)
            );

            $rag->indexChunk('sales_data', (int) $row->id, $row->account_code, $content, [
                'year'  => $row->year,
                'month' => $row->month,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("  Indexed {$rows->count()} sales records.");
    }

    private function indexInventoryData(RagService $rag, ?string $accountCode): void
    {
        $query = DB::connection('sqlite_reports')->table('inventory_data');

        if ($accountCode) {
            $query->where('account_code', $accountCode);
        }

        $rows = $query->get();
        $bar  = $this->output->createProgressBar($rows->count());
        $bar->setFormat("Inventory %current%/%max% [%bar%] %percent%%");
        $bar->start();

        foreach ($rows as $row) {
            $content = sprintf(
                'Inventory %d-%02d: Location %s (%s). Product: %s %s (%s), Total: %s %s.',
                $row->year,
                $row->month,
                $row->location_name,
                $row->location_code,
                $row->description,
                $row->size,
                $row->stock_code,
                number_format((float) $row->total, 2),
                $row->uom
            );

            $rag->indexChunk('inventory_data', (int) $row->id, $row->account_code, $content, [
                'year'  => $row->year,
                'month' => $row->month,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("  Indexed {$rows->count()} inventory records.");
    }

    private function indexInventoryAging(RagService $rag, ?string $accountCode): void
    {
        $query = DB::connection('sqlite_reports')->table('inventory_aging');

        if ($accountCode) {
            $query->where('account_code', $accountCode);
        }

        $rows = $query->get();
        $bar  = $this->output->createProgressBar($rows->count());
        $bar->setFormat("Aging     %current%/%max% [%bar%] %percent%%");
        $bar->start();

        foreach ($rows as $row) {
            $content = sprintf(
                'Inventory Aging at %s: Product %s %s (%s), Stock: %s %s, Expiry Date: %s.',
                $row->location_name,
                $row->description,
                $row->size,
                $row->stock_code,
                number_format((float) $row->inventory, 2),
                $row->uom,
                $row->expiry_date
            );

            $rag->indexChunk('inventory_aging', (int) $row->id, $row->account_code, $content, [
                'expiry_date' => $row->expiry_date,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("  Indexed {$rows->count()} inventory aging records.");
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

        $totalChunks = 0;

        foreach ($files as $file) {
            $filename = basename($file);
            $content  = file_get_contents($file);
            $sections = $this->splitIntoSections($content);
            $bar      = $this->output->createProgressBar(count($sections));
            $bar->setFormat("Docs [{$filename}] %current%/%max% [%bar%] %percent%%");
            $bar->start();

            foreach ($sections as $index => $section) {
                if (empty(trim($section))) {
                    $bar->advance();
                    continue;
                }

                // Use a stable ID: hash of filename + section index
                $sourceId = crc32($filename . ':' . $index);

                $rag->indexChunk('docs', $sourceId, 'global', $section, [
                    'file'    => $filename,
                    'section' => $index,
                ]);

                $bar->advance();
                $totalChunks++;
            }

            $bar->finish();
            $this->newLine();
        }

        $this->line("  Indexed {$totalChunks} doc chunk(s) from " . count($files) . " file(s).");
    }

    /** @return string[] */
    private function splitIntoSections(string $content): array
    {
        // Split on markdown headings (##, ---) to create meaningful chunks
        $sections = preg_split('/\n(?=#{1,3} |\n---)/u', $content);
        return array_values(array_filter($sections, fn($s) => strlen(trim($s)) > 20));
    }
}
