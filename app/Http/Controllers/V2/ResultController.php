<?php

namespace App\Http\Controllers\V2;

use App\Exceptions\ApiException;
use App\Exceptions\ApiStatusException;
use App\Repositories\FileRepository;
use Illuminate\Http\Request;

class ResultController extends BaseController
{
    /**
     * @api {get} /v2/decode/result?token=:token 获取识别结果
     * @apiDescription 根据图片ID获取图片的识别结果
     * @apiGroup Decode Interface
     * @apiPermission JWT TOKEN
     * @apiParam {String} id  图片ID
     * @apiVersion 0.2.0
     * @apiExample {curl} curl示例:
     *     curl -d "id=15097995384666600001356001" http://api.jiqishibie.com/v2/decode/result?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9
     * @apiSuccessExample {json} Success-Response(成功响应 http状态码：200):
     * {
     *   "message": "success",
     *   "status_code": 200,
     *   "data": {
     *      "id": "15097995384666600001356001",
     *      "result": "good"    #若为空表示识别中或ID不存在
     *   }
     * }
     *
     */

    public function result(Request $request)
    {
        $this->validate(
            $request,
            ['id' => 'required|string|min:26|max:26',]
        );

        $id = $request->input('id');

        //有缓存
        $result = FileRepository::cacheDecodeResult($id);
        if($result) {
            if(!$result || $result == "\0") {$result = null;}
            return $this->responseSuccess('success', ['id' => $id, 'result' => $result]);
        }
        //没缓存就查库
        $result = FileRepository::getResult($id, $request->user()->user_id);
        if (!$result) {
            $cache_data = ($request === false) ? "\0" : "\0";
            FileRepository::cacheDecodeResult($id, $cache_data);
            if($result === false) {
                //文件id不存在
                throw new ApiStatusException(ApiException::STATUS_FILE_NOT_FOUND_ID);
            }
        } else {
            FileRepository::cacheDecodeResult($id, $result);
        }

        return $this->responseSuccess('success', ['id' => $id, 'result' => $result]);
    }
}
