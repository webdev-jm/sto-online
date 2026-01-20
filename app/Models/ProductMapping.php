<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMapping extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'product_id',
        'external_stock_code',
        'type',
    ];

    public function account()
    {
        return $this->belongsTo('App\Models\Account');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\SMSProduct');
    }
}
