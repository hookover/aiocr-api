<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return 'api 2.0';
});


$router->group(['prefix' => 'v2', 'namespace' => '\App\Http\Controllers\V2'], function () use ($router) {

    $router->get('/servers', ['uses' => 'ServerController@servers']);

    $router->post('/user/login', ['uses' => 'UserController@loginResponseJWTToken']);
    $router->get('/user/login', ['uses' => 'UserController@loginResponseJWTToken']);

    $router->group(['middleware' => ['auth']], function () use ($router) { // 指定 auth 的 guard 为 新建的 admins

        //获取剩余积分
        $router->get('/user/point', 'UserController@point');

        //上传识别结果
        $router->post('/decode/upload', 'UploadController@upload');
        $router->post('/decode/upload-base64', 'UploadController@uploadBase64');
        $router->get('/decode/upload-base64', 'UploadController@uploadBase64');

        //报错
        $router->post('/decode/report', 'ReportController@report');
        $router->get('/decode/report', 'ReportController@report');
        //获取识别结果
        $router->get('/decode/result', 'ResultController@result');

    });

});
