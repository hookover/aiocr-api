<?php

namespace App\Http\Controllers\V2;

use App\Exceptions\ApiException;
use App\Exceptions\ApiStatusException;
use App\Helpers\ImageHelper;
use App\Repositories\AppRepository;
use App\Repositories\FileRepository;
use App\Repositories\FileTypeRepository;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class UploadController extends BaseController
{
    /**
     * @api {post} /v2/decode/upload?token=:token 上传图片数据流
     * @apiDescription
     * 使用图片文件流的方式上传到服务器
     *
     * 参数编码：Content-Type: multipart/form-data
     *
     * @apiGroup Decode Interface
     * @apiPermission JWT TOKEN
     * @apiParam {String} app_key 软件KEY（默认系统key）
     * @apiParam {Integer} type_id  图片类型ID
     * @apiParam {FILE} file  二进制文件流
     * @apiVersion 0.2.0
     * @apiExample {curl} curl示例:
     *     curl -F "file=@captcha.jpg" -F "type_id=10001" http://api.jiqishibie.com/v2/decode/upload?api_token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9
     * @apiSuccessExample {json} Success-Response(成功响应 http状态码：200):
     * {
     *      "message": "ok",
     *      "status_code": 200,
     *      "data": {
     *          "id": "15097995384666600001356001"    #id长26位
     *      }
     * }
     */

    public function upload(Request $request)
    {
        $this->validate(
            $request,
            [
                'app_key' => 'string',
                'type_id' => 'integer',
                'file'    => 'required|image|mimes:jpeg,bmp,png,gif',
            ]
        );

        //上传的文件不能超过100k
        if (($request->file('file')->getSize() / 1024) > env('LIMIT_THE_IMAGE_SIZE', 100)) {
            //上传的文件不能超过限制
            throw new ApiException('上传文件不能超过' . env('LIMIT_THE_IMAGE_SIZE', 100) . 'k', ApiException::STATUS_FILE_LIMIT_OVER_SIZE);
        }

        //检查基本信息
        $base_info = $this->check($request);

        //保存图片
        $paths = ImageHelper::saveFileImage($request->file('file'));

        $id = FileRepository::saveData($base_info, $paths);


        return $this->responseSuccess('success', ['id' => $id]);
    }


    /**
     * @api {post} /v2/decode/upload-base64?token=:token 上传图片base64编码
     * @apiDescription 将图片转换为base64编码后通过此接口上传到服务器
     * @apiGroup Decode Interface
     * @apiPermission JWT TOKEN
     * @apiParam {String} app_key 软件KEY（默认系统key）
     * @apiParam {Integer} type_id 图片类型ID
     * @apiParam {String} file    图片的base64编码(必须包含类似data:image/png;base64的文件数据头)
     * @apiVersion 0.2.0
     * @apiExample {curl} curl示例:
     *     curl "type_id=100001&file=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAMAAAARCA0dGVyY4LQCQAAABFXKwAAAABJRU5ErkJggg=="
     *     http://api.jiqishibie.com/v2/decode/upload-base64?token=eyJ0eXAiOiJKV1QizI1NiJ9.eyJpc3MiOiJodHRwOi8NhcH4NzJkzAifQ.vwdP3rEVSnmbEoKTwAegcg0BaZ0sIQ
     * @apiSuccessExample {json} Success-Response(成功响应 http状态码：200):
     * {
     *      "message": "success",
     *      "status_code": 200,
     *      "data": {
     *          "id": "15097995384666600001356001"    #id长26位
     *      }
     * }
     */

    public function uploadBase64(Request $request)
    {
        $this->validate(
            $request,
            [
                'app_key' => 'string',
                'file'    => 'required|string|regex:/^(data:\s*image\/(\w+);base64,)/',
                'type_id' => 'integer',
            ]
        );


        $file = $request->input('file');

        //检查 图片大小 不能超过100k
        if ((strlen($file) / 1024) > env('LIMIT_THE_IMAGE_SIZE', 100)) {
            throw new ApiException('上传的文件不能超过' . env('LIMIT_THE_IMAGE_SIZE', 100) . 'k', ApiException::STATUS_FILE_LIMIT_OVER_SIZE);
        }

        //检查 图片数据的类型是否正确
        $file_data = ImageHelper::decodeBase64Image($file);

        //检查基本信息
        $info = $this->check($request);

        //保存图片文件
        $paths = ImageHelper::saveBase64Image($file_data['data'], $file_data['extension']);


        $id = FileRepository::saveData($info, $paths);

//        return '@@@' . $id;

        return $this->responseSuccess('success', ['id' => $id]);
    }

    protected function check(Request $request)
    {
        $app_key = $request->input('app_key') ? $request->input('app_key') : env('DEFAULT_APP_KEY');
        $type_id = $request->input('type_id') ? $request->input('type_id') : env('DEFAULT_FILE_TYPE_ID');


        //todo 检查用户 是否绑定地区、绑定app
        //todo 检查用户 是否限制每日最多使用积分
        //todo 检查用户 是否启用异常使用服务

        //检查app_key 是否正确
        $app = AppRepository::retrieveByAppKey($app_key);
        if (!$app) {
            //app key已被禁用或不正确
            throw new ApiStatusException(ApiException::STATUS_APP_FAIL_NOT_FOUND_OR_DISABLED);
        }

        //检查文件类型ID是否正确
        $file_type = FileTypeRepository::retrieveByFileTypeID($type_id);
        if (!$file_type) {

            //文件类型id已被禁用或不正确
            throw new ApiStatusException(ApiException::STATUS_FILE_TYPE_ID_FAIL_DISABLED_OR_NOT_FOUND);
        }

        $user = $request->user();

        //检查用户 账户积分是否够用
        if ($user->point_pay_current <= 0) {
            //帐户余额不足，请及时充值
            throw new ApiStatusException(ApiException::STATUS_USER_NOT_HAVE_POINT);
        }


        return [
            'app'       => $app,
            'file_type' => $file_type,
            'user_id'   => $user->user_id,
            'ip'        => $request->getClientIp(),
        ];
    }
}
