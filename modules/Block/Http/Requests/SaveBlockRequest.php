<?php

namespace Modules\Block\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Block\Entities\Block;
use Modules\Core\Http\Requests\Request;

class SaveBlockRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var array
     */
    protected $availableAttributes = 'block::attributes';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'content' => 'required',
            'identifier' => 'required',
            'is_active' => 'required|boolean',
        ];
    }
}
