<?php

namespace App\Http\Controllers\V2;

use App\Exceptions\ApiException;
use App\Exceptions\ApiStatusException;
use App\Repositories\UserRepository;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    public function loginResponseUserToken(Request $request)
    {
        $email    = $request->input('email');
        $password = $request->input('password');

        $this->validate(
            $request,
            [
                'email'    => 'required|string|email|max:64',
                'password' => 'required|string|min:6',
            ]
        );

        if ($user = UserRepository::findCacheByEmail($email)) {
            if (app('hash')->check($password, $user->password)) {
                $data = [
                    "token"            => $user->api_token . $user->user_id,
                    "token_created_at" => $user->api_token_created_at,
                ];

                return $this->responseSuccess('登录成功', $data);
            }
        }

        //邮箱账户或密码错误，请重试
        throw new ApiStatusException(ApiException::STATUS_LOGIN_FAILED_ACCOUNT_OR_PASSWORD);
    }

    /**
     * @api {post} /v2/user/login 登录
     * @apiDescription
     * 登录系统获取与服务器通信的token
     *
     * 每次登录都将获取一个新的token，并且之前获取的token在其过期之前都可以使用
     *
     * 若想紧急停止当前账户所有已生成的token，请到后台修改密码或更新个人安全密钥
     *
     * @apiGroup User Interface
     * @apiPermission None(无)
     * @apiParam {String} email  邮箱地址（必填）
     * @apiParam {String} password 密码（必填）
     * @apiParam {Datetime} expired_at 过期时间（最小24小时,最多30天后，如：2015-11-11 12:00:00）
     * @apiVersion 0.2.0
     * @apiExample {curl} curl示例
     *     curl -d "email=admin@example.com&password=123456" http://api.jiqishibie.com/v2/user/login
     * @apiSuccessExample {json} Success-Response(成功响应 http状态码：200):
     * {
     *      "data": {
     *          "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodCJwcnYiOiIyMhMjI0ZmU4ZjcwZTVjMzAifQ.vwdPRYi06EPxDF2sT3rEVSnmbEoKTwAegcg0BaZ0sIQ",
     *          "token_expired_at": "2015-11-02 17:08:47",            #token过期时间，默认过期时间为15天
     *      }
     *      "status_code": 200,
     *      "message": ""
     * }
     * @apiErrorExample {json} Error-Response(错误响应 http状态码非200):
     * {
     *      "message": "邮箱或密码错误。",
     *      "status_code": 401,
     * }
     *
     */
    public function loginResponseJWTToken(Request $request)
    {
        $email      = $request->input('email');
        $password   = $request->input('password');
        $expired_at = $request->input('expired_at');

        $this->validate(
            $request,
            [
                'email'    => 'required|string|email|max:64',
                'password' => 'required|string|min:6',
                'expired_at'=>'nullable|date'
            ]
        );

        if($expired_at) {
            if(strtotime($expired_at) > time() * 24 * 3600 * 30) {
                //token过期时间不能超过最大限制
                throw new ApiStatusException(ApiException::STATUS_TOKEN_EXPIRED_OVER_MAX_DAY);
            }

            if(strtotime($expired_at) < time() * 24 * 3600) {
                //token过期时间不能超最小限制
                throw new ApiStatusException(ApiException::STATUS_TOKEN_EXPIRED_LOWER_MIN_DAY);
            }
        }


        if ($user = UserRepository::findCacheByEmail($email)) {
            if (app('sha')->check($password, $user->password)) {
                $key = $user->salt;
                if (!$key) {
                    //用户KEY不能为空
                    throw new ApiStatusException(ApiException::STATUS_LOGIN_FAILED_ACCOUNT_OR_PASSWORD);

                }


                $token_expired_at = time() + env('JWT_EXPIRED', 604800);

                $payload = [
                    "id"  => $user->user_id,
                    "exp" => $token_expired_at,

                ];

                $token = JWT::encode($payload, $key);

                $data = [
                    "token"            => $token,
                    "token_expired_at" => date('Y-m-d H:i:s', $token_expired_at),
                ];


                return $this->responseSuccess('登录成功', $data);
            }
        }

        //邮箱账户或密码错误
        throw new ApiStatusException(ApiException::STATUS_LOGIN_FAILED_ACCOUNT_OR_PASSWORD);

    }


    /**
     * @api {get} /v2/user/point?token=:token 获取用戶积分
     * @apiDescription 获取用戶积分（2~5分钟缓存）
     * @apiGroup User Interface
     * @apiPermission JWT TOKEN
     * @apiVersion 0.2.0
     * @apiExample {curl} curl示例:
     *     curl -i http://api.jiqishibie.com/v2/user/point?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTk4NjkxOCwiZXhwIjoxNTEwNzYxNzE0fQ.v79tOJ5Ji_O4pqB03JBzFs-J2W3LLABYEw0MpCY1LCk
     *
     * @apiSuccessExample {json} Success-Response(成功响应 http状态码：200):
     *   {
     *      "data": {
     *          "point": 10000,
     *      }
     *      "status_code": 200,
     *      "message": "success"
     *   }
     */
    public function point()
    {
        $user = Auth::user();
        if (!$user) {
            //帐户异常，请联系客服
            throw new ApiStatusException(ApiException::STATUS_USER_NOT_FOUND);

        }

        return $this->responseSuccess('积分获取成功', [
            'point' => $user->point_pay_current,
        ]);
    }
}
