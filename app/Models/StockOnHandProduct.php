<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOnHandProduct extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'stock_on_hand_id',
        'product_id',
        'sku_code',
        'sku_code_other',
        'inventory',
    ];

    public function getConnectionName() {
        return auth()->check() ? auth()->user()->account->db_data->connection_name : null;
    }

    public function stock_on_hand() {
        return $this->belongsTo('App\Models\StockOnHand');
    }
}
