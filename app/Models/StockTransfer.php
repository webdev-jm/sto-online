<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_branch_id',
        'customer_id',
        'year',
        'month',
        'total_units_transferred_ty',
        'total_units_transferred_ly',
    ];

    public function getConnectionName() {
        return auth()->check() ? auth()->user()->account->db_data->connection_name : null;
    }

    public function customer() {
        return $this->belongsTo('App\Models\Customer');
    }

    public function products() {
        return $this->hasMany('App\Models\StockTransferProduct');
    }
}
