<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountBranchShippingAddressMapping extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_branch_id',
        'ship_to_code',
        'ship_to_name',
        'bevi_ship_to_code',
    ];
}
