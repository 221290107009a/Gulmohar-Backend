<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class ReferralUsage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'referral_code',
        'referrer_id',
        'referred_user_id',
        'reward_point',
        'referral_share_type',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'reward_point' => 'decimal:2',
    ];

    /**
     * Get the referrer user associated with the referral usage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the referred user associated with the referral usage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
