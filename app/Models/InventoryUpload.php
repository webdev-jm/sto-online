<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryUpload extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'user_id',
        'date',
        'total_inventory',
    ];

    public function account() {
        return $this->belongsTo('App\Models\SMSAccount', 'account_id', 'id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function inventories() {
        return $this->hasMany('App\Models\Inventory');
    }
}
