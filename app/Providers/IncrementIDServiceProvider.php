<?php

namespace App\Providers;

use App\Services\IncrementIDService;
use Illuminate\Support\ServiceProvider;

class IncrementIDServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('increment.id', function () {
            return new IncrementIDService();
        });

        //使用bind绑定实例到接口以便依赖注入
//        $this->app->bind('App\Contracts\IncrementIDContract', function () {
//            $worker_id      = env('INCREMENT_WORKER_ID', 0);
//            $data_center_id = env('INCREMENT_DATA_CENTER_ID', 0);
//
//            return new IncrementIDService($worker_id, $data_center_id);
//        });
    }
}
