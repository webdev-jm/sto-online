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
use App\Models\Salesman;
use App\Models\SalesmanCustomer;
use Carbon\Carbon;

// 1. We removed the ini_set calls. Good code doesn't need them.
class CustomerImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $customer_data;
    public $account_id;
    public $account_branch_id;
    public $timeout = 1800; // 30 minutes, a generous timeout

    public function __construct(array $customer_data, int $account_id, int $account_branch_id)
    {
        $this->customer_data = $customer_data;
        $this->account_id = $account_id;
        $this->account_branch_id = $account_branch_id;
    }

    public function handle()
    {
        $account = Account::findOrFail($this->account_id);
        $connectionName = $this->setDatabaseConnection($account);

        // 2. Pre-fetch all salesmen needed for this import chunk in a single query.
        $salesmanCodes = array_column($this->customer_data, 'salesman');
        $salesmenByCode = Salesman::on($connectionName)
            ->whereIn('code', $salesmanCodes)
            ->where('account_id', $this->account_id)
            ->where('account_branch_id', $this->account_branch_id)
            ->get()
            ->keyBy('code');

        $customersToInsert = [];
        $now = Carbon::now();

        foreach ($this->customer_data as $data) {
            if ($data['check'] != 0) {
                continue;
            }

            // 3. Prepare data for a single bulk insert. No queries here!
            $customersToInsert[] = [
                'account_id'        => $this->account_id,
                'account_branch_id' => $this->account_branch_id,
                'salesman_id'       => $salesmenByCode[$data['salesman']]->id ?? null,
                'channel_id'        => $data['channel']['id'],
                'code'              => $data['code'],
                'name'              => $data['name'] ?? '-',
                'address'           => $data['address'] ?? '-',
                'street'            => $data['street'] ?? '-',
                'brgy'              => $data['brgy'],
                'city'              => $data['city'],
                'province'          => $data['province'],
                'postal_code'       => $data['postal_code'],
                'country'           => $data['country'],
                'status'            => $data['status'],
                'province_id'       => $data['province_id'],
                'municipality_id'   => $data['city_id'],
                'barangay_id'       => $data['brgy_id'],
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        if (empty($customersToInsert)) {
            return; // Nothing to do
        }

        // 4. Perform the bulk insert in one go.
        Customer::on($connectionName)->insert($customersToInsert);

        // 5. Get the IDs of the customers we just created to pass to the next job.
        // $customerCodes = array_column($customersToInsert, 'code');
        // $newCustomerIds = Customer::on($connectionName)
        //     ->whereIn('code', $customerCodes)
        //     ->where('account_id', $this->account_id)
        //     ->pluck('id')
        //     ->toArray();

        // // 6. Dispatch the expensive similarity check to a dedicated job.
        // // This keeps the import process fast and responsive.
        // if (!empty($newCustomerIds)) {
        //     ProcessCustomerUboJob::dispatch($newCustomerIds, $this->account_id, $this->account_branch_id);
        // }

        CustomerUboJob::dispatch($this->account_id, $this->account_branch_id);

        DB::setDefaultConnection(config('database.default'));
    }

    private function setDatabaseConnection(Account $account): string
    {
        $connectionName = 'tenant_' . $account->id;
        if (!config()->has('database.connections.' . $connectionName)) {
            config()->set('database.connections.' . $connectionName, [
                'driver' => 'mysql',
                'url' => NULL,
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => $account->db_data->database_name,
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'unix_socket' => '',
                'charset' => 'utf8',
                'collation' => 'utf8_general_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => 'InnoDB',
                'pool' => [
                    'min_connections' => 1,
                    'max_connections' => 10,
                    'max_idle_time' => 30,
                ],
            ]);
        }
        DB::setDefaultConnection($connectionName);
        return $connectionName;
    }
}
