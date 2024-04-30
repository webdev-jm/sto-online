<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\DB;

use App\Models\Customer;
use App\Models\CustomerUbo;
use App\Models\CustomerUboDetail;
use App\Models\Account;

class CustomerUboJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $account_id;
    public $account_branch_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($account_id, $account_branch_id)
    {
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
        \Config::set('database.connections.'.$account->db_data->connection_name, [
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

        DB::setDefaultConnection($account->db_data->connection_name);

        // Get customers with eager-loaded relationships
        $customers = Customer::where('account_id', $this->account_id)
            ->where('account_branch_id', $this->account_branch_id)
            ->doesntHave('ubo')
            ->doesntHave('ubo_detail')
            ->get();

        foreach($customers as $customer) {
            if(empty($customer->ubo->count())) { // Check if UBO does not exist
                // Find potential duplicates with high similarity
                $potential_duplicate = CustomerUbo::whereRaw('CalculateLevenshteinSimilarity(name, ?) >= 90', [$customer->name])
                    ->whereRaw('CalculateLevenshteinSimilarity(address, ?) >= 90', [$customer->address])
                    ->where('customer_id', '<>', $customer->id)
                    ->where('account_id', $customer->account_id)
                    ->where('account_branch_id', $customer->account_branch_id)
                    ->first();

                if(!empty($potential_duplicate)) {
                    $ubo_id = $potential_duplicate->ubo_id;
                    
                    $percent = $this->checkSimilarity($potential_duplicate->name, $customer->name);
                    $address_pc = $this->checkSimilarity($potential_duplicate->address, $customer->address);

                    CustomerUboDetail::updateOrInsert(
                        [
                            'account_id' => $customer->account_id,
                            'account_branch_id' => $customer->account_branch_id,
                            'customer_ubo_id' => $potential_duplicate->id,
                            'customer_id' => $customer->id,
                            'ubo_id' => $ubo_id,
                        ],
                        [
                            'name' => $customer->name,
                            'address' => $customer->address,
                            'similarity' => $percent,
                            'address_similarity' => $address_pc,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );

                    $customer->update([
                        'status' => 1
                    ]);

                    $customer->sales()->update([
                        'status' => 1
                    ]);
                } else {
                    // Insert and assign UBO ID for similar customers
                    $last_ubo_id = CustomerUbo::max('ubo_id');
                    $ubo_id = $last_ubo_id ? $last_ubo_id + 1 : 1;

                    // Create a new UBO
                    CustomerUbo::updateOrInsert([
                            'account_id' => $customer->account_id,
                            'account_branch_id' => $customer->account_branch_id,
                            'customer_id' => $customer->id,
                        ],
                        [
                            'ubo_id' => $ubo_id ?? 1,
                            'name' => $customer->name,
                            'address' => $customer->address,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );

                    $customer->update([
                        'status' => 0
                    ]);

                    $customer->sales()->update([
                        'status' => 0
                    ]);
                }
            }
        }

        DB::setDefaultConnection('mysql');
    }

    private function checkSimilarity($str1, $str2) {
        // remove spaces before comparing
        $str1 = str_replace(' ', '', $str1);
        $str2 = str_replace(' ', '', $str2);

        // Calculate Levenshtein distance
        $distance = levenshtein(strtoupper($str1), strtoupper($str2));

        // Calculate maximum length
        $max_length = max(strlen($str1), strlen($str2));

        // Check if the maximum length is zero to avoid division by zero
        if ($max_length == 0) {
            return 0; // or any other appropriate value
        }

        // Calculate similarity percentage
        $similarity = 1 - ($distance / $max_length);
        $similarity = $similarity * 100;

        return $similarity;
    }
}
