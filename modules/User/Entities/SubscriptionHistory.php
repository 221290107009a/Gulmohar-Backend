<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Database\factories\SubscriptionHistoryFactory;
use Illuminate\Http\JsonResponse;
use Modules\Admin\Ui\AdminTable;

class SubscriptionHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'start_date', 'end_date', 'subscription_fees_id', 'subscription_fees_name', 'subscription_fees_duration', 'subscription_fees'];
    
    /**
     * Get table data for the resource
     *
     * @return JsonResponse
     */
    public function table()
    {
        return new AdminTable($this->newQuery());
    }
}
