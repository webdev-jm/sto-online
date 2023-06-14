<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'salesman_id',
        'code',
        'name',
        'address',
    ];

    public function account() {
        return $this->belongsTo('App\Models\SMSAccount', 'account_id', 'id');
    }

    public function salesman() {
        return $this->belongsTo('App\Models\Salesman');
    }

    public function sales() {
        return $this->hasMany('App\Models\Sales');
    }
}
