<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsolidatedSalesReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_code',
        'account_name',,
        'account_description',
        'customer_code',
        'customer_name',
        'province',
        'city',
        'brgy',
        'year',
        'month',
        'stock_code',
        'description',
        'size',
        'brand_classification',
        'brand',
        'category',
        'uom',
        'quantity',
        'sales',
        'fg_quantity',
        'fg_sales',
        'promo_quantity',
        'promo_sales',
        'credit_memo',
        'parked_quantity',
        'parked_amount',
    ];
}
