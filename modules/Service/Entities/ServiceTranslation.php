<?php

namespace Modules\Service\Entities;

use Modules\Support\Eloquent\TranslationModel;

class ServiceTranslation extends TranslationModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','description'];
}
