<?php

namespace App\Exceptions;

use App\Exceptions\ApiException;
use App\Exceptions\ApiStatusException;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        ApiException::class,
        ApiStatusException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     *
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {

        $rendered  = parent::render($request, $e);
        $http_code = $rendered->getStatusCode();

        //验证错误 exception
        if ($e instanceof ValidationException) {
            $validatorErrors = $e->validator->errors()->getMessages();
            $errors          = [];

            foreach ($validatorErrors as $field => $error) {
                foreach ($error as $message) {
                    $errors[ $field ] = $message;
                }
            }

            $validator_http_code = $http_code ? $http_code : 422;

            $response = [
                'status_code' => $validator_http_code,
            ];
            if (env('API_RESPONSE_MESSAGE')) {
                $response['message'] = '给定的数据无效';
                $response['errors']  = $errors;
            }

            return response()->json($response, $validator_http_code);
        }

        //自定义Exception
        if (($e instanceof ApiException) || ($e instanceof ApiStatusException)) {

            $response = [
                'status_code' => $e->status_code,
            ];
            if (env('API_RESPONSE_MESSAGE')) {
                $response['message'] = $e->getMessage();
            }

            return response()->json($response, $e->http_code);
        }


        //如果未被捕获，则执行这里，报英文错，底层抛的错
        $response = [
            'status_code' => $e->getCode() ? $e->getCode() : $rendered->getStatusCode(),
        ];

        if (env('API_RESPONSE_MESSAGE')) {
            $class               = explode('\\', get_class($e));
            $class_name          = Str::snake($class[ count($class) - 1 ], ' ');
            $response['message'] = $e->getMessage() ? $e->getMessage() : $class_name;
        }

        if (env('API_RESPONSE_MESSAGE') && env('APP_DEBUG')) {
            $response['debug'] = [
                'file'   => $e->getFile(),
                'line'   => $e->getLine(),
                'string' => $e->getTraceAsString(),
            ];
        }

        return response()->json($response, $rendered->getStatusCode());
    }
}
