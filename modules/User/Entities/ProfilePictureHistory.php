<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Database\factories\ProfilePictureHistoryFactory;

class ProfilePictureHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'image_url', 'active_profile'];
    
    protected static function newFactory(): ProfilePictureHistoryFactory
    {

    }
}
