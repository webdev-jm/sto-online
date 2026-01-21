<?php

namespace App\Http\Traits;

use App\Models\ProductMapping;

Trait ProductMappingTrait {

    public function productMapping($account_id, $stock_code) {
        $product_mapping = ProductMapping::where('account_id', $account_id)
            ->where('external_stock_code', $stock_code)
            ->first();

        $type = NULL;
        if(!empty($product_mapping)) {
            $stock_code = $product_mapping->product->stock_code;
            $type = $product_mapping->type;
        }

        return [$stock_code, $type];
    }
}
