<?php

namespace Modules\Service\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Service\Entities\Service;
use Modules\Core\Http\Requests\Request;

class SaveServiceRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'service::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required'],
            'link_url' => ['required'],
            'webpage_url' => ['nullable'],
            'gradient_colors' => ['required'],
            'files.logo' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The name is required.',
            'link_url.required' => 'The link url is required.',
            'gradient_colors.required' => 'The gradient colors is required.',
            'files.logo.required' => 'The icon is required.',
        ];
    }
}
