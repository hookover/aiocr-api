<?php

namespace App\Http\Controllers\V2;

use App\Exceptions\ApiStatusException;
use App\Repositories\FileRepository;
use Illuminate\Http\Request;

class ReportController extends BaseController
{
    /**
     * @api {get} /v2/report?token=:token 报错
     * @apiDescription 根据上传文件ID报错，报错后返还积分，15分钟内可报错
     * @apiGroup Decode Interface
     * @apiPermission JWT TOKEN
     * @apiParam {String} id  图片ID
     * @apiVersion 0.2.0
     * @apiExample {curl} curl示例:
     *     curl -d "id=15097995384666600001356001" http://api.jiqishibie.com/v2/decode/result?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9
     * @apiSuccessExample {json} Success-Response(成功响应 http状态码：200):
     * {
     *   "message": "success",
     *   "status_code": 200,    #报错成功
     *   "data": null
     * }
     *
     */
    public function report(Request $request)
    {
        $this->validate(
            $request,
            ['id' => 'required|string|min:26|max:26',]
        );

        //todo 看看软件是否已关闭报错

        $id = $request->input('id');
        //防止重复调用，放到缓存。先看缓存有没有

        $cache = FileRepository::reportCache($id);
        if($cache) {
            //不能重复报错
            throw new ApiStatusException(ApiStatusException::STATUS_FILE_REPORT_FAIL_REPLAY_CANT);
        }

        $status = FileRepository::report($id, $request->user()->user_id);

        if($status) {
            return $this->responseSuccess('success');
        }
        return $this->responseError('数据更新异常，请联系客服~');
    }
}
