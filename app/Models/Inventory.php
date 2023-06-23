<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable = [
        'account_id',
        'inventory_upload_id',
        'location_id',
        'product_id',
        'type',
        'uom',
        'inventory'
    ];

    public function account() {
        return $this->belongsTo('App\Models\SMSAccount', 'account_id', 'id');
    }

    public function inventory_upload() {
        return $this->belongsTo('App\Models\InventoryUpload');
    }

    public function location() {
        return $this->belongsTo('App\Models\Location');
    }

    public function product() {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }
}
