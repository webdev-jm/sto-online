<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesUpload extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'account_branch_id',
        'user_id',
        'sku_count',
        'total_quantity',
        'total_price_vat',
        'total_amount',
        'total_amount_vat',
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function sales() {
        return $this->hasMany('App\Models\Sale');
    }
}
