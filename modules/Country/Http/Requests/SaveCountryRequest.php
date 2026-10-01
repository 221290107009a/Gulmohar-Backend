<?php

namespace Modules\Country\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Country\Entities\Country;
use Modules\Core\Http\Requests\Request;

class SaveCountryRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var array
     */
    protected $availableAttributes = 'country::attributes';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'sort_order' => 'required|numeric',
            'is_active' => 'required|boolean',
            'code' => 'required',
        ];
    }
}
