<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-20
 * Time: 11:40
 */

namespace App\Services;


use App\Enums\RoleEnum;
use App\Exceptions\Businesses\BusinessException;
use App\Http\Response;

class PassportService
{

    private $userService;

    private static $userId;

    private static $userInfo;

    private static $isAdmin;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param string $sess
     */
    public function checkLogin($sess = null)
    {
        if (self::$userId && self::$userInfo && ! $sess) {
            return true;
        }

        $sess = $sess ? : $this->getLoginSess();
        if ($sess) {
            $userId = $this->userService->getUserBySess($sess);
            if ($userId) {
                $userInfo = $this->userService->getUserById($userId);
                self::$userId = $userId;
                self::$userInfo = $userInfo;
                isset(self::$userInfo['role']) && self::$userInfo['role'] == RoleEnum::ROLE_ADMIN &&
                self::$isAdmin = true;
                return true;
            }
        }


        throw new BusinessException('请先登录', Response::UNAUTHORIZED);
    }


    private function getLoginSess()
    {
        return request()->input('sess');
    }

    /**
     * @return int
     */
    public function getCurrentUserId() :int
    {
        $this->checkLogin();
        return self::$userId;
    }

    /**
     * @return array
     */
    public function getUserInfo() :array
    {
        $this->checkLogin();
        return self::$userInfo;
    }


    public function getIsAdmin()
    {
        $this->checkLogin();
        return self::$isAdmin;
    }
}