<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerUbo extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'account_branch_id',
        'customer_id',
        'ubo_id',
        'name',
        'address'
    ];

    public function customer() {
        return $this->belongsTo('App\Models\Customer');
    }

    public function ubo_details() {
        return $this->hasMany('App\Models\CustomerUboDetail');
    }
}
