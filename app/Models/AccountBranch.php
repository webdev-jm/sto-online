<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Str;

class AccountBranch extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'bevi_area_id',
        'code',
        'name',
        'branch_token',
    ];

    public function generateBranchToken() {
        do {
            $token = Str::random(60);
        } while ($this->where('branch_token', $token)->exists());

        $this->branch_token = $token;
        $this->save();

        return $token;
    }

    public static function findByToken($token) {
        return self::where('branch_token', $token)->first();
    }

    public function account() {
        return $this->belongsTo('App\Models\Account', 'account_id', 'id');
    }

    public function users() {
        return $this->belongsToMany('App\Models\User');
    }

    public function bevi_area() {
        return $this->belongsTo('App\Models\BeviArea');
    }
}