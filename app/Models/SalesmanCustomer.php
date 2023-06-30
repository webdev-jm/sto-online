<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesmanCustomer extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable = [
        'salesman_id',
        'customer_id',
        'start_date',
        'end_date'
    ];

    public function salesman() {
        return $this->belongsTo('App\Models\Salesman');
    }

    public function customer() {
        return $this->belongsTo('App\Models\Customer');
    }
}
