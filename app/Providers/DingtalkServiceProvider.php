<?php
namespace App\Providers;

use EasyDingTalk\Application;
use Illuminate\Support\ServiceProvider;

class DingtalkServiceProvider extends ServiceProvider
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
        $this->app->singleton(Application::class, function ($app) {
            $dingtalkApp = new Application(config('dingtalk'));
            $dingtalkApp->register(new Dingtalk\CacheServiceProvider());
            return $dingtalkApp;
        });
    }
}
