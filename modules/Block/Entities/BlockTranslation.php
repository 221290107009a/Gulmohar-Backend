<?php

namespace Modules\Block\Entities;

use Modules\Support\Eloquent\TranslationModel;

class BlockTranslation extends TranslationModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'content'];
}

