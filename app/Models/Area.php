<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'account_branch_id',
        'code',
        'name'
    ];

    public function getConnectionName() {
        return auth()->check() ? auth()->user()->account->db_data->connection_name : null;
    }

    public function account() {
        return $this->belongsTo('App\Models\SMSAccount', 'account_id', 'id');
    }

    public function account_branch() {
        return $this->belongsTo('App\Models\AccountBranch');
    }

    public function salesmen() {
        return $this->belongsToMany('App\Models\Salesman');
    }

    public function sales() {
        return $this->hasMany('App\Models\Sales');
    }
}
