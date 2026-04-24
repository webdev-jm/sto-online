<?php

namespace App\Jobs;

use App\Http\Traits\ConsolidateAccountData;
use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ConsolidateAccountDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ConsolidateAccountData;

    public int $timeout = 0;
    public int $tries = 3;

    public function __construct(public Account $account) {}

    /**
     * Serialize all consolidation jobs so only one writes to SQLite at a time.
     * SQLite does not support concurrent writers.
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping('consolidate-account-reports'))->releaseAfter(30)];
    }

    public function handle(): void
    {
        ini_set('memory_limit', '-1');

        $this->consolidateSingleAccount($this->account);

        foreach ([2025, 2026] as $year) {
            Cache::forget("sales_data_consolidated_{$year}");
            Cache::forget("inventory_data_consolidated_{$year}");
            Cache::forget("inventory_aging_data_consolidated_{$year}");
        }
    }
}
