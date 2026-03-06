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
        return Cache::remember("sales_data_consolidated_{$year}", 60 * 60, function () use ($year) {

            set_time_limit(120);
            ini_set('memory_limit', '512M');

            $accounts = Account::where('id', '>=', 10)->get();
            $masterData = [];

            foreach ($accounts as $account) {
                // 1. Get account details once per account
                $smsAccount = $account->sms_account;
                $smsCompany = $smsAccount ? $smsAccount->company : null;

                if (!$smsAccount || !$smsCompany) continue;

                // 2. Loop through each month
                foreach (range(1, 12) as $m) {
                    $jsonPath = storage_path("app/reports/consolidated_account_data-{$account->account_code}-{$year}-{$m}.json");

                    if (!file_exists($jsonPath)) continue;

                    $raw = json_decode(file_get_contents($jsonPath), true);
                    $yearRows = collect($raw['sales_data'] ?? [])->where('year', $year);

                    if ($yearRows->isEmpty()) continue;

                    // 3. Process products and prices for this specific month's file
                    $stockCodes = $yearRows->pluck('stock_code')->unique();
                    $products = SMSProduct::whereIn('stock_code', $stockCodes)->get()->keyBy('stock_code');

                    $priceCodes = SMSPriceCode::where('company_id', $smsCompany->id)
                        ->where('code', $smsAccount->price_code)
                        ->whereIn('product_id', $products->pluck('id'))
                        ->get()
                        ->keyBy('product_id');

                    // Pre-calculate prices for this batch
                    $priceCache = [];
                    foreach ($products as $code => $product) {
                        $pCode = $priceCodes->get($product->id);
                        $basePrice = $pCode ? $this->calculateBaseUnitPrice($product, $pCode) : 0;
                        if ($smsAccount->discount && $basePrice > 0) {
                            $basePrice = $this->applyDiscounts($basePrice, $smsAccount->discount);
                        }
                        $priceCache[$code] = $basePrice;
                    }

                    // 4. Normalize and push to Master Data
                    foreach ($yearRows as $row) {
                        $code = $row['stock_code'];
                        $product = $products->get($code);

                        if (!$product) continue;

                        $netPrice = $priceCache[$code] ?? 0;
                        $uomFactor = $this->getConversionFactor($product, $row['uom']);
                        $qtyPcs = $row['quantity'] * $uomFactor;

                        $masterData[] = [
                            'customer_code' => $row['customer_code'],
                            'customer_name' => $row['customer_name'],
                            'channel_code' => $row['channel_code'],
                            'channel_name' => $row['channel_name'],
                            'customer_status' => $row['customer_status'],
                            'sku'       => $code,
                            'name'      => $product->description,
                            'full_name' => "{$product->stock_code} {$product->description} {$product->size}",
                            'brand'     => $product->brand,
                            'month'     => (int) $row['month'],
                            'sales'     => (float) ($qtyPcs * $netPrice),
                            'qty_pcs'   => (float) $qtyPcs,
                            'account_id'=> $account->id
                        ];
                    }
                } // End Month Loop
            } // End Account Loop

            return $masterData;
        });
    }

    public function getYearlyInventoryData(int $year)
    {
        return Cache::remember("inventory_data_consolidated_{$year}", 60 * 60, function () use ($year) {
            set_time_limit(120);
            ini_set('memory_limit', '512M');

            $accounts = Account::where('id', '>=', 10)->get();
            $masterData = [];

            foreach ($accounts as $account) {
                $smsAccount = $account->sms_account;
                $smsCompany = $smsAccount ? $smsAccount->company : null;

                if (!$smsAccount || !$smsCompany) continue;

                foreach (range(1, 12) as $m) {
                    $jsonPath = storage_path("app/reports/consolidated_account_data-{$account->account_code}-{$year}-{$m}.json");

                    if (!file_exists($jsonPath)) continue;

                    $raw = json_decode(file_get_contents($jsonPath), true);
                    $yearRows = collect($raw['inventory_data'] ?? [])->where('year', $year);

                    if ($yearRows->isEmpty()) continue;

                    foreach($yearRows as $row) {
                        $masterData[] = [
                            'sku'       => $row['stock_code'],
                            'name'      => $row['description'],
                            'full_name' => "{$row['stock_code']} {$row['description']} {$row['size']}",
                            'year'     => $row['year'],
                            'month'     => (int) $row['month'],
                            'total'     => (float) $row['total'],
                            'uom'       => $row['uom'],
                            'account_id'=> $account->id
                        ];
                    }
                }
            }

            return $masterData;

        });
    }

    public function getYearlyInventoryAgingData(int $year)
    {
        return Cache::remember("inventory_aging_data_consolidated_{$year}", 60 * 60, function () use ($year) {
            set_time_limit(120);
            ini_set('memory_limit', '512M');

            $accounts = Account::where('id', '>=', 10)->get();
            $masterData = [];

            foreach ($accounts as $account) {
                $smsAccount = $account->sms_account;
                $smsCompany = $smsAccount ? $smsAccount->company : null;

                if (!$smsAccount || !$smsCompany) continue;

                foreach (range(1, 12) as $m) {
                    $jsonPath = storage_path("app/reports/consolidated_account_data-{$account->account_code}-{$year}-{$m}.json");

                    $raw = json_decode(file_get_contents($jsonPath), true);
                    $yearRows = collect($raw['inventory_aging'] ?? []);

                    if ($yearRows->isEmpty()) continue;

                    foreach($yearRows as $row) {

                        $remainingDays = $this->computeRemainingDays($row['expiry_date']);

                        $masterData[] = [
                            'location_code' => $row['location_code'],
                            'location_name' => $row['location_name'],
                            'stock_code' => $row['stock_code'],
                            'name' => $row['description'],
                            'size' => $row['size'],
                            'uom' => $row['uom'],
                            'expiry_date' => $row['expiry_date'],
                            'total_inventory' => $row['inventory'],
                            'remaining_days' => $remainingDays,
                            'account_id'=> $account->id
                        ];

                    }
                }
            }

            return collect($masterData)
                ->groupBy(function ($item) {
                    return $item['stock_code'] . '|' . $item['expiry_date'] . '|' . $item['uom'];
                })
                ->map(function ($group) {
                    $first = $group->first();

                    $first['total_inventory'] = $group->sum('total_inventory');

                    return $first;
                })
                ->values()
                ->all();

        });
    }

    private function computeRemainingDays($expiryDate) {
        if (empty($expiryDate)) {
            return 0;
        }

        $today = new \DateTime();
        $expiry = new \DateTime($expiryDate);

        // This returns the difference. We use format('%r%a') to get
        // a signed integer (e.g., -5 for expired, 10 for future).
        $interval = $today->diff($expiry);

        return (int)$interval->format('%r%a');
    }

}
