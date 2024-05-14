<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'account_branch_id',
        'salesman_id',
        'channel_id',
        'province_id',
        'municipality_id',
        'barangay_id',
        'code',
        'name',
        'address',
        'street',
        'brgy',
        'city',
        'province',
        'country',
        'postal_code',
        'status',
    ];

    public function getConnectionName() {
        return auth()->check() ? auth()->user()->account->db_data->connection_name : null;
    }

    public function account() {
        return $this->belongsTo('App\Models\SMSAccount', 'account_id', 'id');
    }

    public function account_branch() {
        return $this->belongsTo('App\Models\AccountBranch');
    }

    public function salesman() {
        return $this->belongsTo('App\Models\Salesman');
    }
    
    public function channel() {
        return $this->belongsTo('App\Models\Channel');
    }

    public function sales() {
        return $this->hasMany('App\Models\Sale');
    }

    public function customer_salesmen() {
        return $this->hasMany('App\Models\SalesmanCustomer');
    }

    public function ubo() {
        return $this->hasMany('App\Models\CustomerUbo');
    }

    public function ubo_detail() {
        return $this->hasMany('App\Models\CustomerUboDetail');
    }
}
