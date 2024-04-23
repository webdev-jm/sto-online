<?php

namespace App\Models;

use App\Models\OrganizationalStructure;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Spatie\Permission\Traits\HasRoles;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    use SoftDeletes;

    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'company_id',
        'department_id',
        'position_id',
        'appraisal_type_id',
        'name',
        'email',
        'username',
        'password',
        'status',
        'dark_mode',
        'profile_picture_url',
        'user_signature_url',
        'last_activity_time',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getAuthIdentifierName() {
        return 'username';
    }

    public function adminlte_profile_url() {
        return '/profile';
    }

    public function adminlte_image() {
        return !empty($this->profile_picture_url) ? $this->profile_picture_url.'-small.jpg' : '/images/user2-160x160.jpg';
    }

    public function adminlte_desc() {
        return implode(', ', $this->getRoleNames()->toArray());
    }

    public function accounts() {
        return $this->belongsToMany('App\Models\Account', 'account_user', 'user_id', 'account_id');
    }

    public function account_branches() {
        return $this->belongsToMany('App\Models\AccountBranch');
    }

    public function account() {
        return $this->belongsTo('App\Models\Account');
    }
}
