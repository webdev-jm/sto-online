<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOnHand extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_branch_id',
        'customer_id',
        'year',
        'month',
        'total_inventory',
    ];

    public function getConnectionName() {
        return auth()->check() ? auth()->user()->account->db_data->connection_name : null;
    }

    public function account_branch() {
        return $this->belongsTo('App\Models\AccountBranch');
    }

    public function products() {
        return $this->hasMany('App\Models\StockOnHandProduct');
    }
}
