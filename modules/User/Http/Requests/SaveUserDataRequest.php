<?php
namespace Modules\User\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;

class SaveUserDataRequest extends Request
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
        $validdation =[]; 
        if ($this->route()->getName() === 'admin.member.store' || $this->route()->getName() === 'admin.member.update')  {
            $validdation =  [
                'first_name' => 'nullable',
                'last_name' => 'nullable',
                'fullname' => 'required',
                'email' => 'required|email|'. $this->emailUniqueRule(),
                'phone' => 'required|min:10',
                'dob' =>  'required',
                'state' => 'required',
                'city' => 'required',
                'area_type' => 'required|in:urban,rural',
                'taluka' => 'required',
                'village' => 'required_if:area_type,rural',
                'address_1' => 'required',
                'address_2' => 'required',
                'roles' => 'required',
            ];
            if(request()->has('existing_profile') && request('existing_profile') == ''){
                $validdation['profile'] = 'required|image|mimes:jpeg,png,jpg,webp';
            }
        } else {
            $validdation = [
                'first_name' => 'nullable',
                'last_name' => 'nullable',                
                'password' => 'nullable|confirmed|min:6',
                'phone' => ['required|min:10'],
                'dob' =>  ['required'],
                'whatsapp_mobile' =>  ['required'],
                'address' => ['required'],
                'city' => ['required'],
                'category' => ['required'],
                'education' => ['required'],
                'religion' => ['required'],
                'cast' => ['required'],
                'gender' => ['required'],
                'country' => ['required'],
                'state' => ['required'],
            ];
            if(!request()->has('photo_hidden')){
                $validdation['photo'] = 'required|image|mimes:jpeg,png,jpg,gif,svg';
            }            
        }
        return $validdation;
    }


    private function emailUniqueRule()
    {
        $rule = Rule::unique('users');

        if ($this->route()->getName() === 'admin.users.update') {
            $userId = $this->route()->parameter('id');

            return $rule->ignore($userId);
        } else if ($this->route()->getName() === 'admin.member.update') {
            $userId = $this->route()->parameter('id');

            return $rule->ignore($userId);
        }
        return $rule;
    }
}
