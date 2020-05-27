<?php
namespace App\Providers\Dingtalk;

use Illuminate\Cache\RedisStore as LaravelRedisStore;

class SecondRedisStore extends LaravelRedisStore
{
     /**
      * Store an item in the cache for a given number of minutes.
      *
      * @param  string  $key
      * @param  mixed   $value
      * @param  float|int  $seconds
      * @return void
      */
      public function put($key, $value, $seconds)
      {
          $this->connection()->setex(
              $this->prefix.$key, (int) max(1, $seconds), $this->serialize($value)
          );
      }

      /**
       * Store an item in the cache if the key doesn't exist.
       *
       * @param  string  $key
       * @param  mixed   $value
       * @param  float|int  $seconds
       * @return bool
       */
      public function add($key, $value, $seconds)
      {
          $lua = "return redis.call('exists',KEYS[1])<1 and redis.call('setex',KEYS[1],ARGV[2],ARGV[1])";

          return (bool) $this->connection()->eval(
              $lua, 1, $this->prefix.$key, $this->serialize($value), (int) max(1, $seconds)
          );
      }
}

