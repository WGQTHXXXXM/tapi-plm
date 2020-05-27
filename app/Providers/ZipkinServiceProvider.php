<?php

namespace App\Providers;

use App\ServiceFactory\ZipkinContext;
use Illuminate\Support\ServiceProvider;

class ZipkinServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ZipkinContext::class, function () {
            return new ZipkinContext();
        });
    }
}
