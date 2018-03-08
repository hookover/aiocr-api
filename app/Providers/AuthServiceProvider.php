<?php

namespace App\Providers;

use App\Exceptions\ApiException;
use App\Exceptions\ApiStatusException;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->input('token')) {
                /*
                 * 使用数据库中的api_token字段进行验证
                 */
//                return UserRepository::findByToken($request->input('token'));

                /*
                 * 使用jwt token验证
                 */
                return UserRepository::findByJWTToken($request->input('token'));
            }
            throw new ApiStatusException(ApiException::STATUS_TOKEN_FAIL_PARAM_NOT_FOUND);
        });
    }
}
