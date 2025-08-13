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
    public $timeout = 1800; // 30 minutes

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $customerIds, int $account_id, int $account_branch_id)
    {
        $this->customerIds = $customerIds;
        $this->account_id = $account_id;
        $this->account_branch_id = $account_branch_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $account = Account::findOrFail($this->account_id);
        $connectionName = $this->setDatabaseConnection($account);

        // Fetch only the customers relevant to this job
        $customers = Customer::on($connectionName)
            ->whereIn('id', $this->customerIds)
            ->where('account_id', $this->account_id)
            ->where('account_branch_id', $this->account_branch_id)
            ->get();

        // Pre-fetch existing UBOs for efficiency
        $customerUbos = CustomerUbo::on($connectionName)
            ->where('account_id', $this->account_id)
            ->where('account_branch_id', $this->account_branch_id)
            ->get();

        foreach ($customers as $customer) {
            // Skip if customer already has an UBO associated (e.g., from a previous run or manual assignment)
            if ($customer->ubo()->exists()) {
                continue;
            }

            $similarUbo = $customerUbos->first(function ($item) use ($customer) {
                return $this->checkSimilarity($item->name, $customer->name) >= 90
                    && $this->checkSimilarity($item->address, $customer->address) >= 90;
            });

            if ($similarUbo) {
                $this->handleDuplicateCustomer($customer, $similarUbo, $connectionName);
            } else {
                $this->createNewUboForCustomer($customer, $connectionName);
            }
        }

        // Reset the connection if necessary for other jobs in the queue
        DB::setDefaultConnection(config('database.default'));
    }

    /**
     * Sets the database connection for the job.
     */
    private function setDatabaseConnection(Account $account): string
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

    /**
     * Handle a customer identified as a duplicate.
     */
    private function handleDuplicateCustomer(Customer $customer, CustomerUbo $potential_duplicate, string $connectionName)
    {
        $percent = $this->checkSimilarity($potential_duplicate->name, $customer->name);
        $address_pc = $this->checkSimilarity($potential_duplicate->address, $customer->address);

        CustomerUboDetail::on($connectionName)->updateOrInsert(
            [
                'customer_id'       => $customer->id,
                'customer_ubo_id'   => $potential_duplicate->id,
            ],
            [
                'account_id'        => $customer->account_id,
                'account_branch_id' => $customer->account_branch_id,
                'ubo_id'            => $potential_duplicate->ubo_id,
                'name'              => $customer->name,
                'address'           => $customer->address,
                'similarity'        => $percent,
                'address_similarity'=> $address_pc,
                'created_at'        => now(),
                'updated_at'        => now()
            ]
        );

        $this->updateCustomerStatus($customer, 1);
    }

    /**
     * Create a new UBO record for a unique customer.
     */
    private function createNewUboForCustomer(Customer $customer, string $connectionName)
    {
        // Optimized UBO ID generation
        // This might be a bottleneck if many jobs run concurrently and rely on MAX(ubo_id)
        // A better approach might be to use UUIDs or a dedicated sequence table.
        // For now, keeping it simple as per original logic.
        $last_ubo_id = CustomerUbo::on($connectionName)->max('ubo_id') ?? 0;

        CustomerUbo::on($connectionName)->create([
            'account_id'        => $customer->account_id,
            'account_branch_id' => $customer->account_branch_id,
            'customer_id'       => $customer->id,
            'ubo_id'            => $last_ubo_id + 1,
            'name'              => $customer->name,
            'address'           => $customer->address,
        ]);

        $this->updateCustomerStatus($customer, 0);
    }

    /**
     * Updates the status for a customer and their sales records.
     */
    private function updateCustomerStatus(Customer $customer, int $status)
    {
        $customer->status = $status;
        $customer->save();

        // Assuming 'sales' is a relationship. Use the relationship to update.
        // This assumes the 'sales' relationship is defined on the Customer model.
        // If not, this line might cause an error or do nothing.
        if (method_exists($customer, 'sales')) {
            $customer->sales()->update(['status' => $status]);
        }
    }

    /**
     * Calculates the similarity between two strings using Levenshtein distance.
     */
    private function checkSimilarity(?string $str1, ?string $str2): float
    {
        $s1 = Str::upper(str_replace(' ', '', $str1 ?? ''));
        $s2 = Str::upper(str_replace(' ', '', $str2 ?? ''));

        $len1 = strlen($s1);
        $len2 = strlen($s2);

        if ($len1 === 0 && $len2 === 0) return 100.0; // Both empty, consider them same
        if ($len1 === 0 || $len2 === 0) return 0.0;   // One is empty, not similar

        $maxLength = max($len1, $len2);
        $distance = levenshtein($s1, $s2);

        return (1 - ($distance / $maxLength)) * 100.0;
    }
}