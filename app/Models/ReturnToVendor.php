<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturnToVendor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'account_branch_id',
        'rtv_number',
        'document_number',
        'ship_date',
        'entry_date',
        'reason',
        'ship_to_name',
        'ship_to_address',
    ];

    public function getConnectionName() {
        return auth()->check() ? auth()->user()->account->db_data->connection_name : null;
    }

    public function rtv_products() {
        return $this->hasMany('App\Models\ReturnToVendorProduct');
    }
}
