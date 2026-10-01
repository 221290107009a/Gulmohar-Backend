<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Database\factories\OtpVerificationFactory;

class OtpVerification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'email', 'code'];
    
    protected static function newFactory(): OtpVerificationFactory
    {
        //return OtpVerificationFactory::new();
    }
}
