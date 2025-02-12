<?php

namespace App\Http\Livewire\PurchaseOrder;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\PurchaseOrder;
use App\Models\SMSAccount;
use App\Models\SMSProduct;
use App\Models\SMSPriceCode;

use App\Http\Traits\PriceCodeTrait;

class Create extends Component
{
    use PriceCodeTrait;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $account;
    public $sms_account;
    public $control_number;
    public $order_data;
    public $order_details;

    public $brand_filter = 'ALL', $search;

    private function generatePoNumber() {
        $control_number = 'PO-20250111-0001';

        $date_code = date('Ymd', time());
        $control_number = 'PO-'.$date_code.'-0001';

        $purchase_order = PurchaseOrder::withTrashed()
            ->where('control_number', 'LIKE', 'PO-' . $date_code . '-%')
            ->orderBy('control_number', 'DESC')
            ->first();

        if(!empty($purchase_order)) {
            // Extract the last number
            $control_number_arr = explode('-', $purchase_order->control_number);
            $last_number = (int) end($control_number_arr) + 1; // Increment last number

            // Ensure the number is always 4 digits (e.g., 001, 002, ...)
            $new_number = str_pad($last_number, 4, '0', STR_PAD_LEFT);

            $control_number = 'PO-' . $date_code . '-' . $new_number;
        }

        // Ensure uniqueness before finalizing
        while (PurchaseOrder::withTrashed()->where('control_number', $control_number)->exists()) {
            $last_number++;
            $new_number = str_pad($last_number, 3, '0', STR_PAD_LEFT);
            $control_number = 'PO-' . $date_code . '-' . $new_number;
        }

        $this->control_number = $control_number;
    }

    public function mount($account) {
        $this->account = $account;
        $this->sms_account = SMSAccount::find($account->sms_account_id);
    }

    public function getOrders() {
        $order_details = [];
        $total_quantity = 0;
        $total_price = 0;
        if(!empty($this->order_data)) {
            foreach($this->order_data as $product_id => $data) {
                if(!empty($data['order'])) {
                    $product = SMSPRoduct::find($product_id);

                    $uom = $product->order_uom;
                    if(!empty($data['uom'])) {
                        $uom = $data['uom'];
                    }
    
                    // getProductPrice(account_code, company name, stock code, quantity, UOM, discount)
                    $price = $this->getProductPrice($this->sms_account->account_code, $this->sms_account->company->name, $product->stock_code, $data['order'], $uom, false);

                    $total_quantity += $data['order'];
                    $total_price += $price;
    
                    $order_details[] = [
                        'product' => $product,
                        'uom' => $uom,
                        'quantity' => $data['order'],
                        'price' => $price
                    ];
                }
            }
        }

        $this->order_details['details'] = $order_details;
        $this->order_details['total_quantity'] = $total_quantity;
        $this->order_details['total_price'] = $total_price;
    }

    public function updatedOrderData() {
        $this->getOrders();
    }

    public function updatedSearch() {
        $this->resetPage('product-page');
    }

    public function render()
    {
        $this->generatePoNumber();

        $special_products = $this->sms_account->products;

        $query = SMSProduct::query();
        
        // Apply search filters
        $query->when(!empty($this->search), function($qry) {
            $qry->where(function ($q) {
                $q->where('stock_code', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%")
                  ->orWhere('category', 'like', "%{$this->search}%")
                  ->orWhere('size', 'like', "%{$this->search}%")
                  ->orWhere('stock_uom', 'like', "%{$this->search}%")
                  ->orWhere('order_uom', 'like', "%{$this->search}%")
                  ->orWhere('other_uom', 'like', "%{$this->search}%")
                  ->orWhere('brand', 'like', "%{$this->search}%")
                  ->orWhereHas('references', function ($qry) {
                      $qry->where('account_reference', 'like', "%{$this->search}%")
                          ->orWhere('description', 'like', "%{$this->search}%");
                  });
            });
        });
        
        $query->whereHas('price_codes', function ($q) {
            $q->where('company_id', $this->sms_account->company_id)
                ->where('code', $this->sms_account->price_code);
        });
        
        if ($special_products->isNotEmpty()) {
            $query->where(function ($q) use ($special_products) {
                $q->where('special_product', 0)
                    ->orWhere(function ($qry) use ($special_products) {
                        $qry->where('special_product', 1)
                            ->whereIn('id', $special_products->pluck('id'));
                    });
            });
        } else {
            $query->where('special_product', 0);
        }
        
        // Apply brand filter
        if ($this->brand_filter !== 'ALL') {
            $query->where('brand', $this->brand_filter);
        }

        // dd($query->toSql(), $query->getBindings());
        
        // Pagination
        $products = $query->paginate(15, ['*'], 'product-page')->onEachSide(1);

        // Fetch brands
        $brands = SMSProduct::select('brand')->distinct()->orderBy('brand', 'ASC')
            ->whereHas('price_codes', function ($query) {
                $query->where('company_id', $this->sms_account->company_id)
                      ->where('code', $this->sms_account->price_code);
            })
            ->when(!empty($this->search), function($q) {
                $q->where('stock_code', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%")
                  ->orWhere('category', 'like', "%{$this->search}%")
                  ->orWhere('size', 'like', "%{$this->search}%")
                  ->orWhere('stock_uom', 'like', "%{$this->search}%")
                  ->orWhere('order_uom', 'like', "%{$this->search}%")
                  ->orWhere('other_uom', 'like', "%{$this->search}%")
                  ->orWhere('brand', 'like', "%{$this->search}%")
                  ->orWhereHas('references', function ($qry) {
                      $qry->where('account_reference', 'like', "%{$this->search}%")
                          ->orWhere('description', 'like', "%{$this->search}%");
                  });
            })
            ->get('brand');

        return view('livewire.purchase-order.create')->with([
            'brands' => $brands,
            'products' => $products
        ]);
    }
}
