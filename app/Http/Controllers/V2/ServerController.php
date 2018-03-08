<?php

namespace App\Http\Controllers\V2;

use App\Models\ServerId;

class ServerController extends BaseController
{
    /**
     * @api {get} /v2/servers 可用服务器列表
     * @apiDescription 获取全部服务器列表地址
     * @apiGroup Decode Interface
     * @apiPermission None
     * @apiVersion 0.2.0
     * @apiExample {curl} curl示例:
     *     curl http://api.jiqishibie.com/v2/servers
     * @apiSuccessExample {json} Success-Response(成功响应 http状态码：200):
     * {
     *   "message": "success",
     *   "status_code": 200,    #状态码200即为正确
     *   "data": [
     *       {
     *           type: 1,       #1通用型，2登录服务器，3上传服务器，4获取结果服务器，5报错服务器
     *           url: "http//api.captcha.com",
     *           weight: 100
     *       },
     *       {
     *           type: 2,
     *           url: "http//api.captcha.com",
     *           weight: 100
     *       }
     *    ]
     * }
     *
     */
    public function servers()
    {
        $data = \Cache::store(env('CACHE_KEY_SERVERS_DRIVER','file'))
            ->remember(env('CACHE_KEY_SERVERS', 'CACHE_KEY_SERVERS'), env('CACHE_KEY_SERVERS_TTL', 5), function (){
                return ServerId::select(['server_type as type','server_api_url as url','server_api_weight as weight'])
                    ->where([
                        ['status', '=', ServerId::STATUS_ENABLED]
                    ])->get();
            });
        if($data) {
            $data = $data->toArray();
        }
        return $this->responseSuccess('success', $data);
    }
}
