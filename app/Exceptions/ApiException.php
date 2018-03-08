<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    public $status_code = 500;
    public $http_code   = 500;


    public function __construct($message, $status_code = 500, $http_code = 500)
    {
        parent::__construct($message);

        $this->http_code = $http_code;

        $this->status_code = $status_code;
    }

    public function status($status)
    {
        $this->status = $status;

        return $this;
    }

    public function httpCode($http_code)
    {
        $this->http_code = $http_code;

        return $this;
    }

    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    public function response($message, $status_code = 500, $http_code = 500)
    {

        $this->http_code = $http_code;

        $this->status_code = $status_code;

        $this->message = $message;

        return $this;
    }


    public function getResponse()
    {
        return response()->json([
            'status_code' => $this->status_code,
            'message'     => $this->getMessage(),
        ], $this->http_code);
    }


    const STATUS_LOGIN_FAILED                     = 1001;//登录失败
    const STATUS_LOGIN_FAILED_PASSWORD            = 1002;//登录失败密码错误
    const STATUS_LOGIN_FAILED_ACCOUNT             = 1003;//登录失败用户名错误
    const STATUS_LOGIN_FAILED_ACCOUNT_OR_PASSWORD = 1004;//登录失败用户名或密码错误

    const STATUS_TOKEN_FAIL                 = 1501; //token错误
    const STATUS_TOKEN_FAIL_FORMAT          = 1502; //token格式不正确
    const STATUS_TOKEN_FAIL_VERIFICATION    = 1503; //token验证失败
    const STATUS_TOKEN_FAIL_EXPIRED         = 1504; //token已过期
    const STATUS_TOKEN_FAIL_PARAM_NOT_FOUND = 1505; //token参数未传值
    const STATUS_TOKEN_FAIL_USER_NOT_FOUND  = 1520; //token对应的用户不存在

    const STATUS_TOKEN_EXPIRED_OVER_MAX_DAY  = 1601; //token过期时间不能超过最大限制
    const STATUS_TOKEN_EXPIRED_LOWER_MIN_DAY = 1602; //token过期时间不能超过最小限制


    const STATUS_USER_NOT_FOUND         = 2101;  //用户不存在
    const STATUS_USER_NOT_FOUND_ID      = 2102;  //用户ID不存在
    const STATUS_USER_NOT_FOUND_ACCOUNT = 2103;  //用户帐户不存在

    const STATUS_USER_SALT_NOT_FOUND = 2201;    //用户私有KEY异常

    const STATUS_USER_NOT_HAVE_POINT = 2301;  //用户点数不足
    const STATUS_USER_NOT_HAVE_MONEY = 2302;  //用户余额不足

    const STATUS_FILE_NOT_FOUND    = 3000; //文件不存在
    const STATUS_FILE_NOT_FOUND_ID = 3001; //文件ID不存在

    const STATUS_FILE_LIMIT_OVER_SIZE   = 3101;//文件超过最大大小限制
    const STATUS_FILE_LIMIT_OVER_WIDTH  = 3103;//文件超过最大宽度限制
    const STATUS_FILE_LIMIT_OVER_HEIGHT = 3104;//文件超过最大高度限制

    const STATUS_FILE_LIMIT_LOWER_SIZE   = 3101;//文件低于最小大小限制
    const STATUS_FILE_LIMIT_LOWER_WIDTH  = 3103;//文件低于最小宽度限制
    const STATUS_FILE_LIMIT_LOWER_HEIGHT = 3104;//文件低于最小高度限制

    const STATUS_FILE_SUFFIX_NOT_ALLOW = 3301;//文件后缀不在允许的范围内

    const STATUS_FILE_TYPE_ID_FAIL                       = 3401;//文件类型ID错误
    const STATUS_FILE_TYPE_ID_FAIL_NOT_FOUND             = 3402;//文件类型ID不存在
    const STATUS_FILE_TYPE_ID_FAIL_NOT_ALLOW             = 3403;//文件类型ID不可用
    const STATUS_FILE_TYPE_ID_FAIL_DISABLED_OR_NOT_FOUND = 3404;//文件类型不存在或已被禁用


    const STATUS_FILE_REPORT_FAIL                   = 3601; //文件报错失败
    const STATUS_FILE_REPORT_FAIL_REPLAY_CANT       = 3602; //文件报错不允许重复
    const STATUS_FILE_REPORT_FAIL_REPLAY_OR_TIMEOUT = 3603; //文件报错不允许重复或已超过时间限制
    const STATUS_FILE_REPORT_FAIL_UPDATE_SQL_ERROR  = 3604; //文件报错更新数据失败，请联系客服


    const STATUS_DB_SAVE_ERROR_FILE = 4001;   //文件信息入库失败

    const STATUS_ACCOUNT_RETURN_POINTS = 5001;    //帐户返还点数异常
    const STATUS_ACCOUNT_DEDUCT_POINTS = 5101;    //帐户扣减点数异常

    const STATUS_APP_FAIL_NOT_FOUND             = 6001;     //软件ID不存在
    const STATUS_APP_FAIL_IS_DISABLED           = 6002;     //软件已被禁用
    const STATUS_APP_FAIL_NOT_FOUND_OR_DISABLED = 6003;     //软件已被禁用或不存在

    const STATUS_PARAMS_FAIL              = 9001;   //参数错误
    const STATUS_PARAMS_FAIL_FORMAT       = 9002;   //参数格式错误
    const STATUS_PARAMS_FAIL_VERIFICATION = 9003;   //参数验证失败


    public static $status = [
        self::STATUS_LOGIN_FAILED                     => '登录失败',
        self::STATUS_LOGIN_FAILED_PASSWORD            => '登录失败密码错误',
        self::STATUS_LOGIN_FAILED_ACCOUNT             => '登录失败用户名错误',
        self::STATUS_LOGIN_FAILED_ACCOUNT_OR_PASSWORD => '登录失败用户名或密码错误',

        self::STATUS_TOKEN_FAIL                 => 'token错误',
        self::STATUS_TOKEN_FAIL_FORMAT          => 'token格式不正确',
        self::STATUS_TOKEN_FAIL_VERIFICATION    => 'token验证失败',
        self::STATUS_TOKEN_FAIL_EXPIRED         => 'token已过期',
        self::STATUS_TOKEN_FAIL_USER_NOT_FOUND  => 'token对应的用户不存在',
        self::STATUS_TOKEN_FAIL_PARAM_NOT_FOUND => 'token参数未传值',

        self::STATUS_TOKEN_EXPIRED_OVER_MAX_DAY  => 'token过期时间不能超过最大限制',
        self::STATUS_TOKEN_EXPIRED_LOWER_MIN_DAY => 'token过期时间不能超过最小限制',

        self::STATUS_USER_NOT_FOUND         => '用户不存在',
        self::STATUS_USER_NOT_FOUND_ID      => '用户ID不存在',
        self::STATUS_USER_NOT_FOUND_ACCOUNT => '用户帐户不存在',

        self::STATUS_USER_SALT_NOT_FOUND => '用户私有KEY异常',

        self::STATUS_USER_NOT_HAVE_POINT => '用户点数不足',
        self::STATUS_USER_NOT_HAVE_MONEY => '用户余额不足',

        self::STATUS_FILE_LIMIT_OVER_SIZE   => '文件超过最大大小限制',
        self::STATUS_FILE_LIMIT_OVER_WIDTH  => '文件超过最大宽度限制',
        self::STATUS_FILE_LIMIT_OVER_HEIGHT => '文件超过最大高度限制',

        self::STATUS_FILE_LIMIT_LOWER_SIZE   => '文件低于最小大小限制',
        self::STATUS_FILE_LIMIT_LOWER_WIDTH  => '文件低于最小宽度限制',
        self::STATUS_FILE_LIMIT_LOWER_HEIGHT => '文件低于最小高度限制',

        self::STATUS_FILE_SUFFIX_NOT_ALLOW => '文件后缀不在允许的范围内',

        self::STATUS_FILE_TYPE_ID_FAIL                       => '文件类型ID错误',
        self::STATUS_FILE_TYPE_ID_FAIL_NOT_FOUND             => '文件类型ID不存在',
        self::STATUS_FILE_TYPE_ID_FAIL_NOT_ALLOW             => '文件类型ID不可用',
        self::STATUS_FILE_TYPE_ID_FAIL_DISABLED_OR_NOT_FOUND => '文件类型不存在或已被禁用',

        self::STATUS_FILE_REPORT_FAIL                   => '文件报错失败',
        self::STATUS_FILE_REPORT_FAIL_REPLAY_CANT       => '文件报错不允许重复',
        self::STATUS_FILE_REPORT_FAIL_REPLAY_OR_TIMEOUT => '文件报错不允许重复或已超过时间限制',
        self::STATUS_FILE_REPORT_FAIL_UPDATE_SQL_ERROR  => '文件报错更新数据失败，请联系客服',

        self::STATUS_DB_SAVE_ERROR_FILE => '文件信息入库失败',

        self::STATUS_ACCOUNT_RETURN_POINTS => '帐户返还点数异常',
        self::STATUS_ACCOUNT_DEDUCT_POINTS => '帐户扣减点数异常',


        self::STATUS_APP_FAIL_NOT_FOUND             => '软件ID不存在',
        self::STATUS_APP_FAIL_IS_DISABLED           => '软件已被禁用',
        self::STATUS_APP_FAIL_NOT_FOUND_OR_DISABLED => '软件已被禁用或不存在',


        self::STATUS_PARAMS_FAIL              => '参数错误',
        self::STATUS_PARAMS_FAIL_FORMAT       => '参数格式错误',
        self::STATUS_PARAMS_FAIL_VERIFICATION => '参数格式错误',
    ];

    public function getMessageByStatus($status)
    {
        return key_exists($status, self::$status) ? self::$status[ $status ] : "";
    }

    public function autoSetMessageByStatus()
    {
        if (!$this->message) {
            $this->message = $this->getMessageByStatus($this->status_code);
        }
    }
}
