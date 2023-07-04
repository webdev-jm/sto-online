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
            
            // check
            $customer = Customer::where('account_id', $this->account_id)
                ->where('account_branch_id', $this->account_branch_id)
                ->where('code', $data['code'])
                ->where('name', $data['name'] ?? '-')
                ->first();
            if(empty($customer)) {
                $customer = new Customer([
                    'account_id' => $this->account_id,
                    'account_branch_id' => $this->account_branch_id,
                    'code' => $data['code'],
                    'name' => $data['name'] ?? '-',
                    'address' => $data['address'] ?? '-',
                ]);
                $customer->save();
            }

            // add salesman
            if(!empty($salesman) && $customer->salesman_id != $salesman->id) {
                // update previous salesan history record
                $salesman_customer = SalesmanCustomer::where('salesman_id', $customer->salesman_id)
                    ->where('customer_id', $customer->id)
                    ->first();
                
                if(!empty($salesman_customer)) {
                    $salesman_customer->update([
                        'end_date' => date('Y-m-d')
                    ]);
                }

                // update salesman
                $customer->update([
                    'salesman_id' => $salesman->id
                ]);

                // record new salesman history
                $salesman_customer = new SalesmanCustomer([
                    'salesman_id' => $salesman->id,
                    'customer_id' => $customer->id,
                    'start_date' => date('Y-m-d'),
                ]);
                $salesman_customer->save();
            }
            
        }

        // logs
        activity('upload')
        ->log(':causer.name has uploaded customer data.');
    }
}
