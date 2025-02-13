<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderAttachment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'path',
    ];

    public function getConnectionName() {
        return auth()->check() ? auth()->user()->account->db_data->connection_name : null;
    }

    public function purchase_order() {
        return $this->belongsTo('App\Models\PurchaseOrder');
    }
}
