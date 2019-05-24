<?php

namespace App\Exceptions;

use App\Exceptions\Businesses\BusinessException;
use App\Exceptions\Requests\RequestException;
use App\Exceptions\Services\ServiceException;
use App\Http\Response;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {

        $code = $exception->getCode() != 0 ? $exception->getCode() : Response::FAILED;
        $message = $exception->getMessage();

        if ($exception instanceof  RequestException) {
            return Response::generate(Response::PARAM_ERROR, null, $message);
        }

        if ($exception instanceof BusinessException) {
            // dontReport 中拦截了该异常，这里主动写 info 级别异常
            $this->log($exception);

            $message = $message ?: '业务异常,请稍后重试';
            return Response::generate($code, null, $message);
        }

        if ($exception instanceof ModelNotFoundException) {
            return Response::generate(Response::FAILED, null, $exception->getMessage());
        }


        if ($exception instanceof ServiceException) {
            $message = $message ?: '服务异常,请稍后重试';
            return Response::generate($code, null, $message);
        }


        return parent::render($request, $exception);
    }

    /**
     * 记录异常日志.
     *
     * @param \Exception|\RuntimeException $e        Exception.
     * @param boolean                      $hasTrace 是否显示详细追踪信息.
     *
     * @return void
     */
    private function log($e, $hasTrace = false)
    {
        if (!$hasTrace) {
            Log::info(
                sprintf(
                    'exception with message \'%s\' in %s:%s',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
        } else {
            Log::info(
                sprintf(
                    "exception with message '%s' in %s:%s\nStack trace:\n%s",
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $e->getTraceAsString()
                )
            );
        }
    }
}
