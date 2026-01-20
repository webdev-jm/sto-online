<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\SMSProduct;
use App\Models\MonthlyInventory;
use App\Models\Sale;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Vmi extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $account_branch;
    public $year, $month, $parameter, $month_param;
    public $search;

    public $months_arr = [
        1 => '1 MONTH',
        2 => '2 MONTHS',
        3 => '3 MONTHS',
    ];

    public function updatedSearch() {
        $this->resetPage('inventory-page');
    }

    public function updatedMonth() {
        if($this->month > 12) {
            $this->month = 12;
        } else if($this->month < 1) {
            $this->month = 1;
        }
    }

    public function mount($account_branch) {
        $this->account_branch = $account_branch;

        $this->year = date('Y');
        // $this->month = date('n');
         $this->month = 4;

        $this->parameter = 4;
        $this->month_param = 1;
    }

    private function csConversion($product, $uom, $quantity) {
        // Normalize UOM to standard codes
        $uom = trim(strtoupper($uom));
        $uomMapping = [
            'CAS' => 'CS',
            'CASE' => 'CS',
            'PC' => 'PCS',
            'PIECES' => 'PCS',
            'IB' => 'IN',
            'PAC' => 'PCK'
        ];
        $uom = $uomMapping[$uom] ?? $uom;

        // Return null if the product is empty
        if (empty($product)) {
            return NULL;
        }

        // Define the UOMs
        $stock_uom = $product->stock_uom;
        $order_uom = $product->order_uom;
        $other_uom = $product->other_uom;

        // Direct return if already in 'CS'
        if ($uom == 'CS') {
            return $quantity;
        }

        // Conversion logic based on UOM
        if ($stock_uom == 'CS') {
            if ($uom == $order_uom) {
                $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, true);
            } elseif ($uom == $other_uom) {
                $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, true);
            }
        } elseif ($order_uom == 'CS') {
            if ($uom == $stock_uom) {
                $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, true);
            } elseif ($uom == $other_uom) {
                $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, true);
                $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, false);
            }
        } elseif ($other_uom == 'CS') {
            if ($uom == $stock_uom) {
                $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, true);
            } elseif ($uom == $order_uom) {
                $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, true);
                $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, false);
            }
        }

        return $quantity;
    }

    private function quantityConversion($quantity, $conversion, $operator, $reverse = false) {
        if ($operator == 'M') { // multiply
            return $reverse ? $quantity / $conversion : $quantity * $conversion;
        } elseif ($operator == 'D') { // divide
            return $reverse ? $quantity * $conversion : $quantity / $conversion;
        }
        return $quantity;
    }

    public function render()
    {

        $curr_date = new Carbon($this->year.'-'.$this->month.'-01');
        $prev_date1 = $curr_date->startOfMonth()->subMonth();
        $curr_date = new Carbon($this->year.'-'.$this->month.'-01');
        $prev_date2 = $curr_date->startOfMonth()->subMonth(2);
        $curr_date = new Carbon($this->year.'-'.$this->month.'-01');
        $prev_date3 = $curr_date->startOfMonth()->subMonth(3);

        $inventories = MonthlyInventory::select(
                'product_id',
                'uom',
                DB::raw('SUM(total) as total'),
            )
            ->where('account_id', $this->account_branch->account_id)
            ->where('account_branch_id', $this->account_branch->id)
            ->where('year', $prev_date1->year)
            ->where('month', $prev_date1->month)
            ->where('total', '>', 0)
            ->when(!empty($this->search), function($query) {
                $query->whereExists(function($qry) {
                    $qry->select(DB::raw(1))
                        ->from(env('DB_DATABASE_2').'.products')
                        ->whereColumn('products.id', 'monthly_inventories.product_id')
                        ->where(function($qry1) {
                            $qry1->where('stock_code', 'like', '%'.$this->search.'%')
                                ->orWhere('description', 'like', '%'.$this->search.'%')
                                ->orWhere('size', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->groupBy('product_id', 'uom')
            ->paginate(20, ['*'], 'inventory-page');

        $product_ids = $inventories->pluck('product_id')->toArray();
        $products = SMSProduct::whereIn('id', $product_ids)->get()->keyBy('id');

        // $months_param = [1, 2, 3];
        // $param_data = array();
        // foreach($months_param as $param) {
        //     $sales = Sale::select(
        //             'product_id',
        //             DB::raw('CASE WHEN TRIM(uom) = "CAS" THEN "CS" ELSE TRIM(uom) END as uom'),
        //             DB::raw('SUM(quantity) / '.$param.' as total')
        //         )
        //         ->whereIn('product_id', $product_ids)
        //         ->where('account_id', $this->account_branch->account_id)
        //         ->where('account_branch_id', $this->account_branch->id)
        //         ->where(function($query) use($prev_date1, $prev_date2, $prev_date3, $param) {
        //             for($i = 1; $i <= $param; $i++) {
        //                 $query->orWhere(function($qry) use($prev_date1, $prev_date2, $prev_date3, $i) {
        //                     $qry->where(DB::raw('MONTH(date)'), ${'prev_date'.$i}->month)
        //                         ->where(DB::raw('YEAR(date)'), ${'prev_date'.$i}->year);
        //                 });
        //             }
        //         })
        //         ->groupBy('product_id', 'uom')
        //         ->get()
        //         ->groupBy('product_id');

        //     $data = array();
        //     foreach($inventories as $inventory) {
        //         if(!empty($inventory->total)) {
        //             $product = $products->get($inventory->product_id);
        //             $cs_total = $this->csConversion($product, $inventory->uom, $inventory->total);

        //             $sales_data = $sales->get($inventory->product_id);

        //             $sales_cs_total = 0;
        //             if(!empty($sales_data)) {
        //                 foreach($sales_data as $val) {
        //                     $sales_cs_total += $this->csConversion($product, $val->uom, $val->total);
        //                 }
        //             }

        //             $w_cov = 0;
        //             if(!empty($cs_total) && !empty($sales_cs_total)) {
        //                 $w_cov = $cs_total / $sales_cs_total;
        //             }

        //             $w_cov_needed = 0;
        //             if(!empty($w_cov) && !empty($this->parameter)) {
        //                 $w_cov_needed = $this->parameter - $w_cov;
        //             }

        //             $vmi = 0;
        //             if(!empty($w_cov_needed) && !empty($sales_cs_total)) {
        //                 $vmi = $w_cov_needed * $sales_cs_total;
        //             }

        //             $data[] = [
        //                 'stock_code' => $product->stock_code,
        //                 'description' => $product->description.' '.$product->size,
        //                 'uom' => $inventory->uom,
        //                 'total' => $inventory->total,
        //                 'cs_total' => $cs_total,
        //                 'sto' => $sales_cs_total ?? 0,
        //                 'w_cov' => $w_cov,
        //                 'w_cov_needed' => $w_cov_needed,
        //                 'vmi' => $vmi,
        //             ];
        //         }
        //     }

        //     $param_data[$param] = $data;
        // }

        $data = array();
        foreach($inventories as $inventory) {
            if(!empty($inventory->total)) {
                $product = $products->get($inventory->product_id);
                $cs_total = $this->csConversion($product, $inventory->uom, $inventory->total);

                $data[$inventory->product_id] = [
                    'stock_code' => $product->stock_code,
                    'description' => $product->description.' '.$product->size,
                    'uom' => $inventory->uom,
                    'total' => $inventory->total,
                    'cs_total' => $cs_total,
                ];

                $months_param = [1, 2, 3];
                $months_data = array();
                foreach($months_param as $param) {
                    $sales = Sale::select(
                            'product_id',
                            DB::raw('CASE WHEN TRIM(uom) = "CAS" THEN "CS" ELSE TRIM(uom) END as uom'),
                            DB::raw('SUM(quantity) / '.$param.' as total')
                        )
                        ->where('product_id', $inventory->product_id)
                        ->where('account_id', $this->account_branch->account_id)
                        ->where('account_branch_id', $this->account_branch->id)
                        ->where(function($query) use($prev_date1, $prev_date2, $prev_date3, $param) {
                            for($i = 1; $i <= $param; $i++) {
                                $query->orWhere(function($qry) use($prev_date1, $prev_date2, $prev_date3, $i) {
                                    $qry->where(DB::raw('MONTH(date)'), ${'prev_date'.$i}->month)
                                        ->where(DB::raw('YEAR(date)'), ${'prev_date'.$i}->year);
                                });
                            }
                        })
                        ->groupBy('product_id', 'uom')
                        ->get()
                        ->groupBy('product_id');

                    $sales_data = $sales->get($inventory->product_id);

                    $sales_cs_total = 0;
                    if(!empty($sales_data)) {
                        foreach($sales_data as $val) {
                            $sales_cs_total += $this->csConversion($product, $val->uom, $val->total);
                        }
                    }

                    $w_cov = 0;
                    if(!empty($cs_total) && !empty($sales_cs_total)) {
                        $w_cov = $cs_total / $sales_cs_total;
                    }

                    $w_cov_needed = 0;
                    if(!empty($w_cov) && !empty($this->parameter)) {
                        $w_cov_needed = $this->parameter - $w_cov;
                    }

                    $vmi = 0;
                    if(!empty($w_cov_needed) && !empty($sales_cs_total)) {
                        $vmi = $w_cov_needed * $sales_cs_total;
                    }

                    $months_data[$param] = [
                        'sto' => $sales_cs_total ?? 0,
                        'w_cov' => $w_cov,
                        'w_cov_needed' => $w_cov_needed,
                        'vmi' => $vmi,
                    ];
                }

                $data[$inventory->product_id]['months_data'] = $months_data;
            }

        }

        return view('livewire.reports.vmi')->with([
            'data' => $data,
            'inventories' => $inventories
        ]);
    }
}
