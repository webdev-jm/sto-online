<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Account;

class AccountAddRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('account create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $table = (new Account)->getTable();
        return [
            'sms_account_id' => [
                'required',
                Rule::unique($table)
            ],
            'account_code' => [
                'required',
                Rule::unique($table)
            ],
            'account_name' => [
                'required',
            ],
            'short_name' => [
                'required'
            ],
            'password' => [
                'required',
                'confirmed'
            ]
        ];
    }
}
