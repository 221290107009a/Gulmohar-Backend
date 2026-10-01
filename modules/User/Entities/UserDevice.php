<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $table = 'user_devices';

    protected $fillable = [
        'user_id',
        'unique_device_id',
        'device_name',
        'device_model',
        'device_brand',
        'device_manufacturer',
        'os_name',
        'os_version',
        'app_version',
        'app_build',
        'network_ip',
        'network_state',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
