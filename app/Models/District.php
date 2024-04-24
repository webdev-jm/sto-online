<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_branch_id',
        'district_code'
    ];
    
    public function getConnectionName() {
        return auth()->check() ? auth()->user()->account->db_data->connection_name : null;
    }

    public function areas() {
        return $this->belongsToMany('App\Models\Area');
    }

    public function sales_people() {
        return $this->hasMany('App\Models\Salesman');
    }
}
