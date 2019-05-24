<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-19
 * Time: 14:15
 */

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Response;
use App\Services\AdminService;
use App\Services\MiniWechatService;
use App\Services\PassportService;
use App\Services\UserService;

class AuthController extends Controller
{
    private $miniWechatService;

    protected $userService;

    protected $adminService;

    protected $passportService;

    public function __construct(
        MiniWechatService $miniWechatService,
        UserService $userService,
        AdminService $adminService,
        PassportService $passportService
    )
    {
        $this->miniWechatService = $miniWechatService;
        $this->userService = $userService;
        $this->adminService = $adminService;
        $this->passportService = $passportService;
    }


    public function loginByWxApp(AuthRequest $req)
    {
        $jsCode = $req->input('code');
        $nickName = (string)$req->input('nickname');
        $avatar = (string)$req->input('avatar');
        $wx = $this->miniWechatService->getSessionKey($jsCode);
        $thirdParty = [
            'openId'      => $wx['open_id'],
            'sessionKey'  => $wx['session_key'],
            'name'        => $nickName,
            'avatar'      => $avatar,
            'regIp'       => $req->getClientIp(),
        ];


        $ret = $this->userService->login($thirdParty);
        return Response::generate(Response::SUCCESS, $ret);
    }


    public function loginByAdmin(AuthRequest $req)
    {
        $username = $req->input('username');
        $password = $req->input('password');
        $ret = $this->adminService->adminLogin($username, $password);
        return Response::success($ret);
    }


    public function getUserInfo()
    {
        $usrInfo = $this->passportService->getUserInfo();
        unset($usrInfo['ext']);
        return Response::success($usrInfo);
    }

}