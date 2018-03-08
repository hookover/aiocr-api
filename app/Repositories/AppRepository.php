<?php
/**
 * Created by PhpStorm.
 * User: hookover
 * Date: 17-10-16
 * Time: 下午3:38
 */

namespace App\Repositories;

use App\Models\App;

class AppRepository extends BaseRepository
{
    protected function retrieveByAppKey($app_key)
    {
        //缓存有么？
        $app = \Cache::get($this->getEnvCacheKEY($app_key, 'CACHE_KEY_APP_KEY'));
        if ($app) {
            return $app != "\0" ? unserialize($app) : false;
        }

        $app = App::select(["app_id", "app_key", "developer_id"])
            ->where(['app_key' => $app_key, 'status' => 1])
            ->first();

        if ($app) {
            \Cache::put($this->getEnvCacheKEY($app_key, 'CACHE_KEY_APP_KEY'), serialize($app), env('CACHE_APP_CACHE_TTL', 120));

            return $app;
        }

        //如果app不存在，就放存个空key
        \Cache::put($this->getEnvCacheKEY($app_key, 'CACHE_KEY_APP_KEY'), "\0", env('CACHE_APP_CACHE_TTL', 120));
        return false;
    }
}