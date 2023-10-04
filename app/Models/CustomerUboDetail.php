<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerUboDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'account_branch_id',
        'customer_ubo_id',
        'customer_id',
        'ubo_id',
        'name',
        'address',
        'similarity',
        'address_similarity',
    ];

    public function customer_ubo() {
        return $this->belongsTo('App\Model\CustomerUbo');
    }

    public function customer() {
        return $this->belongsTo('App\Model\Customer');
    }
}
