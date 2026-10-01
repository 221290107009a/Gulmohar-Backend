<?php

namespace Modules\Country\Http\Controllers\Admin;

use Modules\Country\Entities\Country;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Country\Http\Requests\SaveCountryRequest;

class CountryController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Country::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'country::countries.country';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'country::admin.countries';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected $validation = SaveCountryRequest::class;
}
