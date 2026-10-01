<?php

namespace Modules\Seller\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Seller\Entities\Seller;
use Modules\Core\Http\Requests\Request;

class SaveSellerRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var array
     */
    protected $availableAttributes = 'seller::attributes';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'shop_name' => 'required',
            'owner_name' => 'required',
            'description' => 'required',
            'address1' => 'required',
            'address2' => 'required',
            'city' => 'required',
            'state' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'category' => 'required|in:all,books',
            'current_status' => 'required|in:pending,in_review,approved,rejected',
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'shop_name.required' => 'The Shop name is required.',
            'owner_name.required' => 'The Owner name is required.',
            'description.required' => 'The Description is required.',
            'address1.required' => 'The Locality / Area / Village is required.',
            'address2.required' => 'The Address is required.',
            'city.required' => 'The City is required.',
            'state.required' => 'The State is required.',
            'phone.required' => 'The Phone is required.',
            'email.required' => 'The Email is required.',
            'email.email' => 'The Email must be a valid email address.',
            'category.required' => 'The Category is required.',
            'category.in' => 'The Category must be either All Categories or Only Books.',
            'current_status.required' => 'The Status is required.',
            'current_status.in' => 'Invalid status selected.',            
        ];
    }
}