<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSBranch extends Model
{
    use HasFactory;

    protected $table = 'branches';
    protected $connection = 'sms_db';

    public function account() {
        return $this->belongsTo('App\Models\SMSAccount', 'id', 'account_id');
    }
}
