<?php

namespace App\Http\Traits;

use App\Models\SMSAccount;
use App\Models\SMSCompany;
use App\Models\SMSProduct;
use App\Models\SMSPriceCode;
use App\Http\Traits\UomConversionTrait;

use Illuminate\Support\Facades\Session;

Trait PriceCodeTrait {

    use UomConversionTrait;
    
    // get product price base on account and Unit of Measurement(UOM)
    public function getProductPrice($accountInput, $vendorInput, $skuInput, $quantity, $uom = 'PCS', $withDiscount = true)
    {
        // 1. Resolve Models
        $account = $this->resolveModel(SMSAccount::class, 'account_code', $accountInput);
        $company = $this->resolveModel(SMSCompany::class, 'name', $vendorInput);
        $product = $this->resolveModel(SMSProduct::class, 'stock_code', $skuInput);

        if (!$account || !$company || !$product) {
            $this->logError('missing_data', [
                'account' => $account ? null : $accountInput,
                'company' => $company ? null : $vendorInput,
                'product' => $product ? null : $skuInput,
            ]);
            return null;
        }

        // 2. Fetch Price Code
        $priceCode = SMSPriceCode::where('company_id', $company->id)
            ->where('product_id', $product->id)
            ->where('code', $account->price_code)
            ->first();

        if (!$priceCode) {
            $this->logError('price_code_missing', ['sku' => $skuInput]);
            return null;
        }

        // 3. GET BASE UNIT PRICE (Price per 1 Stock Unit)
        // We use the helper below which leverages your UomConversion logic
        $baseUnitPrice = $this->calculateBaseUnitPrice($product, $priceCode);

        // 4. CONVERT QUANTITY TO BASE UNITS
        // Instead of calculating "Price per Target UOM", we calculate "Total Base Units Needed"
        // Example: User wants 2 CS. 1 CS = 12 PCS.
        // convertUom(product, 'CS', 2, 'PCS') returns 24.
        // Total Price = 24 PCS * PricePerPCS.
        $totalBaseUnits = $this->convertUom($product, $uom, $quantity, $product->stock_uom);
        
        $totalPrice = $totalBaseUnits * $baseUnitPrice;

        // 5. Apply Discounts
        if ($withDiscount && $account->discount) {
            $totalPrice = $this->applyDiscounts($totalPrice, $account->discount);
        }

        return $totalPrice;
    }

    /**
     * Calculate the price of ONE Single Stock Unit (e.g., 1 PCS).
     * This uses the logic from your UomConversionTrait implicitly by checking operators.
     */
    public function calculateBaseUnitPrice($product, $priceCode)
    {
        $price = (float) $priceCode->selling_price;
        $basis = $priceCode->price_basis; // 'A' (Order), 'O' (Other), 'S' (Stock)

        // If price is already in Stock UOM, return as is.
        if ($basis !== 'A' && $basis !== 'O') {
            return $price;
        }

        // We need to know: "How many Stock Units are in this Price Basis?"
        // We can reuse getConversionFactor() from your UomConversionTrait!
        
        $basisUom = ($basis === 'A') ? $product->order_uom : $product->other_uom;
        
        // This function comes from UomConversionTrait
        $factor = $this->getConversionFactor($product, $basisUom); 

        // If the price is $120 for a Case (Factor 12), the unit price is $120 / 12.
        return ($factor != 0) ? ($price / $factor) : $price;
    }

    /**
     * Apply chain discounts (e.g., 10% + 5% + 2%).
     */
    private function applyDiscounts($amount, $discountModel)
    {
        $discounts = [
            $discountModel->discount_1,
            $discountModel->discount_2,
            $discountModel->discount_3
        ];

        foreach ($discounts as $disc) {
            if ($disc > 0) {
                $amount *= (1 - ($disc / 100));
            }
        }

        return $amount;
    }

    /**
     * Helper to resolve a Model from ID/Code or return the Model itself.
     */
    private function resolveModel($class, $field, $input)
    {
        if ($input instanceof $class) {
            return $input;
        }
        return $class::where($field, $input)->first();
    }

    private function logError($type, $data)
    {
        $errors = Session::get('price_code_err', []);
        
        if ($type === 'missing_data') {
            foreach ($data as $key => $value) {
                if ($value) $errors[] = [$key => $value];
            }
        } elseif ($type === 'price_code_missing') {
            $errors[] = ['price_code' => $data['sku'] ?? 'unknown'];
        }

        Session::put('price_code_err', $errors);
    }
}