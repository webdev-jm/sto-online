<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountBranchProductMapping extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_branch_id',
        'sku_code',
        'other_sku_code',
        'description',
        'uom',
        'brand'
    ];
}
