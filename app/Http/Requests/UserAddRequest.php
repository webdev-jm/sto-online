<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class UserAddRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('user create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'min:2'
            ],
            'email' => [
                'required',
                'email',
                Rule::unique((new User)->getTable())
            ],
            'username' => [
                'required',
                Rule::unique((new User)->getTable())
            ],
            'password' => [
                'required',
                'confirmed'
            ],
            'role_ids' => [
                'required'
            ],
        ];
    }
}
