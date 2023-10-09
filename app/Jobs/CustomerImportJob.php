<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Salesman;
use App\Models\Customer;
use App\Models\CustomerUbo;
use App\Models\CustomerUboDetail;
use App\Models\SalesmanCustomer;

class CustomerImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $customer_data;
    public $account_id;
    public $account_branch_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($customer_data, $account_id, $account_branch_id)
    {
        $this->customer_data = $customer_data;
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
        foreach($this->customer_data as $data) {
            // get salesman
            $salesman = Salesman::where('account_id', $this->account_id)
                ->where('account_branch_id', $this->account_branch_id)
                ->where('code', $data['salesman'])
                ->first();
            
            if($data['check'] == 0) {
                $customer = new Customer([
                    'account_id' => $this->account_id,
                    'account_branch_id' => $this->account_branch_id,
                    'code' => $data['code'],
                    'name' => $data['name'] ?? '-',
                    'address' => $data['address'] ?? '-',
                    'brgy' => $data['brgy'],
                    'city' => $data['city'],
                    'province' => $data['province'],
                    'country' => $data['country'],
                    'status' => $data['status'],
                ]);
                $customer->save();

                $this->checkCustomerSimilarity($customer);
            }

            // add salesman
            if(!empty($salesman) && !empty($customer) && $customer->salesman_id != $salesman->id) {
                // update previous salesan history record
                $salesman_customer = SalesmanCustomer::where('salesman_id', $customer->salesman_id)
                    ->where('customer_id', $customer->id)
                    ->first();
                
                if(!empty($salesman_customer)) {
                    $salesman_customer->update([
                        'end_date' => date('Y-m-d')
                    ]);
                } else {
                    // record new salesman history
                    $salesman_customer = new SalesmanCustomer([
                        'salesman_id' => $salesman->id,
                        'customer_id' => $customer->id,
                        'start_date' => date('Y-m-d'),
                    ]);
                    $salesman_customer->save();
                }

                // update salesman
                $customer->update([
                    'salesman_id' => $salesman->id
                ]);
                
            }
        }
    }

    private function checkCustomerSimilarity($customer) {
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
                    ]
                );

                $customer->update([
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
                    ]
                );

                $customer->update([
                    'status' => 0
                ]);
            }
        }
    }

    private function checkSimilarity($str1, $str2) {
        // remove spaces before comparing
        $str1 = str_replace(' ', '', $str1);
        $str2 = str_replace(' ', '', $str2);

        $distance = levenshtein(strtoupper($str1), strtoupper($str2));
        $max_length = max(strlen($str1), strlen($str2));
        $similarity = 1 - ($distance / $max_length);
        $similarity = $similarity * 100;

        return $similarity;
    }
}
