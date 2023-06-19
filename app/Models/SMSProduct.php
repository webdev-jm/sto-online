<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSProduct extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $connection = 'sms_db';
}
