<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_branch_id',
        'customer_id',
        'total_units_transferred_ty',
        'total_units_transferred_ly',
    ];
}
