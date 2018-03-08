<?php

namespace App\Providers;

use App\Exceptions\ApiException;
use Illuminate\Support\ServiceProvider;

class ApiExceptionProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('api.exception', function () {
            return new ApiException('The server throws an exception.');
        });

    }
}
