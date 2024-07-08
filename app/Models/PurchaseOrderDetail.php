<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'sku_code',
        'sku_code_other',
        'product_name',
        'quantity',
        'unit_of_measure',
        'discount_amount',
        'gross_amount',
        'net_amount',
        'net_amount_per_uom'
    ];

    public function getConnectionName() {
        return auth()->check() ? auth()->user()->account->db_data->connection_name : null;
    }

    public function purchase_order() {
        return $this->belongsTo('App\Models\PurchaseOrder');
    }
}
