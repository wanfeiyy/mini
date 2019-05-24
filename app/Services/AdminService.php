<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-19
 * Time: 13:41
 */

namespace App\Services;

use App\Daos\UserDao;
use App\Enums\RoleEnum;
use App\Exceptions\Businesses\BusinessException;
use App\Exceptions\Services\ServiceException;
use App\Http\Response;
use App\Traits\CaseConverter;

class AdminService
{
    use CaseConverter;

    private $userService;

    private $passportService;

    private $userDao;

    public function __construct(UserService $userService, PassportService $passportService, UserDao $userDao)
    {
        $this->userService = $userService;
        $this->passportService = $passportService;
        $this->userDao = $userDao;
    }


    /**
     * @param string $username
     * @param string $pwd
     *
     * @return array
     */
    public function adminLogin(string $username, string $pwd) : array
    {
        $userId = $this->passportService->getCurrentUserId();
        $config = config('auth.admin');
        foreach ($config as $v) {
            if ($v['pwd'] == $pwd && $v['username'] == $username) {
                $userInfo = $this->passportService->getUserInfo();
                if (! isset($userInfo['role']) || $userInfo['role'] != RoleEnum::ROLE_ADMIN) {
                    if ($this->userDao->updateById($userId, ['role' => RoleEnum::ROLE_ADMIN])) {
                        $userInfo['role'] = RoleEnum::ROLE_ADMIN;
                        $this->updateUserSess($userInfo, $userId);
                    };
                }

                unset($userInfo['ext']);
                return $userInfo;
            }
        }

        Response::throwError(Response::ACCOUNT_ERROR);
    }


    /**
     * @param array $filter
     * @param int $start
     * @param int $limit
     *
     * @return array
     */
    public function getUserList(array $filter = [], int $start = 0, int $limit = 20)
    {
        $data = $this->userDao->getList(
            $filter,
            ['start' => $start, 'limit' => $limit,
                'column' => array_merge($this->userDao->getFillable(), ['id', 'role', 'created_at'])
            ],
            null
        );

        $data['list'] = $this->key2CamelCase($data['list']);
        return $data;
    }
    
    
    private function updateUserSess(array $userInfo, int $userId)
    {
        $ext = isset($userInfo['ext']) ? $userInfo['ext'] : [];
        unset($userInfo['ext']);
        $ttl = $this->userService->getSessExpire($userId);
        $ttl > 0 && $this->userService->saveSeesAndUserInfo($userId, $userInfo, $ext, $ttl);
    }


    /**
     * @param int $userId
     * @param int $role
     *
     * @return null
     */
    public function updateUserRole(int $userId, int $role)
    {
        if (! $this->passportService->getIsAdmin()) {
            throw new BusinessException('请先登录', Response::UNAUTHORIZED);
        } elseif (! in_array($role, [RoleEnum::ROLE_GENERAL, RoleEnum::ROLE_CHECK])) {
            throw new ServiceException('更新权限角色错误');
        }

        $user = $this->userService->getUserById($userId);
        if ($user === null) {
            throw new ServiceException('用户不存在');
        }

        if ($user['role'] == $role) {
            return null;
        } elseif ($this->userDao->updateById($userId, ['role' => $role])) {
            $user['role'] = $role;
            $this->updateUserSess($user->toArray(), $userId);
            return null;
        }

        throw new ServiceException('更新权限失败');
    }

}