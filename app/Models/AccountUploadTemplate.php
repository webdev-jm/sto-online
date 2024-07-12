<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountUploadTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'upload_template_id'
    ];

    public function upload_template() {
        return $this->belongsTo('App\Models\UploadTemplate');
    }

    public function fields() {
        return $this->hasMany('App\Models\AccountUploadTemplateField');
    }
}
