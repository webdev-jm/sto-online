<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'sms_account_id',
        'account_code',
        'account_name',
        'short_name',
        'account_password',
    ];

    public function sms_account() {
        return $this->belongsTo(\App\Models\SMSAccount::class, 'sms_account_id', 'id');
    }

    public function users() {
        return $this->belongsToMany('App\Models\User');
    }

    public function db_data() {
        return $this->hasOne('\App\Models\AccountDatabase');
    }

    public function scopeAccountAjax($query, $search) {
        $accounts = $query->select('id', 'account_code', 'short_name')
            ->when(!empty($search), function($qry) use($search) {
                $qry->where('account_code', 'like', '%'.$search.'%')
                    ->orWhere('short_name', 'like', '%'.$search.'%');
            })
            ->limit(5)->get();

        $response = [];
        foreach($accounts as $account) {
            $response[] = [
                'id' => $account->id,
                'text' => '['.$account->account_code.'] '.$account->short_name
            ];
        }

        return $response;
    }
}
