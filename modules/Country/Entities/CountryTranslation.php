<?php
namespace Modules\Country\Entities;

use Modules\Support\Eloquent\TranslationModel;

class CountryTranslation extends TranslationModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'body'];
}
