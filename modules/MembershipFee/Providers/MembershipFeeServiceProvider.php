<?php

namespace Modules\MembershipFee\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\MembershipFee\Admin\MembershipFeeTabs;

class MembershipFeeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        TabManager::register('membership_fees', MembershipFeeTabs::class);
    }
}
