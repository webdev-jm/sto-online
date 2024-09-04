<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserNotification extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'notification_id',
        'frequency_id',
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function notification() {
        return $this->belongsTo('App\Models\Notification');
    }

    public function frequency() {
        return $this->belongsTo('App\Models\Frequency');
    }
}
