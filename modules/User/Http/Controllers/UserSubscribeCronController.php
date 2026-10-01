<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\User\Entities\SubscriptionHistory;
use Modules\User\Entities\User;
use Carbon\Carbon;

class UserSubscribeCronController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function userSubscribeCron()
    {
        $todayDate = Carbon::today();
        
        $userIds = SubscriptionHistory::whereDate('end_date', $todayDate)->pluck('user_id');
        if ($userIds->isNotEmpty()) {
            User::whereIn('id', $userIds)->update(['is_subscribe' => 0, 'subscription_history_id' => 0]);
            User::whereIn('id', $userIds)->each(function ($user) {
                $user->roles()->update(['role_id' => 2]);
            });
        }
    }
}
