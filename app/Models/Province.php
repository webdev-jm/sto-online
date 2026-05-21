<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'region_id',
        'province_name',
    ];

    public function municipalities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\Municipality');
    }

    public function region(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Region');
    }

    public static function resolveCanonicalName(string $rawName): ?string
    {
        return static::whereFullText('province_name', $rawName)
            ->value('province_name');
    }
}
