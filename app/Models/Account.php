<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'sms_account_id',
        'account_code',
        'account_name',
        'short_name',
        'account_password',
    ];

    public function sms_account() {
        return $this->belongsTo(\App\Models\SMSAccount::class, 'sms_account_id', 'id');
    }

    public function users() {
        return $this->belongsToMany('App\Models\User');
    }
}
