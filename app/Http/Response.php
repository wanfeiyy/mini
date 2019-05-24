<?php

namespace App\Http;


use App\Exceptions\Businesses\BusinessException;

class Response
{

    const SUCCESS = 0;
    const PARAM_ERROR = 1;
    const FAILED = 2;
    const UNAUTHORIZED = 3;
    const FORBIDDEN = 5;
    const ACCOUNT_DISABLED = 6;
    const ACCOUNT_ERROR = 7;

    private static $errorMsgs = [
        self::SUCCESS           => 'success',
        self::PARAM_ERROR       => '参数错误',
        self::FAILED            => '操作失败，请稍后再试',
        self::FORBIDDEN         => '权限不足',
        self::UNAUTHORIZED      => '请先登录',
        self::ACCOUNT_DISABLED  => '当前账号被禁用',
        self::ACCOUNT_ERROR => '请先登录管理员账号',
    ];

    public static function getMsg($code)
    {
        return isset(static::$errorMsgs[$code]) ? static::$errorMsgs[$code]
            : '';
    }

    public static function generate($errCode, $data = null, $errMsg = '')
    {
        $rs['errcode'] = $errCode;
        $rs['errmsg']  = $errMsg;
        $rs['errmsg']
        || $rs['errmsg'] = static::getMsg($errCode)
            ?: static::$errorMsgs[static::FAILED];
        $rs['data'] = $data === null || $data === [] ? new \stdClass() : $data;
        return response()->json(
            $rs,
            200,
            ['Content-Type' => 'application/json; charset=UTF-8'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    public static function success($data = null)
    {
        return static::generate(Response::SUCCESS, $data);
    }

    public static function throwError($errCode, $errMsg = '')
    {
        $errMsg || $errMsg = static::getMsg($errCode);
        throw new BusinessException($errMsg, $errCode);
    }

    public static function byException(\Exception $e)
    {
        return self::generate($e->getCode(), new \stdClass(), $e->getMessage());
    }
}
