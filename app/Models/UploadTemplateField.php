<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UploadTemplateField extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'upload_template_id',
        'number',
        'column_name',
        'column_name_alt'
    ];

    public function template() {
        return $this->belongsTo('App\Models\UploadTemplate');
    }
}
