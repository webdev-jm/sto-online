<?php

namespace App\Http\Traits;

use App\Models\SMSAccount;
use App\Models\SMSCompany;
use App\Models\SMSProduct;
use App\Models\SMSPriceCode;

use Illuminate\Support\Facades\Session;

Trait PriceCodeTrait {
    
    // get product price base on account and Unit of Measurement(UOM)
    public function getProductPrice($account_code, $vendor, $sku_code, $quantity, $uom = 'PCS', $with_discount = true) {
        // company
        $company = SMSCompany::where('name', $vendor)->first();
        // account
        $account = SMSAccount::where('account_code', $account_code)->first();
        // discount
        $discount = NULL;
        if($with_discount) {
            $discount = $account->discount ?? NULL;
        }
        // product
        $product = SMSProduct::where('stock_code', $sku_code)->first();

        $err_msg = Session::get('price_code_err');
        if(empty($err_msg)) {
            $err_msg = [];
        }

        if(!empty($company) && !empty($account) && !empty($product)) {

            // get price code account and product
            $price_code = SMSPriceCode::where('company_id', $company->id)
            ->where('product_id', $product->id)
            ->where('code', $account->price_code)
            ->first();

            $selling_price = 0;
            if(!empty($price_code)) {
                // COMPUTE VALUE BASE ON UOM
                $selling_price = $price_code->selling_price;
                $price_basis = $price_code->price_basis;
        
                // convert selling price to stock uom price
                if($price_basis == 'A') { // ORDER UOM
                    if($product->order_uom_operator == 'M') { // MULTIPLY
                        $selling_price = $selling_price / $product->order_uom_conversion;
                    }
                    if($product->order_uom_operator == 'D') { // DIVIDE
                        $selling_price = $selling_price * $product->order_uom_conversion;
                    }
                } else if($price_basis == 'O') { // OTHER UOM
                    if($product->other_uom_operator == 'M') { // MULTIPLY
                        $selling_price = $selling_price / $product->other_uom_conversion;
                    }
                    if($product->other_uom_operator == 'D') { // DIVIDE
                        $selling_price = $selling_price * $product->other_uom_conversion;
                    }
                }
            } else {
                $err_msg[] = [
                    'price_code' => ''
                ];

                Session::put('price_code_err', $err_msg);
            }

            $total = 0;
            // CONVERT BASE ON UOM
            if($uom == $product->stock_uom) {
                $total += $quantity * $selling_price;
            } else if($uom == $product->order_uom) {
                // check operation
                if($product->order_uom_operator == 'M') { // MULTIPLY
                    $total += ($quantity * $product->order_uom_conversion) * $selling_price;
                }
                if($product->order_uom_operator == 'D') { // DIVIDE
                    $total += ($quantity / $product->order_uom_conversion) * $selling_price;
                }
            } else if($uom == $product->other_uom) {
                if($product->other_uom_operator == 'M') { // MULTIPLY
                    $total += ($quantity * $product->other_uom_conversion) * $selling_price;
                }
                if($product->other_uom_operator == 'D') { // DIVIDE
                    $total += ($quantity / $product->other_uom_conversion) * $selling_price;
                }
            } else { // if UOM is not found use default price code UOM
                $total += $quantity * $selling_price;
            }

            // apply discount
            $discounted = $total;
            if(!empty($discount)) {
                if($discount->discount_1 > 0) {
                    $discounted = $discounted * ((100 - $discount->discount_1) / 100);
                }
                if($discount->discount_2 > 0) {
                    $discounted = $discounted * ((100 - $discount->discount_2) / 100);
                }
                if($discount->discount_3 > 0) {
                    $discounted = $discounted * ((100 - $discount->discount_3) / 100);
                }
            }

            return $discounted;

        } else {
            if(empty($company)) {
                $err_msg[]['company'] = $vendor;
            }
            if(empty($account)) {
                $err_msg[]['account'] = $account_code;
            }
            if(empty($product)) {
                $err_msg[]['product'] = $sku_code;
            }

            Session::put('price_code_err', $err_msg);

            return null;
        }

    }
}