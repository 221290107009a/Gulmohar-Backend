<?php

namespace Modules\Block\Providers;

use Modules\Block\Admin\BlockTabs;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Block\Http\Controllers\BlockController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;


class BlockServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        TabManager::register('blocks', BlockTabs::class);

        // $this->addAdminAssets('admin.blocks.(create|edit)', [
        //     'admin.media.css', 'admin.media.js', 'admin.block.js',
        // ]);

        $this->registerBlockRoute();
    }

    private function registerBlockRoute()
    {
      
    }
}
