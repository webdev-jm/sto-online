<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SMSPriceCode extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'price_codes';
    protected $connection = 'sms_db';

    public function product() {
        return $this->belongsTo('App\Models\SMSProduct', 'id', 'product_id');
    }
}
