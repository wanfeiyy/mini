<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-19
 * Time: 14:15
 */
namespace App\Services;

use App\Exceptions\Services\ServiceException;
use Illuminate\Support\Facades\Log;

class MiniWechatService
{
    private $app;

    public function __construct()
    {
        $this->app = \EasyWeChat::miniProgram();
    }

    public function getSessionKey(string $jsCode)
    {
        try {
           $ret = $this->app->auth->session($jsCode);
           Log::debug('session ker ret', [$ret]);
           //return ['session_key' => 'xxxxxx', 'openid' => mt_rand(1,10000)];
           if (! isset($ret['errcode']) || $ret['errcode'] != 0) {
               return $ret;
           } else {
               throw new \Exception('auth.code2Session error:' . json_encode($ret));
           }
        } catch (\Exception $e) {
            Log::error('mini get session key error', [$e]);
            throw new ServiceException('获取session_key失败');
        }
    }
}