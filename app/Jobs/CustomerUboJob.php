<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerUbo;
use App\Models\CustomerUboDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CustomerUboJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;

    public function __construct(
        public int $account_id,
        public int $account_branch_id,
    ) {}

    public function handle(): void
    {
        $account = Account::findOrFail($this->account_id);
        $connection = $this->setDatabaseConnection($account);

        // Pre-fetch and sanitize UBOs once to avoid repeated string ops inside the loop.
        $existingUbos = CustomerUbo::on($connection)
            ->select('id', 'ubo_id', 'name', 'address')
            ->get()
            ->each(function ($ubo) {
                $ubo->clean_name = $this->sanitize($ubo->name);
                $ubo->clean_address = $this->sanitize($ubo->address);
            });

        $maxUboId = $existingUbos->max('ubo_id') ?? 0;

        Customer::on($connection)
            ->where('account_id', $this->account_id)
            ->where('account_branch_id', $this->account_branch_id)
            ->where('status', 0)
            ->doesntHave('ubo')
            ->doesntHave('ubo_detail')
            ->chunkById(500, function ($customers) use ($connection, $existingUbos, &$maxUboId) {
                DB::connection($connection)->transaction(function () use ($customers, $connection, $existingUbos, &$maxUboId) {
                    foreach ($customers as $customer) {
                        $cleanName = $this->sanitize($customer->name);
                        $cleanAddr = $this->sanitize($customer->address);

                        $match = $this->findDuplicate($cleanName, $cleanAddr, $existingUbos);

                        if ($match) {
                            $this->linkDuplicate($customer, $match, $connection);
                        } else {
                            $newUbo = $this->createUbo($customer, ++$maxUboId, $connection);
                            $newUbo->clean_name = $cleanName;
                            $newUbo->clean_address = $cleanAddr;
                            $existingUbos->push($newUbo);
                        }
                    }
                });
            });

        DB::setDefaultConnection(config('database.default'));
    }

    /**
     * Returns the matching UBO and pre-computed scores, or null if no match.
     *
     * @return array{ubo: CustomerUbo, name_sim: float, addr_sim: float}|null
     */
    private function findDuplicate(string $cleanName, string $cleanAddr, $existingUbos): ?array
    {
        foreach ($existingUbos as $ubo) {
            $nameSim = $this->getScore($ubo->clean_name, $cleanName);
            if ($nameSim >= 90) {
                $addrSim = $this->getScore($ubo->clean_address, $cleanAddr);
                if ($addrSim >= 90) {
                    return ['ubo' => $ubo, 'name_sim' => $nameSim, 'addr_sim' => $addrSim];
                }
            }
        }

        return null;
    }

    private function linkDuplicate($customer, array $match, string $connection): void
    {
        CustomerUboDetail::on($connection)->updateOrInsert(
            ['customer_id' => $customer->id, 'customer_ubo_id' => $match['ubo']->id],
            [
                'account_id' => $customer->account_id,
                'account_branch_id' => $customer->account_branch_id,
                'ubo_id' => $match['ubo']->ubo_id,
                'name' => $customer->name,
                'address' => $customer->address,
                'similarity' => $match['name_sim'],
                'address_similarity' => $match['addr_sim'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->updateCustomerStatus($customer, 1);
    }

    private function createUbo($customer, int $uboId, string $connection): CustomerUbo
    {
        $ubo = CustomerUbo::on($connection)->create([
            'account_id' => $customer->account_id,
            'account_branch_id' => $customer->account_branch_id,
            'customer_id' => $customer->id,
            'ubo_id' => $uboId,
            'name' => $customer->name,
            'address' => $customer->address,
        ]);

        $this->updateCustomerStatus($customer, 0);

        return $ubo;
    }

    private function updateCustomerStatus($customer, int $status): void
    {
        $customer->update(['status' => $status]);
        $customer->sales()->update(['status' => $status]);
    }

    private function sanitize(?string $str): string
    {
        return strtoupper(str_replace(' ', '', $str ?? ''));
    }

    private function getScore(string $s1, string $s2): float
    {
        if ($s1 === $s2) {
            return 100.0;
        }

        $maxLen = max(\strlen($s1), \strlen($s2));

        if ($maxLen === 0) {
            return 100.0;
        }

        return (1 - levenshtein($s1, $s2) / $maxLen) * 100.0;
    }

    private function setDatabaseConnection(Account $account): string
    {
        $connectionName = 'tenant_' . $account->id;

        if (!config()->has('database.connections.' . $connectionName)) {
            config()->set('database.connections.' . $connectionName, [
                'driver' => 'mysql',
                'host' => $account->db_data->host ?? '127.0.0.1',
                'port' => $account->db_data->port ?? 3306,
                'database' => $account->db_data->database_name,
                'username' => $account->db_data->username ?? env('DB_USERNAME'),
                'password' => $account->db_data->password ?? env('DB_PASSWORD'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'strict' => true,
                'engine' => 'InnoDB',
            ]);
        }

        DB::setDefaultConnection($connectionName);

        return $connectionName;
    }
}
