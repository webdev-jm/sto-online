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

use App\Http\Traits\LevenshteinTrait;

class CustomerUboJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use LevenshteinTrait;

    public $account_id;
    public $account_branch_id;
    public $timeout = 1200;

    public function __construct($account_id, $account_branch_id)
    {
        $this->account_id = $account_id;
        $this->account_branch_id = $account_branch_id;
    }

    public function handle()
    {
        $account = Account::findOrFail($this->account_id);
        $connection = $this->setDatabaseConnection($account);

        // 1. Pre-fetch existing UBOs into memory (if the dataset isn't millions)
        // This avoids running a heavy Levenshtein SQL query for every single customer.
        $existingUbos = CustomerUbo::on($connection)
            ->select('id', 'ubo_id', 'name', 'address')
            ->get();

        Customer::on($connection)
            ->where('account_id', $this->account_id)
            ->where('account_branch_id', $this->account_branch_id)
            ->where('status', 0)
            ->doesntHave('ubo')
            ->doesntHave('ubo_detail')
            ->with('sales') // Eager load sales to avoid N+1 in updateCustomerStatus
            ->chunkById(500, function ($customers) use ($connection, $existingUbos) {

                // Wrap in transaction for speed and data integrity
                DB::connection($connection)->transaction(function () use ($customers, $existingUbos, $connection) {

                    // Track the last UBO ID in memory to avoid repeated DB hits
                    $currentMaxUboId = $existingUbos->max('ubo_id') ?? 0;

                    foreach ($customers as $customer) {
                        $duplicate = $this->findDuplicateInMemory($customer, $existingUbos);

                        if ($duplicate) {
                            $this->handleDuplicateCustomer($customer, $duplicate, $connection);
                        } else {
                            $newUbo = $this->createNewUboForCustomer($customer, ++$currentMaxUboId, $connection);
                            $existingUbos->push($newUbo); // Add to memory list for next iteration
                        }
                    }
                });
            });
    }

    /**
     * Optimized: Find duplicates using PHP's Levenshtein (much faster than DB calls inside a loop)
     */
    private function findDuplicateInMemory($customer, $existingUbos)
    {
        foreach ($existingUbos as $ubo) {
            $nameSim = $this->checkSimilarity($ubo->name, $customer->name);
            if ($nameSim >= 90) {
                $addrSim = $this->checkSimilarity($ubo->address, $customer->address);
                if ($addrSim >= 90) {
                    // Attach similarity scores dynamically for handleDuplicateCustomer
                    $ubo->temp_name_sim = $nameSim;
                    $ubo->temp_addr_sim = $addrSim;
                    return $ubo;
                }
            }
        }
        return null;
    }

    private function handleDuplicateCustomer($customer, $duplicate, $connection)
    {
        CustomerUboDetail::on($connection)->updateOrInsert(
            ['customer_id' => $customer->id, 'customer_ubo_id' => $duplicate->id],
            [
                'account_id' => $customer->account_id,
                'account_branch_id' => $customer->account_branch_id,
                'ubo_id' => $duplicate->ubo_id,
                'name' => $customer->name,
                'address' => $customer->address,
                'similarity' => $duplicate->temp_name_sim,
                'address_similarity' => $duplicate->temp_addr_sim,
                'updated_at' => now(),
            ]
        );

        $this->updateCustomerStatus($customer, 1);
    }

    private function createNewUboForCustomer($customer, $newId, $connection)
    {
        $ubo = CustomerUbo::on($connection)->create([
            'account_id' => $customer->account_id,
            'account_branch_id' => $customer->account_branch_id,
            'customer_id' => $customer->id,
            'ubo_id' => $newId,
            'name' => $customer->name,
            'address' => $customer->address,
        ]);

        $this->updateCustomerStatus($customer, 0);
        return $ubo;
    }

    private function updateCustomerStatus($customer, int $status)
    {
        $customer->update(['status' => $status]);
        // sales() update is now more efficient due to eager loading or direct query
        $customer->sales()->update(['status' => $status]);
    }

    private function setDatabaseConnection(Account $account)
    {
        $connectionName = 'tenant_' . $account->id;

        if (!config()->has('database.connections.' . $connectionName)) {
            config()->set('database.connections.' . $connectionName, [
                'driver'         => 'mysql',
                'host'           => $account->db_data->host ?? '127.0.0.1',
                'port'           => $account->db_data->port ?? 3306,
                'database'       => $account->db_data->database_name,
                'username'       => $account->db_data->username ?? env('DB_USERNAME'),
                'password'       => $account->db_data->password ?? env('DB_PASSWORD'),
                'unix_socket'    => '',
                'charset'        => 'utf8mb4',
                'collation'      => 'utf8mb4_unicode_ci',
                'prefix'         => '',
                'strict'         => true,
                'engine'         => 'InnoDB',
            ]);
        }

        DB::setDefaultConnection($connectionName);
        return $connectionName;
    }
}
