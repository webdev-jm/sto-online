<?php

namespace App\Console\Commands;

use App\Jobs\ConsolidateAccountDataJob;
use App\Models\Account;
use Illuminate\Console\Command;

class ConsolidateAllAccountsCommand extends Command
{
    protected $signature = 'reports:consolidate';
    protected $description = 'Queue report consolidation jobs for all tenant accounts';

    public function handle(): void
    {
        $accounts = Account::where('id', '>=', 10)->get();

        $accounts->each(fn(Account $account) => ConsolidateAccountDataJob::dispatch($account));

        $this->info("Queued consolidation for {$accounts->count()} account(s).");
    }
}
