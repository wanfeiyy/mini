<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-19
 * Time: 15:46
 */

namespace App\Daos;

use App\Models\User;

class UserDao extends BaseDao
{
    private $dao;

    public function __construct(User $user)
    {
        $this->dao = $user;
    }


    protected function getDao()
    {
        return $this->dao;
    }
}