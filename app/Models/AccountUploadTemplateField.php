<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountUploadTemplateField extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_upload_template_id',
        'upload_template_field_id',
        'number',
        'file_column_name',
    ];
}
