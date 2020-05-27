<?php
namespace App\Providers\Dingtalk;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Illuminate\Support\Facades\Cache;

class CacheServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['cache'] = function ($app) {
            return Cache::store('secondredis');
        };
    }

}
