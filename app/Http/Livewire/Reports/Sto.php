<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;

use App\Models\Account;
use App\Models\StockOnHand;
use App\Models\SMSProduct;

use Illuminate\Support\Facades\DB;

class Sto extends Component
{
    public $report;
    public $account_data;
    public $channel_data;
    public $brand_data;
    public $ubo_data;

    public $drilldown_data;
    public $selected_key;
    public $selected_type;

    public $year, $month;

    public function selectReport($report) {
        $this->report = $report;
        $this->loadReport();
    }

    public function selectDate($month) {
        $this->month = $month;
        if($month < 1) {
            $this->year = $this->year - 1;
            $this->month = 12;
        }
        if($month > 12) {
            $this->year = $this->year + 1;
            $this->month = 1;
        }

        $this->loadReport();
        $this->loadDrillDown($this->selected_type, $this->selected_key, $reload = true);
    }

    public function loadReport() {
        $accounts = Account::all();

        switch($this->report) {
            case 'account':
                foreach($accounts as $account) {
                    DB::setDefaultConnection($account->db_data->connection_name);

                    $total_inv = DB::table('stock_on_hands')
                        ->where('month', $this->month)
                        ->where('year', $this->year)
                        ->sum('total_inventory');
                    $this->account_data[$account->account_code] = [
                        'account_id' => $account->id,
                        'short_name' => $account->short_name,
                        'account_name' => $account->account_name,
                        'total_inventory' => $total_inv
                    ];
                }
            break;

            case 'channel':

            break;

            case 'brand':

                $this->brand_data = array();
                $data = array();
                foreach($accounts as $account) {
                    DB::setDefaultConnection($account->db_data->connection_name);

                    $products = DB::table('stock_on_hand_products as sohp')
                        ->select(
                            DB::raw('SUM(inventory) as total'),
                            'p.brand',
                        )
                        ->leftJoin('stock_on_hands as soh', 'soh.id', '=', 'sohp.stock_on_hand_id')
                        ->leftJoin(DB::connection('sms_db')->getDatabaseName().'.products as p', 'p.id', '=', 'sohp.product_id')
                        ->where('soh.month', $this->month)
                        ->where('soh.year', $this->year)
                        ->groupBy('p.brand')
                        ->get();

                    foreach($products as $product) {
                        $this->brand_data[$product->brand] = [
                            'total' => $product->total
                        ];

                        $data[] = [
                            'name' => $product->brand,
                            'y' => $product->total
                        ];
                    }
                }

                $this->dispatchBrowserEvent('update-chart', [
                    'data' => $data
                ]);

            break;

            case 'ubo':

                foreach($accounts as $account) {
                    DB::setDefaultConnection($account->db_data->connection_name);

                    $ubo = DB::table('stock_on_hands')
                        ->select(DB::raw('COUNT(DISTINCT(customer_id)) as ubo'))
                        ->where('month', $this->month)
                        ->where('year', $this->year)
                        ->first();

                    $this->ubo_data[$account->account_code] = [
                        'short_name' => $account->short_name,
                        'account_name' => $account->account_name,
                        'ubo' => $ubo->ubo
                    ];
                }

            break;
        }

        DB::setDefaultConnection('mysql');
    }

    public function loadDrillDown($type, $key_id, $reload = false) {

        $this->selected_key = $key_id;
        $this->selected_type = $type;

        if(!$reload) {
            if(!empty($this->drilldown_data['status']) && $this->drilldown_data['status'] == 'open') {
                $this->drilldown_data[$type]['status'] = 'closed';
            } else {
                $this->drilldown_data[$type]['status'] = 'open';
            }
        }

        switch($type) {
            case 'account':
                $account = Account::find($key_id);
                $this->drilldown_data[$type]['account'] = $account;

                DB::setDefaultConnection($account->db_data->connection_name);

                $products = DB::table('stock_on_hand_products as sohp')
                    ->select(
                        DB::raw('SUM(inventory) as total'),
                        'p.brand',
                    )
                    ->leftJoin('stock_on_hands as soh', 'soh.id', '=', 'sohp.stock_on_hand_id')
                    ->leftJoin(DB::connection('sms_db')->getDatabaseName().'.products as p', 'p.id', '=', 'sohp.product_id')
                    ->where('soh.month', $this->month)
                    ->where('soh.year', $this->year)
                    ->groupBy('p.brand')
                    ->get();
                
                $this->drilldown_data[$type]['data'] = array();
                foreach($products as $product) {
                    $this->drilldown_data[$type]['data'][$product->brand] = [
                        'total' => $product->total
                    ];

                    $data[] = [
                        'name' => $product->brand,
                        'y' => $product->total
                    ];
                }

                DB::setDefaultConnection('mysql');
            break;
        }
    }

    public function closeDrillDown($type) {
        $this->drilldown_data[$type]['status'] = 'closed';
    }

    public function mount() {
        $this->year = date('Y');
        $this->month = (int)date('m');
    }

    public function render()
    {
        return view('livewire.reports.sto');
    }
}
