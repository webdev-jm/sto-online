<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'province_id',
        'municipality_name'
    ];

    public function barangays() {
        return $this->hasMany('App\Models\Barangay');
    }

    public function province() {
        return $this->belongsTo('App\Models\Province');
    }
}
