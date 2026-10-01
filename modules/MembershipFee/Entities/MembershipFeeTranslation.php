<?php

namespace Modules\MembershipFee\Entities;

use Modules\Support\Eloquent\TranslationModel;

class MembershipFeeTranslation extends TranslationModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
}
