<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\SalesUpload;
use App\Models\Sale;
use App\Models\Account;

use Illuminate\Support\Facades\DB;

class SalesImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $sales_data;
    public $account_id;
    public $account_branch_id;
    public $user_id;
    public $upload_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sales_data, $account_id, $account_branch_id, $user_id, $upload_id)
    {
        $this->sales_data = $sales_data;
        $this->account_id = $account_id;
        $this->account_branch_id = $account_branch_id;
        $this->user_id = $user_id;
        $this->upload_id = $upload_id;
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
            'username' => 'root',
            'password' => '',
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

        if(!empty($this->sales_data)) {

            $upload = SalesUpload::find($this->upload_id);

            $sku_count = 0;

            $total_quantity = 0;
            $total_price_vat = 0;
            $total_amount = 0;
            $total_amount_vat = 0;

            $total_cm_quantity = 0;
            $total_cm_price_vat = 0;
            $total_cm_amount = 0;
            $total_cm_amount_vat = 0;

            $num = 0;

            $report_data = array();

            foreach($this->sales_data as $data) {
                // check data
                if($data['check'] == 0) { // no error
                    $sku_count++;

                    // check if not FG or PROMO
                    if($data['type'] == 1) {
                        if($data['category'] == 1) { // Credit Memo
                            $total_cm_quantity += $data['quantity'];
                            $total_cm_price_vat += $data['price_inc_vat'];
                            $total_cm_amount += $data['amount'];
                            $total_cm_amount_vat += $data['amount_inc_vat'];
                        } else { // Invoice
                            $total_quantity += $data['quantity'];
                            $total_price_vat += $data['price_inc_vat'];
                            $total_amount += $data['amount'];
                            $total_amount_vat += $data['amount_inc_vat'];
                        }
                    }

                    $sale = new Sale([
                        'sales_upload_id' => $upload->id,
                        'account_id' => $this->account_id,
                        'account_branch_id' => $this->account_branch_id,
                        'customer_id' => $data['customer_id'],
                        'channel_id' => $data['channel_id'],
                        'product_id' => $data['product_id'],
                        'salesman_id' => $data['salesman_id'],
                        'location_id' => $data['location_id'],
                        'user_id' => $this->user_id,
                        'type' => $data['type'],
                        'date' => date('Y-m-d', strtotime($data['date'])),
                        'document_number' => $data['document'],
                        'category' => $data['category'],
                        'uom' => $data['uom'],
                        'quantity' => $data['quantity'],
                        'price_inc_vat' => $data['price_inc_vat'],
                        'amount' => $data['amount'],
                        'amount_inc_vat' => $data['amount_inc_vat'],
                        'status' => $data['status'],
                    ]);
                    $sale->save();

                    $report_data[date('Y', strtotime($data['date']))][date('n', strtotime($data['date']))] = date('Y-m-d', strtotime($data['date']));
                }
            }

            $upload->update([
                'sku_count' => $sku_count,
                'total_quantity' => $total_quantity,
                'total_price_vat' => $total_price_vat,
                'total_amount' => $total_amount,
                'total_amount_vat' => $total_amount_vat,
                'total_cm_quantity' => $total_cm_quantity,
                'total_cm_price_vat' => $total_cm_price_vat,
                'total_cm_amount' => $total_cm_amount,
                'total_cm_amount_vat' => $total_cm_amount_vat,
            ]);

            DB::setDefaultConnection('mysql');

            // logs
            activity('upload')
                ->performedOn($upload)
                ->log(':causer.name has uploaded sales data.');

            // UPDATE SALES REPORTS
            // if(!empty($report_data)) {
            //     foreach($report_data as $year => $months) {
            //         foreach($months as $month => $date) {
            //             DB::statement('CALL generate_sales_report(?, ?, ?, ?)', [$this->account_id, $this->account_branch_id, $year, $month]);
            //         }
            //     }
            // }
        }
        
    }
}
