<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'code',
        'name',
    ];

    public function sales() {
        return $this->hasMany('App\Models\Sales');
    }

    public function customers() {
        return $this->hasMany('App\Models\Customer');
    }
    
}
