<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'notification_reminders';

    protected $fillable = [
        'subject',
        'from_email',
        'from_name',
        'message',
        'link_name',
        'link_url',
    ];
}
