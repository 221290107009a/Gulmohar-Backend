<?php

namespace Modules\Seller\Providers;

use Modules\Seller\Admin\SellerTabs;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Seller\Http\Controllers\SellerController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;


class SellerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        TabManager::register('sellers', SellerTabs::class);

        // $this->addAdminAssets('admin.sellers.(create|edit)', [
        //     'admin.media.css', 'admin.media.js', 'admin.seller.js',
        // ]);

        $this->registerSellerRoute();
    }

    private function registerSellerRoute()
    {
      
    }
}
