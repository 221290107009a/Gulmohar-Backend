<?php

namespace Modules\User\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use App\Modules\User\Models\User;

class UpdateProfileRequest extends Request
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
        $rules = [
            'phone' => ['required'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'dob' => ['required'],
            'whatsapp_mobile' => ['required'],
            'address' => ['required'],
            'city' => ['required'],
            'category' => ['required'],
            'education' => ['required'],
            'religion' => ['required'],
            'cast' => ['required'],
            'gender' => ['required'],
            'password' => ['nullable', 'confirmed', 'min:4']
        ];
    
        if ($this->hasFile('photo')) {
            $rules['photo'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024';
        }
        return $rules;

    }


    /**
     * Hash the user password against the bcrypt algorithm.
     *
     * @return $this|null
     */
    public function bcryptPassword()
    {
        if ($this->filled('password')) {
            return $this->merge(['password' => bcrypt($this->password)]);
        }

        unset($this['password']);
    }
    private function emailUniqueRule()
    {
        $rule = Rule::unique('users');
        return $rule->ignore(auth()->user()->id);
    }
}
