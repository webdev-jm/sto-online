<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\SMSProduct;

use Illuminate\Support\Facades\DB;

class Vmi extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $account_branch;
    public $year, $month, $parameter;

    public function mount($account_branch) {
        $this->account_branch = $account_branch;

        $this->year = date('Y');
        $this->month = date('n');

        $this->parameter = 4;
    }

    private function csConversion($product_id, $uom, $quantity) {
        // UOM CONVERSION
        $uom = trim(strtoupper($uom));
        switch ($uom) {
            case 'CAS':
                $uom = 'CS';
                break;
            
            case 'CASE':
                $uom = 'CS';
                break;

            case 'PC':
                $uom = 'PCS';
                break;

            case 'PIECES':
                $uom = 'PCS';
                break;
        }

        $product = SMSProduct::find($product_id);
        if(!empty($product)) {
            $stock_uom = $product->stock_uom;
            $order_uom = $product->order_uom;
            $other_uom = $product->other_uom;

            if($uom == 'CS') {
                $quantity = $quantity;
            } else {
                if($stock_uom == 'CS') {
                    if($stock_uom == $uom) {
                        $quantity = $quantity;
                    } else if($order_uom == $uom) {
                        $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                    } else if($other_uom == $uom) {
                        $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                    }
                } elseif($order_uom == 'CS') {
                    if($stock_uom == $uom) {
                        $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                    } else if($order_uom == $uom) {
                        $quantity = $quantity;
                    } else if($other_uom == $uom) {
                        // convert to stock uom
                        $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                         // convert to order uom
                        $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = false);
                    }
                } elseif($other_uom == 'CS') {
                    if($stock_uom == $uom) {
                        $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                    } else if($order_uom == $uom) {
                        // convert to stock uom
                        $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                        // convert to other uom
                        $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = false);
                    } else if($other_uom == $uom) {
                        $quantity = $quantity;
                    }
                }
            }

            return $quantity;
        }

        return NULL;
    }

    private function quantityConversion($quantity, $conversion, $operator, $reverse = false) {
        if($operator == 'M') { // mutiply
            if($reverse) {
                return $quantity / $conversion;
            } else {
                return $quantity * $conversion;
            }
        } elseif($operator == 'D') { // divide
            if($reverse) {
                return $quantity * $conversion;
            } else {
                return $quantity / $conversion;
            }
        }

        return $quantity;
    }

    public function render()
    {

        DB::setDefaultConnection('sto_online_db');

        $inventories = DB::table('monthly_inventories as mi')
            ->select(
                'product_id',
                'uom',
                DB::raw('SUM(total) as total'),
            )
            ->where('account_id', 245)
            ->where('account_branch_id', 2)
            ->where('year', 2024)
            ->where('month', 3)
            ->where('total', '>', 0)
            ->groupBy('product_id', 'uom')
            ->paginate(10, ['*'], 'inventory-page');
            
        $data = array();
        foreach($inventories as $inventory) {
            if(!empty($inventory->total)) {
                $sales = DB::table('sales')
                    ->select(
                        'product_id',
                        'uom',
                        DB::raw('SUM(quantity) as total')
                    )
                    ->where('product_id', $inventory->product_id)
                    ->where('account_id', 245)
                    ->where('account_branch_id', 2)
                    ->where(DB::raw('MONTH(date)'), 2)
                    ->where(DB::raw('YEAR(date)'), 2024)
                    ->groupBy('product_id', 'uom')
                    ->first();
    
                $product = SMSProduct::find($inventory->product_id);
                $cs_total = $this->csConversion($inventory->product_id, $inventory->uom, $inventory->total);

                $w_cov = 0;
                if(!empty($cs_total) && !empty($sales->total)) {
                    $w_cov = $cs_total / $sales->total;
                }

                $w_cov_needed = 0;
                if(!empty($w_cov) && !empty($this->parameter)) {
                    $w_cov_needed = $this->parameter - $w_cov;
                }

                $vmi = 0;
                if(!empty($w_cov_needed) && !empty($sales->total)) {
                    $vmi = $w_cov_needed * $sales->total;
                }

                $data[] = [
                    'stock_code' => $product->stock_code,
                    'description' => $product->description.' '.$product->size,
                    'uom' => $inventory->uom,
                    'total' => $inventory->total,
                    'cs_total' => $cs_total,
                    'sto' => $sales->total ?? 0,
                    'w_cov' => $w_cov,
                    'w_cov_needed' => $w_cov_needed,
                    'vmi' => $vmi
                ];
            }
        }

        DB::setDefaultConnection('mysql');
        
        return view('livewire.reports.vmi')->with([
            'data' => $data,
            'inventories' => $inventories
        ]);
    }
}
