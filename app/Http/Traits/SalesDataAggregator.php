<?php

namespace App\Http\Traits;

use App\Models\Account;
use App\Models\SMSProduct;
use App\Models\SMSPriceCode;
use Illuminate\Support\Facades\Cache;
use App\Http\Traits\PriceCodeTrait;

trait SalesDataAggregator
{
    use PriceCodeTrait;

    /**
     * Retrieves processed sales data for a specific year.
     * Caches the result to avoid re-reading files and re-calculating prices.
     */
    public function getYearlySalesData(int $year)
    {
        // Cache Key: Unique per year. Clears automatically after 60 minutes.
        return Cache::remember("sales_data_consolidated_{$year}", 60 * 60, function () use ($year) {

            // 1. Performance Settings (Only runs on cache miss)
            set_time_limit(120);
            ini_set('memory_limit', '512M');

            $accounts = Account::where('id', '>=', 10)->get();
            $masterData = [];

            foreach ($accounts as $account) {
                $jsonPath = storage_path('app/reports/consolidated_account_data-' . $account->account_code . '.json');

                if (!file_exists($jsonPath)) continue;

                $raw = json_decode(file_get_contents($jsonPath), true);

                // --- OPTIMIZATION 1: EARLY FILTER ---
                // Only take rows for the requested year. Ignore the rest of history.
                $yearRows = collect($raw['sales_data'] ?? [])->where('year', $year);

                if ($yearRows->isEmpty()) continue;

                // --- OPTIMIZATION 2: BATCH LOAD ---
                // Load only products needed for this year's data
                $stockCodes = $yearRows->pluck('stock_code')->unique();
                $products = SMSProduct::whereIn('stock_code', $stockCodes)->get()->keyBy('stock_code');

                // Load Prices
                $smsAccount = $account->sms_account;
                $smsCompany = $smsAccount ? $smsAccount->company : null;

                if (!$smsAccount || !$smsCompany) continue;

                $priceCodes = SMSPriceCode::where('company_id', $smsCompany->id)
                    ->where('code', $smsAccount->price_code)
                    ->whereIn('product_id', $products->pluck('id'))
                    ->get()
                    ->keyBy('product_id');

                // Pre-calculate Price Cache for this Account
                $priceCache = [];
                foreach ($products as $code => $product) {
                    $pCode = $priceCodes->get($product->id);
                    $basePrice = $pCode ? $this->calculateBaseUnitPrice($product, $pCode) : 0;
                    if ($smsAccount->discount && $basePrice > 0) {
                        $basePrice = $this->applyDiscounts($basePrice, $smsAccount->discount);
                    }
                    $priceCache[$code] = $basePrice;
                }

                // --- OPTIMIZATION 3: NORMALIZE DATA ---
                // Convert everything into a simple flat array of line items
                foreach ($yearRows as $row) {
                    $code = $row['stock_code'];
                    $product = $products->get($code);

                    if (!$product) continue;

                    // Pricing
                    $netPrice = $priceCache[$code] ?? 0;
                    $uomFactor = $this->getConversionFactor($product, $row['uom']);

                    $qtyPcs = $row['quantity'] * $uomFactor;
                    $totalSales = $qtyPcs * $netPrice;

                    $masterData[] = [
                        'sku' => $code,
                        'name' => $product->description,
                        'full_name' => $product->stock_code . ' ' . $product->description . ' '. $product->size,
                        'brand' => $product->brand,
                        'month' => (int) $row['month'],
                        'sales' => (float) $totalSales,
                        'qty_pcs' => (float) $qtyPcs
                    ];
                }
            }

            return $masterData;
        });
    }
}
