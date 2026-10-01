<?php

namespace Modules\Seller\Entities;

use Modules\Support\Eloquent\TranslationModel;

class SellerTranslation extends TranslationModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['shop_name', 'owner_name', 'description', 'address1', 'category', 'address2', 'city', 'state'];
}

