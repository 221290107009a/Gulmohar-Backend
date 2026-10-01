<?php

namespace Modules\Country\Providers;

use Modules\Country\Admin\CountryTabs;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Country\Http\Controllers\CountryController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class CountryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        TabManager::register('countries', CountryTabs::class);

        // $this->addAdminAssets('admin.countries.(create|edit)', [
        //     'admin.media.css', 'admin.media.js', 'admin.country.js',
        // ]);

        $this->registerCountryRoute();
    }

    private function registerCountryRoute()
    {
        /*$this->app->booted(function () {
            Route::get('{slug}', [CountryController::class, 'show'])
                ->prefix(LaravelLocalization::setLocale())
                ->middleware(['localize', 'locale_session_redirect', 'localization_redirect', 'web'])
                ->name('countries.show');
        });*/
    }
}
