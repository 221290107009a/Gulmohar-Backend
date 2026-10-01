<?php

namespace Modules\User\Entities;

use Modules\Support\Eloquent\Model;
use Modules\Service\Entities\Service;

class UserService extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_services';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'service_id',
        'is_enabled',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the user that owns the service.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the service.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
