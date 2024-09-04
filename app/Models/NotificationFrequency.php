<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationFrequency extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'time',
        'day',
        'week_day',
    ];

    public function user_notifications() {
        return $this->hasMany('App\Models\UserNotification');
    }
}
