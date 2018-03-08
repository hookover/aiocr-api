<?php

namespace App\Http\Controllers\V2;

use App\Exceptions\ApiException;
use App\Exceptions\ApiStatusException;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public function getCurrentClassNameCacheKey($key)
    {
        return str_replace('\\', '.', get_class($this)) . '.' . $key;
    }

    // 返回错误的请求
    protected function errorBadRequest($validator)
    {
        // github like error messages
        // if you don't like this you can use code bellow
        //
        //throw new ValidationHttpException($validator->errors());

        $result   = [];
        $messages = $validator->errors()->toArray();

        if ($messages) {
            foreach ($messages as $field => $errors) {
                foreach ($errors as $error) {
                    $result[] = [
                        'field' => $field,
                        'error' => $error,
                    ];
                }
            }
        }

        //参数验证失败
        throw new ApiStatusException(ApiException::STATUS_PARAMS_FAIL_VERIFICATION);
    }

    public function responseSuccess($message = 'success', array $data = null, $status_code = 200, $http_code = 200)
    {

        return response()->json([
            'status_code' => $status_code,
            'message'     => $message,
            'data'        => $data,
        ]);
    }

    public function responseError($message = "error", $status_code = 500, $http_code = 500)
    {
        return response()->json([
            'status_code' => $status_code,
            'message'     => $message,
        ], $http_code);
    }
}
