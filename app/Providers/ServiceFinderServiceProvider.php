<?php

namespace App\Providers;

use App\ServiceFactory\Consul;
use Illuminate\Support\ServiceProvider;

class ServiceFinderServiceProvider extends ServiceProvider
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
        //
        $this->app->singleton(Consul::class, function ($app) {
            return new Consul();
        });
    }
}
