<?php

namespace Modules\EmailTemplate\Providers;

use Modules\EmailTemplate\Admin\EmailTemplateTabs;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Admin\Ui\Facades\TabManager;

class EmailTemplateServiceProvider extends ServiceProvider
{
    
    /**
     * Bootstrap any application emailtemplates.
     *
     * @return void
     */
    public function boot()
    {
        TabManager::register('emailtemplates', EmailTemplateTabs::class);
    }

}
