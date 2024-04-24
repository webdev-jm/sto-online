<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salesman extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'account_branch_id',
        'district_id',
        'code',
        'name',
        'type',
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

    public function customers() {
        return $this->hasMany('App\Models\Customer');
    }

    public function salesman_customers() {
        return $this->hasMany('App\Models\SalesmanCustomer');
    }

    public function sales() {
        return $this->hasMany('App\Models\Sales');
    }

    public function district() {
        return $this->belongsTo('App\Models\District');
    }
}
