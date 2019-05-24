<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-20
 * Time: 10:21
 */

namespace App\Providers;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    public function register()
    {
        Repository::macro('ttl', function ($key) {
            $driver = Cache::getDefaultDriver();
            if ($driver === 'redis') {
                $store =  Repository::getStore();
                $prefix = $store->getPrefix();
                return Redis::ttl($prefix . $key);
            } else {
                throw new \Exception('un support implement ttl func:' . $driver);
            }
        });
    }
    
    
    public function isDeferred()
    {
        return true;
    }
}