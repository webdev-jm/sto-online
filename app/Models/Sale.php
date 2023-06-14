<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'customer_id',
        'area_id',
        'channel_id',
        'salesman_id',
        'user_id',
        'uom',
        'quantity',
        'sales',
    ];

    public function account() {
        return $this->belongsTo('App\Models\SMSAccount', 'account_id', 'id');
    }

    public function customer() {
        return $this->belongsTo('App\Models\Customer');
    }

    public function area() {
        return $this->belongsTo('App\Models\Area');
    }

    public function channel() {
        return $this->belongsTo('App\Models\Channel');
    }

    public function salesman() {
        return $this->belongsTo('App\Models\Salesman');
    }
    
    public function user() {
        return $this->belongsTo('App\Models\User');
    }
}
