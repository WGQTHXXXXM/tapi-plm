<?php

namespace App\Providers;

use App\ServiceFactory\ZipkinContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use App\Providers\Dingtalk\SecondRedisStore;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Cache::extend('secondredis', function ($app) {
            $config = $app['config']['cache.stores.secondredis'];
            $prefix = $config['prefix'] ?? $app['config']['cache.prefix'];
            $connection = $config['connection'] ?? 'default';
            return Cache::repository(new SecondRedisStore($app['redis'], $prefix, $connection));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        app('Dingo\Api\Exception\Handler')->register(function (\LogicException $exception) {
            $httpException = new \Symfony\Component\HttpKernel\Exception\HttpException(400, $exception->getMessage(), $exception, [], $exception->getCode());
            return app('Dingo\Api\Exception\Handler')->handle($httpException);
        });
        //拦截422状态码
        \API::error(function (\Dingo\Api\Exception\ValidationHttpException $exception){
            $errorMes =$exception->getErrors();
            abort(422,$errorMes->first());
        });

        \API::error(function (\Illuminate\Auth\AuthenticationException $exception){
            abort(401);
        });
        $this->app->singleton(ZipkinContext::class, function () {
            return new ZipkinContext();
        });
    }
}
