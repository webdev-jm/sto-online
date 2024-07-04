<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountUploadTemplate extends Model
{
    use HasFactory;
    use SoftDeletesl;

    protected $fillable = [
        'account_id',
        'upload_template_id'
    ];
}
