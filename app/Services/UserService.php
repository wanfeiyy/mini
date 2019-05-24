<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-19
 * Time: 15:32
 */

namespace App\Services;

use App\Daos\UserDao;
use App\Exceptions\Services\ServiceException;
use App\Traits\CaseConverter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class UserService
{
    use CaseConverter;

    private $userDao;

    protected $sessTtl = 7;


    /**
     * UserService constructor.
     * @param UserDao $userDao
     */
    public function __construct(UserDao $userDao)
    {
        $this->userDao = $userDao;
        Config::set('cache.default', 'redis');
    }

    /**
     * @param array $loginParams
     *
     * @return array
     */
    public function login(array $loginParams) :array
    {
        $attributes = [
            'name' => $loginParams['name'],
            'avatar' => $loginParams['avatar'],
            'role' => 0
        ];
        $ret = $this->userDao->updateOrCreate(
            [
                'openid' => $loginParams['openId'],
            ],
            $attributes
        );

        if ($ret) {
            $data = $this->key2CamelCase($ret);
            $sess = $this->saveSeesAndUserInfo(
                $ret->id,
                $data,
                ['sessionKey' => $loginParams['sessionKey'] ?? '']
            );

            $data = $this->getUserById($data['id']);
            unset($data['ext']);
            return [
                'sess' => $sess,
                'userInfo' => $this->key2CamelCase($data)
            ];
        }

        throw new ServiceException('登录失败!');
    }


    /**
     * @param int $userId
     * @return string
     */
    protected function generateSess(int $userId) :string
    {
        return md5($userId . '_' . str_random(32));
    }


    /**
     * @param int $userId
     *
     * @return string
     */
    protected function saveSess(int $userId) :string
    {
        $sess = $this->generateSess($userId);
        Cache::put($sess, $userId, $this->getSessTTL());
        return $sess;
    }

    /**
     * @param int $userId
     * @param array $userInfo
     * @param array $ext
     * @param int $ttl
     *
     * @return string
     */
    public function saveSeesAndUserInfo(int $userId, array $userInfo, array $ext, int $ttl = null) :string
    {
        if ($sess = $this->saveSess($userId)) {
            Cache::put($userId, array_merge($userInfo, ['ext' => $ext]), $this->getSessTTL($ttl));
            return $sess;
        }

        return '';
    }

    /**
     * @retur int
     */
    private function getSessTTL($ttl = null) :int
    {
        return $ttl ? : $this->sessTtl * 86400 / 60;
    }


    /**
     * @param int $userId
     *
     * @return array|\Illuminate\Database\Eloquent\Model|mixed|null
     */
    public function getUserById(int $userId)
    {
        $data = Cache::get($userId);
        if ($data === null) {
            $data = $this->userDao->getById($userId, array_merge($this->userDao->getFillable(), ['id', 'created_at']));
            $data = $data ? $data->toArray() : [];
        }

        return $data;
    }


    /**
     * @param string $sess
     *
     * @return int
     */
    public function getUserBySess(string $sess) :int
    {
        return intval(Cache::get($sess));
    }


    public function getSessExpire(int $userId)
    {
        return intval(Cache::ttl($userId) / 60);
    }

}