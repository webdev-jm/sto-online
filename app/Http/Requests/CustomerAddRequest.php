<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerAddRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('customer create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'salesman_id' => [
                'required'
            ],
            'channel_id' => [
                'max:20'
            ],
            'code' => [
                'required'
            ],
            'name' => [
                'required'
            ],
            'address' => [
                'max:255'
            ],
            'street' => [
                'required'
            ],
            'barangay' => [
                'required'
            ],
            'city' => [
                'required'
            ],
            'province' => [
                'required'
            ],
            'postal_code' => [
                'max:255'
            ]
        ];
    }
}
