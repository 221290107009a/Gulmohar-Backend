<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {        
        $this->app['request']->segment(1) === 'api' ? 
            config(['auth.defaults.guard' => 'api']) : 
            config(['auth.defaults.guard' => 'web']);
    }

    public function register()
    {
     
    }
}
