<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSProduct extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $connection = 'sms_db';

    public function channel() {
        return $this->belongsTo('App\Models\SMSClassification');
    }

    public function price_codes() {
        return $this->hasMany('App\Models\SMSPriceCode', 'product_id', 'id');
    }

    public function references() {
        return $this->hasMany('App\Models\SMSAccountProductReference', 'product_id', 'id');
    }
}
