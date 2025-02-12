<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSAccountProductReference extends Model
{
    use HasFactory;

    protected $table = 'account_product_references';
    protected $connection = 'sms_db';

    public function account() {
        return $this->belongsTo('App\Models\SMSAccount', 'id', 'account_id');
    }

    public function product() {
        return $this->belongsTo('App\Models\SMSProduct', 'id', 'product_id');
    }
}
