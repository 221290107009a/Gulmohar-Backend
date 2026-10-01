<?php

namespace Modules\EmailTemplate\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\EmailTemplate\Entities\EmailTemplate;
use Modules\Core\Http\Requests\Request;

class SaveEmailTemplateRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var array
     */
    protected $availableAttributes = 'emailtemplate::attributes';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'sort_order' => 'required',
            'case_category_id' => 'required',
            'location' => 'required',
            'description' => 'required',
            'project' => 'required',
            'client' => 'required',
            'complete_date' => 'required',
            'facebookurl' => 'nullable|string',
            'twitterurl' => 'nullable|string',
            'googleurl' => 'nullable|string',
            'pinteresturl' => 'nullable|string',
            'linkedinurl' => 'nullable|string',
            'slug' => $this->getSlugRules(),
        ];
    }

    private function getSlugRules()
    {
        $rules = $this->route()->getName() === 'admin.emailtemplates.update'
            ? ['required']
            : ['sometimes'];

        $slug = EmailTemplate::withoutGlobalScope('active')->where('id', $this->id)->value('slug');

        $rules[] = Rule::unique('emailtemplates', 'slug')->ignore($slug, 'slug');

        return $rules;
    }
}
