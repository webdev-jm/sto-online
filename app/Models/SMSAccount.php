<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SMSAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'accounts';
    protected $connection = 'sms_db';

    public function branches() {
        return $this->hasMany(\App\Models\SMSBranch::class, 'account_id', 'id');
    }

    public function users() {
        return $this->belongsToMany(\App\Models\User::class, 'sto_online_db.account_user', 'account_id', 'user_id');
    }

    public function account_branches() {
        return $this->hasMany(\App\Models\AccountBranch::class, 'id', 'account_id');
    }

    public function products() {
        return $this->belongsToMany('App\Models\SMSProduct', 'sms_db.account_product', 'account_id', 'product_id');
    }

    public function company() {
        return $this->belongsTo('App\Models\SMSCompany', 'company_id', 'id');
    }

    public function scopeAccountAjax($query, $search) {
        if($search == '') {
            $accounts = $query->select('id', 'account_code', 'short_name')->limit(5)->get();
        } else {
            $accounts = $query->select('id', 'account_code', 'short_name')
            ->where('account_code', 'like', '%'.$search.'%')
            ->orWhere('short_name', 'like', '%'.$search.'%')
            ->limit(5)->get();
        }

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
