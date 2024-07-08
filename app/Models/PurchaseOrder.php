<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sms_account_id',
        'account_branch_id',
        'po_number',
        'order_date',
        'ship_date',
        'shipping_instruction',
        'ship_to_name',
        'ship_to_address',
        'status',
        'total_quantity',
        'total_sales',
        'grand_total',
        'po_value'
    ];

    public function getConnectionName() {
        return auth()->check() ? auth()->user()->account->db_data->connection_name : null;
    }

    public function details() {
        return $this->hasMany('App\Models\PurchaseOrderDetail');
    }
}
