<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Account;

class AccountEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('account edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = decrypt($this->id);
        $table = (new Account)->getTable();

        return [
            'sms_account_id' => [
                'required',
                Rule::unique($table)->ignore($id)
            ],
            'account_code' => [
                'required',
                Rule::unique($table)->ignore($id)
            ],
            'account_name' => [
                'required',
            ],
            'short_name' => [
                'required',
            ],
            'password' => [
                'sometimes',
                'nullable',
                'confirmed',
            ]
        ];
    }
}
