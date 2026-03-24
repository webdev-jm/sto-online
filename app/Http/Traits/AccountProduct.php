<?php

namespace App\Http\Traits;

use App\Models\SMSPriceCode;
use App\Models\SMSAccount;
use App\Models\SMSProduct;

trait AccountProduct
{
    public function getAssignedProducts($account_code) {
        $account = SMSAccount::where('account_code', $account_code)
            ->select('company_id', 'price_code')
            ->first();

        if ($account === null) {
            return [];
        }

        $priceCodeQuery = SMSPriceCode::where('company_id', $account->company_id)
            ->where('code', $account->price_code)
            ->select('product_id');

        return SMSProduct::whereIn('id', $priceCodeQuery)->get();
    }
}
