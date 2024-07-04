<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountProductReference extends Model
{
    use HasFactory;
    
    protected $table = 'account_product_references';
    protected $connection = 'sms_db';
}
