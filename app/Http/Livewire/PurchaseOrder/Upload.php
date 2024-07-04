<?php

namespace App\Http\Livewire\PurchaseOrder;

use Livewire\Component;
use Livewire\WithFileUploads;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\AccountProductReference;
use App\Models\SMSProduct;

use Spatie\SimpleExcel\SimpleExcelReader;

use Illuminate\Support\Facades\Session;

class Upload extends Component
{
    use WithFileUploads;

    public $account_branch;
    public $files;
    public $po_data;
    public $po_errors = array();

    public function uploadData() {
        if(!empty($this->po_data)) {
            foreach($this->po_data as $po_number => $data) {
                $err = $this->validateData($po_number, $data);
                if(empty($err)) {
                    $purchase_order = new PurchaseOrder([
                        'account_branch_id' => $this->account_branch->id,
                        'po_number' => $po_number,
                        'order_date' => $data['headers']['order_date'],
                        'ship_date' => $data['headers']['ship_date'],
                        'shipping_instruction' => $data['headers']['shipping_instruction'],
                        'ship_to_name' => $data['headers']['ship_to_name'],
                        'ship_to_address' => $data['headers']['ship_to_address'],
                    ]);
                    $purchase_order->save();
    
                    $total_quantity = 0;
                    $total_gross_amount = 0;
                    $total_net_amount = 0;
                    // products
                    foreach($data['products'] as $product_data) {
                        // lookup product by sku code
                        $product = SMSPRoduct::where('stock_code', $product_data['sku_code'])
                            ->orWhere('stock_code', $product_data['sku_code_other'])
                            ->first();
                        if(empty($product)) {
                            // check account product reference
                            $product_reference = AccountProductReference::where('account_id', $this->account_branch->account->sms_account_id)
                                ->where(function($query) use($product_data) {
                                    $query->where('account_reference', $product_data['sku_code'])
                                    ->orWhere('account_reference', $product_data['sku_code_other']);
                                })
                                ->first();
                            if(!empty($product_reference)) {
                                $product = SMSProduct::find($product_reference->product_id);
                            }
                        }
    
                        $purchase_order_detail = new PurchaseOrderDetail([
                            'purchase_order_id' => $purchase_order->id,
                            'product_id' => $product->id ?? NULL,
                            'sku_code' => $product_data['sku_code'],
                            'sku_code_other' => $product_data['sku_code_other'],
                            'product_name' => $product_data['product_name'],
                            'quantity' => $product_data['quantity'],
                            'unit_of_measure' => $product_data['unit_of_measure'],
                            'discount_amount' => $product_data['discount_amount'],
                            'gross_amount' => $product_data['gross_amount'],
                            'net_amount' => $product_data['net_amount'],
                            'net_amount_per_uom' => $product_data['net_amount_per_uom'],
                        ]);
                        $purchase_order_detail->save();
    
                        $total_quantity += $product_data['quantity'];
                        $total_gross_amount += $product_data['gross_amount'];
                        $total_net_amount += $product_data['net_amount_per_uom'];
                    }
    
                    $purchase_order->update([
                        'total_quantity' => $total_quantity,
                        'total_sales' => $total_gross_amount,
                        'grand_total' => $total_net_amount,
                    ]);

                    unset($this->po_data[$po_number]);
                    Session::put('po_upload_data', $this->po_data);
                } else {
                    $this->po_errors[$po_number] = $err;
                }
            }
        }
    }

    public function checkUploads()
    {
        $this->validate([
            'files' => [
                'required',
            ]
        ]);

        $this->po_data = array();
        foreach ($this->files as $file) {
            $path1 = $file->storeAs('purchase-order-uploads/account_branch_'.$this->account_branch->id, $file->getClientOriginalName());
            $path = storage_path('app').'/'.$path1;
            
            $rows = $rows = SimpleExcelReader::create($path)->getRows();

            $rows->each(function($row) use(&$po_data) {
                $po_number = $row['PO_NO'];
                $order_date = date('Y-m-d', strtotime($row['APPROVED_DATE']));
                $ship_date = date('Y-m-d', strtotime($row['DELIVERY_DATE']));
                $shipping_instruction = $row['TYPE_OF_ORDER'];
                $ship_to_name = $row['STORES'];
                $ship_to_address = $row['ADDREESS'];
                $status = $row['STATUS'];
                $total_quantity = 0;
                $total_amount = $row['TOTAL_GROSS_AMOUNT'];
                $total_net_amount = $row['TOTAL_NET_COST'];
                $po_value = $row['TOTAL_NET_COST'];
                
                $product_id = NULL;
                $sku_code = $row['SKU_CODE'];
                $sku_code_other = $row['UPC_CODE'];
                $product_name = $row['SKU_DESCRIPTION'];
                $quantity = $row['QTY_ORDERED'];
                $unit_of_measure = $row['BUYING_UOM'];
                $discount = $row['ADDL_DISC'];
                $discount_amount = $row['TOTAL_DISCOUNT_AMOUNT'];
                $gross_amount = $row['NET_COST_PER_UOM'];
                $net_amount = $row['NET_COST_PER_BUYING_UOM'];
                $net_amount_per_uom = $row['TOTAL_NET_COST'];
                $total_gross_amount = $row['TOTAL_GROSS_AMOUNT'];

                $po_data[$row['PO_NO']]['headers'] = [
                    'vendor' => $row['VENDOR'],
                    'order_date' => $order_date,
                    'ship_date' => $ship_date,
                    'shipping_instruction' => $shipping_instruction,
                    'ship_to_name' => $ship_to_name,
                    'ship_to_address' => $ship_to_address,
                    'city' => $row['CITY'],
                    'status' => $status,
                    'total_quantity' => $total_quantity,
                    'total_amount' => $total_amount,
                    'total_net_amount' => $total_net_amount,
                    'po_value' => $po_value,
                ];
                $po_data[$row['PO_NO']]['products'][] = [
                    'product_id' => $product_id,
                    'sku_code' => $sku_code,
                    'sku_code_other' => $sku_code_other,
                    'product_name' => $product_name,
                    'quantity' => $quantity,
                    'unit_of_measure' => $unit_of_measure,
                    'discount' => $discount,
                    'discount_amount' => $discount_amount,
                    'gross_amount' => $gross_amount,
                    'net_amount' => $net_amount,
                    'net_amount_per_uom' => $net_amount_per_uom,
                    'total_gross_amount' => $total_gross_amount
                ];

            });
        }

        $this->po_data = $po_data;
        
        Session::put('po_upload_data', $this->po_data);
    }

    public function validateData($po_number, $data) {
        $err = array();
        // PO NUMBER
        if(!empty($po_number)) {
            $check = PurchaseOrder::where('po_number', $po_number)->first();
            if(!empty($check)) {
                $err['po_number'] = 'PO Number already exists.';
            }
        } else {
            $err['po_number'] = 'PO Number is required.';
        }

        return $err;
    }

    public function mount($account_branch) {
        $this->account_branch = $account_branch;

        if(empty($this->po_data) && !empty(Session::get('po_upload_data'))) {
            $this->po_data = Session::get('po_upload_data');
        }
    }

    public function render()
    {
        return view('livewire.purchase-order.upload');
    }
}
