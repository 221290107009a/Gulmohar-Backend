<?php

namespace Modules\User\Http\Requests;

use Modules\Core\Http\Requests\Request;
use Modules\Support\Rules\GoogleRecaptcha;

class RegisterRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'user::attributes.users';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email'],
            'phone' => ['required'],
            'password' => ['required', 'min:4'],
            'privacy_policy' => ['required'],            
            'dob' =>  ['required'],
            'whatsapp_mobile' =>  ['required'],
            'address' => ['required'],
            'city' => ['required'],
            'category' => ['required'],
            'education' => ['required'],
            'religion' => ['required'],
            'cast' => ['required'],
            'gender' => ['required'],            
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return array_merge(parent::messages(), [
            'g-recaptcha-response.required' => trans('support::recaptcha.validation.failed_to_verify'),
        ]);
    }
}
