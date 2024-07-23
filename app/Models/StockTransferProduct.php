<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransferProduct extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'sku_code',
        'sku_code_other',
        'transfer_ty',
        'transfer_ly',
    ];

    public function getConnectionName() {
        return auth()->check() ? auth()->user()->account->db_data->connection_name : null;
    }

    public function sku() {
        return $this->belongsTo('App\Models\SMSProduct', 'product_id', 'id');
    }
}
