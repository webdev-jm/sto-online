<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelMapping extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'channel_id',
        'external_channel_code',
        'external_channel_name',
    ];

    public function account()
    {
        return $this->belongsTo('App\Models\Account');
    }

    public function channel() {
        return $this->belongsTo('App\Models\Channel', 'channel_id', 'id');
    }
}
