<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryExpiry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'account_branch_id',
        'location_id',
        'product_id',
        'inventory_id',
        'quantity',
        'expiry_date',
    ];

    public function getConnectionName() {
        return auth()->check() ? auth()->user()->account->db_data->connection_name : null;
    }

    public function account() {
        return $this->belongsTo('App\Models\Account', 'account_id', 'id');
    }

    public function account_branch() {
        return $this->belongsTo('App\Models\AccountBranch', 'account_branch_id', 'id');
    }

    public function location() {
        return $this->belongsTo('App\Models\Location', 'location_id', 'id');
    }

    public function product() {
        return $this->belongsTo('App\Models\SMSProduct', 'product_id', 'id');
    }

    public function inventory() {
        return $this->belongsTo('App\Models\Inventory', 'inventory_id', 'id');
    }
}
