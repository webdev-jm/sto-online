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
        // It's generally better to configure these in php.ini or through your queue worker's config.
        // However, for large imports, setting them here is acceptable if not configured globally.
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
        ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

        $account = Account::findOrFail($this->account_id);
        $connectionName = 'tenant_' . $account->id; // Use a unique connection name

        // Dynamically set up the database connection for this job instance
        config()->set('database.connections.' . $connectionName, [
            'driver' => 'mysql',
            'host' => $account->db_data->host ?? '127.0.0.1', // Use account's DB host
            'port' => $account->db_data->port ?? 3306,
            'database' => $account->db_data->database_name,
            'username' => $account->db_data->username ?? env('DB_USERNAME'), // Use account's DB username
            'password' => $account->db_data->password ?? env('DB_PASSWORD'), // Use account's DB password
            'charset' => 'utf8mb4', // Use utf8mb4 for broader character support
            'collation' => 'utf8mb4_unicode_ci',
            'strict' => true, // Keep strict mode
        ]);

        // Use a transaction to ensure atomicity for the entire import process
        DB::connection($connectionName)->transaction(function () use ($connectionName) {

            if(!empty($this->sales_data)) {

                // $upload = SalesUpload::on($connectionName)->find($this->upload_id);
                $upload = DB::connection($connectionName)
                    ->table('sales_uploads')
                    ->where('id', $this->upload_id)
                    ->first();

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

                $salesToInsert = []; // Array to collect sales data for bulk insert

                $report_data = array();

                foreach($this->sales_data as $data) {
                    // check data
                    if($data['check'] == 0) { // no error
                        $sku_count++;


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

                        $salesToInsert[] = [
                            'sales_upload_id' => $upload->id,
                            'account_id' => $this->account_id,
                            'account_branch_id' => $this->account_branch_id,
                            'customer_id' => $data['customer_id'],
                            'channel_id' => $data['channel_id'],
                            'product_id' => $data['product_id'],
                            'salesman_id' => $data['salesman_id'] ?? null, // Handle potential null salesman_id
                            'location_id' => $data['location_id'],
                            'user_id' => $this->user_id,
                            'type' => $data['type'],
                            'date' => \Carbon\Carbon::parse($data['date'])->format('Y-m-d'), // Use Carbon for robust date parsing
                            'document_number' => $data['document'],
                            'category' => $data['category'],
                            'uom' => $data['uom'],
                            'quantity' => $data['quantity'],
                            'price_inc_vat' => $data['price_inc_vat'],
                            'amount' => $data['amount'],
                            'amount_inc_vat' => $data['amount_inc_vat'], // Ensure this is numeric
                            'status' => $data['status'],
                        ];

                        $report_data[date('Y', strtotime($data['date']))][date('n', strtotime($data['date']))] = date('Y-m-d', strtotime($data['date']));
                    }
                }

                DB::connection($connectionName)->statement("
                    UPDATE sales_uploads
                    SET
                        sku_count = ?,
                        total_quantity = ?,
                        total_price_vat = ?,
                        total_amount = ?,
                        total_amount_vat = ?,
                        total_cm_quantity = ?,
                        total_cm_price_vat = ?,
                        total_cm_amount = ?,
                        total_cm_amount_vat = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ", [
                    $sku_count,
                    $total_quantity,
                    $total_price_vat,
                    $total_amount,
                    $total_amount_vat,
                    $total_cm_quantity,
                    $total_cm_price_vat,
                    $total_cm_amount,
                    $total_cm_amount_vat,
                    $upload->id // The unique identifier from your original $upload object
                ]);

                // Perform bulk insert for all valid sales records
                if (!empty($salesToInsert)) {
                    // Sale::on($connectionName)->insert($salesToInsert);
                    DB::connection($connectionName)
                        ->table('sales')
                        ->insert($salesToInsert);
                }

                // UPDATE SALES REPORTS
                if(!empty($report_data)) {
                    foreach($report_data as $year => $months) {
                        foreach($months as $month => $date) {
                            DB::connection($connectionName)->statement('CALL generate_sales_report(?, ?, ?, ?)', [$this->account_id, $this->account_branch_id, $year, $month]);
                        }
                    }
                }


            }

        }); // End of transaction

        // logs
        activity('upload')
            ->log(':causer.name has uploaded sales data.');
    }
}
