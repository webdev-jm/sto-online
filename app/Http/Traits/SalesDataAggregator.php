<?php

namespace App\Http\Traits;

use App\Models\SMSProduct;
use App\Models\SMSPriceCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\PriceCodeTrait;

trait SalesDataAggregator
{
    use PriceCodeTrait;

    public function getYearlySalesData(int $year): array
    {
        return Cache::remember("sales_data_consolidated_{$year}", 60 * 60, function () use ($year) {

            $sqlite = DB::connection('sqlite_reports');

            // Pull distinct stock codes for price calculation
            $stockCodes = $sqlite->table('sales_data')
                ->where('year', $year)
                ->distinct()
                ->pluck('stock_code');

            $products = SMSProduct::whereIn('stock_code', $stockCodes)
                ->get()
                ->keyBy('stock_code');

            // Build price cache per account's sms_account/company
            // Group accounts by price_code + company to avoid redundant DB hits
            $accounts = \App\Models\Account::where('id', '>=', 10)
                ->with('sms_account.company')
                ->get()
                ->filter(fn($a) => $a->sms_account && $a->sms_account->company)
                ->keyBy('account_code');

            // Pre-build price cache: account_code => stock_code => price
            $priceCache = [];
            foreach ($accounts as $accountCode => $account) {
                $smsAccount = $account->sms_account;
                $smsCompany = $smsAccount->company;

                $priceCodes = SMSPriceCode::where('company_id', $smsCompany->id)
                    ->where('code', $smsAccount->price_code)
                    ->whereIn('product_id', $products->pluck('id'))
                    ->get()
                    ->keyBy('product_id');

                foreach ($products as $code => $product) {
                    $pCode     = $priceCodes->get($product->id);
                    $basePrice = $pCode ? $this->calculateBaseUnitPrice($product, $pCode) : 0;

                    if ($smsAccount->discount && $basePrice > 0) {
                        $basePrice = $this->applyDiscounts($basePrice, $smsAccount->discount);
                    }

                    $priceCache[$accountCode][$code] = $basePrice;
                }
            }

            // Query SQLite — one query for the entire year
            $rows = $sqlite->table('sales_data')
                ->where('year', $year)
                ->get();

            $masterData = [];

            foreach ($rows as $row) {
                $product = $products->get($row->stock_code);
                if (!$product) continue;

                $netPrice  = $priceCache[$row->account_code][$row->stock_code] ?? 0;
                $uomFactor = $this->getConversionFactor($product, $row->uom);
                $qtyPcs    = $row->quantity * $uomFactor;

                $account = $accounts->get($row->account_code);

                $masterData[] = [
                    'customer_code'   => $row->customer_code,
                    'customer_name'   => $row->customer_name,
                    'channel_code'    => $row->channel_code,
                    'channel_name'    => $row->channel_name,
                    'customer_status' => $row->customer_status,
                    'sku'             => $row->stock_code,
                    'name'            => $product->description,
                    'full_name'       => "{$product->stock_code} {$product->description} {$product->size}",
                    'brand'           => $product->brand,
                    'brand_tag'       => $product->brand_tag,
                    'category'        => $product->category,
                    'month'           => (int) $row->month,
                    'sales'           => (float) ($qtyPcs * $netPrice),
                    'qty_pcs'         => (float) $qtyPcs,
                    'account_id'      => $account?->id,
                    'account_name'    => $row->account_name,
                    'short_name'      => $account?->short_name,
                    'area'            => $row->area,
                ];
            }

            return $masterData;
        });
    }

    public function getYearlyInventoryData(int $year): array
    {
        return Cache::remember("inventory_data_consolidated_{$year}", 60 * 60, function () use ($year) {

            $accounts = \App\Models\Account::where('id', '>=', 10)
                ->get()
                ->keyBy('account_code');

            return DB::connection('sqlite_reports')
                ->table('inventory_data')
                ->where('year', $year)
                ->get()
                ->map(function ($row) use ($accounts) {
                    $account = $accounts->get($row->account_code);

                    return [
                        'sku'          => $row->stock_code,
                        'name'         => $row->description,
                        'full_name'    => "{$row->stock_code} {$row->description} {$row->size}",
                        'year'         => $row->year,
                        'month'        => (int) $row->month,
                        'total'        => (float) $row->total,
                        'uom'          => $row->uom,
                        'account_id'   => $account?->id,
                        'account_code' => $row->account_code,
                        'short_name'   => $account?->short_name,
                    ];
                })
                ->toArray();
        });
    }

    public function getYearlyInventoryAgingData(int $year): array
    {
        return Cache::remember("inventory_aging_data_consolidated_{$year}", 60 * 60, function () use ($year) {

            $accounts = \App\Models\Account::where('id', '>=', 10)
                ->get()
                ->keyBy('account_code');

            // SQLite handles the grouping + sum — no PHP collection gymnastics needed
            $rows = DB::connection('sqlite_reports')
                ->table('inventory_aging')
                ->select(
                    'stock_code',
                    'description',
                    'size',
                    'uom',
                    'expiry_date',
                    'location_code',
                    'location_name',
                    'account_code',
                    DB::raw('SUM(inventory) as total_inventory')
                )
                ->where('year', $year)
                ->groupBy('stock_code', 'expiry_date', 'uom', 'account_code', 'location_code', 'location_name', 'description', 'size')
                ->get();

            return $rows->map(function ($row) use ($accounts) {
                $account = $accounts->get($row->account_code);

                return [
                    'location_code'   => $row->location_code,
                    'location_name'   => $row->location_name,
                    'stock_code'      => $row->stock_code,
                    'name'            => $row->description,
                    'size'            => $row->size,
                    'uom'             => $row->uom,
                    'expiry_date'     => $row->expiry_date,
                    'total_inventory' => (float) $row->total_inventory,
                    'remaining_days'  => $this->computeRemainingDays($row->expiry_date),
                    'account_code'    => $account->account_code,
                    'short_name'      => $account->short_name,
                    'account_id'      => $account?->id,
                ];
            })->toArray();
        });
    }

    private function computeRemainingDays($expiryDate): int
    {
        if (empty($expiryDate)) return 0;

        $today  = new \DateTime();
        $expiry = new \DateTime($expiryDate);

        return (int) $today->diff($expiry)->format('%r%a');
    }
}
