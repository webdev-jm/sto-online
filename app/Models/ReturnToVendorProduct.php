<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use illuminate\Database\Eloquent\SoftDeletes;

class ReturnToVendorProduct extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'return_to_vendor_id',
        'sku_code',
        'other_sku_code',
        'description',
        'uom',
        'quantity',
        'cost',
        'reason',
    ];

    public function getConnectionName() {
        return auth()->check() ? auth()->user()->account->db_data->connection_name : null;
    }
}
