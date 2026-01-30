<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerUbo;
use App\Models\CustomerUboDetail;
use Illuminate\Support\Str;

class ProcessCustomerUboJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $customerIds;
    public int $account_id;
    public int $account_branch_id;
    public $timeout = 1800;

    public function __construct(array $customerIds, int $account_id, int $account_branch_id)
    {
        $this->customerIds = $customerIds;
        $this->account_id = $account_id;
        $this->account_branch_id = $account_branch_id;
    }

    public function handle()
    {
        $account = Account::findOrFail($this->account_id);
        $connection = $this->setDatabaseConnection($account);

        // Pre-fetch UBOs once to avoid N+1 queries in the loop
        $customerUbos = CustomerUbo::on($connection)
            ->where('account_id', $this->account_id)
            ->where('account_branch_id', $this->account_branch_id)
            ->get()
            ->map(function ($ubo) {
                // Sanitize once for faster comparison
                $ubo->clean_name = $this->sanitize($ubo->name);
                $ubo->clean_address = $this->sanitize($ubo->address);
                return $ubo;
            });

        // Use a transaction for safety and speed
        DB::connection($connection)->transaction(function () use ($connection, $customerUbos) {

            // Process in chunks to handle large arrays efficiently
            Customer::on($connection)
                ->whereIn('id', $this->customerIds)
                ->where('account_id', $this->account_id)
                ->where('account_branch_id', $this->account_branch_id)
                ->with('ubo')
                ->chunkById(100, function ($customers) use ($connection, $customerUbos) {

                    $lastUboId = null;

                    foreach ($customers as $customer) {
                        if ($customer->ubo) continue;

                        $cleanName = $this->sanitize($customer->name);
                        $cleanAddr = $this->sanitize($customer->address);

                        $similarUbo = $customerUbos->first(function ($ubo) use ($cleanName, $cleanAddr) {
                            return $this->getScore($ubo->clean_name, $cleanName) >= 90
                                && $this->getScore($ubo->clean_address, $cleanAddr) >= 90;
                        });

                        if ($similarUbo) {
                            $this->linkDuplicate($customer, $similarUbo, $connection);
                        } else {
                            // Fetch max ID only once or increment locally
                            if (is_null($lastUboId)) {
                                $lastUboId = CustomerUbo::on($connection)->max('ubo_id') ?? 0;
                            }
                            $lastUboId++;

                            $this->createNewUbo($customer, $lastUboId, $connection);
                        }
                    }
                });
        });

        DB::setDefaultConnection(config('database.default'));
    }

    private function sanitize(?string $str): string
    {
        return Str::upper(str_replace(' ', '', $str ?? ''));
    }

    private function getScore(string $s1, string $s2): float
    {
        if ($s1 === $s2) return 100.0;
        $len1 = strlen($s1);
        $len2 = strlen($s2);
        if ($len1 === 0 || $len2 === 0) return 0.0;

        $dist = levenshtein($s1, $s2);
        return (1 - ($dist / max($len1, $len2))) * 100.0;
    }

    private function linkDuplicate($customer, $ubo, string $conn)
    {
        DB::connection($conn)->table('customer_ubo_details')->updateOrInsert(
            ['customer_id' => $customer->id, 'customer_ubo_id' => $ubo->id],
            [
                'account_id' => $this->account_id,
                'account_branch_id' => $this->account_branch_id,
                'ubo_id' => $ubo->ubo_id,
                'name' => $customer->name,
                'address' => $customer->address,
                'similarity' => $this->getScore($ubo->clean_name, $this->sanitize($customer->name)),
                'address_similarity' => $this->getScore($ubo->clean_address, $this->sanitize($customer->address)),
                'updated_at' => now()
            ]
        );

        $this->updateStatus($customer, 1, $conn);
    }

    private function createNewUbo($customer, int $newId, string $conn)
    {
        DB::connection($conn)->table('customer_ubos')->create([
            'account_id' => $this->account_id,
            'account_branch_id' => $this->account_branch_id,
            'customer_id' => $customer->id,
            'ubo_id' => $newId,
            'name' => $customer->name,
            'address' => $customer->address,
        ]);

        $this->updateStatus($customer, 0, $conn);
    }

    private function updateStatus($customer, int $status, string $conn)
    {
        // Update via Query Builder to avoid redundant model events and speed up execution
        Customer::on($conn)->where('id', $customer->id)->update(['status' => $status]);

        if (method_exists($customer, 'sales')) {
            $customer->sales()->update(['status' => $status]);
        }
    }

    private function setDatabaseConnection($account): string
    {
        $connectionName = 'tenant_' . $account->id;
        if (!config()->has("database.connections.$connectionName")) {
            config()->set("database.connections.$connectionName", [
                'driver' => 'mysql',
                'host' => $account->db_data->host ?? '127.0.0.1',
                'port' => $account->db_data->port ?? 3306,
                'database' => $account->db_data->database_name,
                'username' => $account->db_data->username ?? env('DB_USERNAME'),
                'password' => $account->db_data->password ?? env('DB_PASSWORD'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'strict' => true,
            ]);
        }
        DB::purge($connectionName); // Ensure fresh connection
        DB::setDefaultConnection($connectionName);
        return $connectionName;
    }
}
