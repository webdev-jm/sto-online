<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SMSAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'accounts';
    protected $connection = 'sms_db';

    public function branches() {
        return $this->hasMany(\App\Models\SMSBranch::class, 'account_id', 'id');
    }

    public function users() {
        return $this->belongsToMany(\App\Models\User::class, env('DB_DATABASE_2').'.account_user', 'account_id', 'user_id');
    }
}
