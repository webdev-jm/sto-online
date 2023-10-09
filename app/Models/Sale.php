<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sales_upload_id',
        'account_id',
        'account_branch_id',
        'customer_id',
        'product_id',
        'channel_id',
        'salesman_id',
        'location_id',
        'user_id',
        'type',
        'date',
        'document_number',
        'category',
        'uom',
        'quantity',
        'price_inc_vat',
        'amount',
        'amount_inc_vat',
        'status',
    ];

    public function sales_upload() {
        return $this->belongsTo('App\Models\SalesUpload');
    }

    public function account() {
        return $this->belongsTo('App\Models\SMSAccount', 'account_id', 'id');
    }

    public function account_branch() {
        return $this->belongsTo('App\Models\AccountBranch');
    }

    public function customer() {
        return $this->belongsTo('App\Models\Customer');
    }

    public function channel() {
        return $this->belongsTo('App\Models\Channel');
    }

    public function salesman() {
        return $this->belongsTo('App\Models\Salesman');
    }
    
    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function location() {
        return $this->belongsTo('App\Models\Location');
    }

    public function product() {
        return $this->belongsTo('App\Models\SMSProduct', 'product_id', 'id');
    }
}
