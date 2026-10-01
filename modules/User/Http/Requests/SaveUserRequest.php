<?php

namespace Modules\User\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;

class SaveUserRequest extends Request
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
        $commonRules = [
            'fullname' => 'required',
            'email' => ['required', 'email'],
            'phone' => ['nullable'],
            'roles' => ['required', Rule::exists('roles', 'id')],
            'state' => ['nullable'],
            'city' => ['nullable'],
            'area_type' => ['nullable', 'in:urban,rural'],
            'taluka' => ['nullable'],
            'village' => ['nullable'],
            'address_1' => ['nullable'],
            'address_2' => ['nullable'],
            'zip' => ['nullable'],
        ];

        if ($this->route()->getName() == 'admin.users.update') {
            return array_merge($commonRules, [
                'password' => 'nullable|confirmed|min:6',
            ]);
        } else {
            return array_merge($commonRules, [
                'password' => 'required|confirmed|min:6',
            ]);
        }
    }


    private function emailUniqueRule()
    {
        $rule = Rule::unique('users');

        if ($this->route()->getName() === 'admin.users.update') {
            $userId = $this->route()->parameter('id');

            return $rule->ignore($userId);
        }

        return $rule;
    }
}
