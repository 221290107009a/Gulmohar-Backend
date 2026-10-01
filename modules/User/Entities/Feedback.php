<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'image',
        'status',
        'app_version',
        'os_version',
        'device_model',
        'network_type',
        'current_route',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
