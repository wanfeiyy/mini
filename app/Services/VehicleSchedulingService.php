<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-24
 * Time: 09:33
 */

namespace App\Services;

use App\Daos\VehicleSchedulingDao;
use App\Enums\RoleEnum;
use App\Enums\SchedulingStatus;
use App\Exceptions\Services\ServiceException;
use App\Traits\CaseConverter;
use App\Utils\Helper;

class VehicleSchedulingService
{
    use CaseConverter;

    private $vehicleSchedulingDao;

    public function __construct(VehicleSchedulingDao $vehicleSchedulingDao)
    {
        $this->vehicleSchedulingDao = $vehicleSchedulingDao;
    }


    public function create($userId, array $data)
    {
        $data = Helper::arrayKeySnakeCase($data);
        $data['user_id'] = $userId;
        return $this->key2CamelCase($this->vehicleSchedulingDao->create($data));
    }


    public function getList($userId, $role, $start, $limit)
    {
        $where = $this->buildWhere($userId, $role);
        $data = $this->vehicleSchedulingDao->getList($where, compact('start', 'limit'));
        return $this->key2CamelCase($data);
    }


    public function getDetail($id, $userId, $role)
    {
        $where = $this->buildWhere($userId, $role);
        $where['id'] = $id;
        $data = $this->vehicleSchedulingDao->getRow($where);
        if ($data === null) {
            throw new ServiceException('获取调度详情错误');
        }

        return $this->key2CamelCase($data);
    }

    /**
     * @param $id
     * @param string $safetyAccounting
     * @param string $opinion
     * @param array $checkInfo
     *
     * @return null
     */
    public function check(int $id, int $state, string $safetyAccounting, string $opinion, array $checkInfo)
    {
        $role = $checkInfo['role'] ?? 0;
        if (! in_array($role, [RoleEnum::ROLE_ADMIN, RoleEnum::ROLE_CHECK])) {
            throw new ServiceException('没有权限审核');
        }

        $this->getDetail($id, $checkInfo['userId'] ?? 0, $role);
        $ret = $this->vehicleSchedulingDao->updateById($id, ['check_info' => json_encode([
            'safety_accounting' => $safetyAccounting,
            'opinion' => $opinion,
            'name' => $checkInfo['name'] ?? '',
            'state' => $state,
        ]), 'status' => $state ? SchedulingStatus::CHECK_SUCCESS : SchedulingStatus::CHECK_ERROR]);

        if (!$ret) {
            throw new ServiceException('审核失败');
        }

        return null;
    }


    public function scheduling(int $id, int $state, string $driver, string $numberPlates, string $remarks, array $checkInfo)
    {
        $role = $checkInfo['role'] ?? 0;
        if (! in_array($role, [RoleEnum::ROLE_ADMIN, RoleEnum::ROLE_SCHEDULING])) {
            throw new ServiceException('没有权限调度');
        }

        $this->getDetail($id, $checkInfo['userId'] ?? 0, $role);
        $ret = $this->vehicleSchedulingDao->updateById($id, ['scheduling_info' => json_encode([
            'driver' => $driver,
            'number_plates' => $numberPlates,
            'remarks' => $remarks,
            'name' => $checkInfo['name'] ?? '',
            'state' => $state,
        ]), 'status' => $state ? SchedulingStatus::SCHEDULING_SUCCESS : SchedulingStatus::SCHEDULING_ERROR]);

        if (!$ret) {
            throw new ServiceException('审核失败');
        }

        return null;

    }

    private function buildWhere($userId, $role)
    {
        $where = [];
        if ($role == RoleEnum::ROLE_GENERAL) {
            $where['user_id'] = $userId;
        } elseif ($role == RoleEnum::ROLE_CHECK) {
            $where['status'] = ['in', [SchedulingStatus::CHECK_SUCCESS, SchedulingStatus::CHECK_ERROR, SchedulingStatus::INIT]];
        } elseif ($role == RoleEnum::ROLE_SCHEDULING) {
            $where['status'] = ['in', [SchedulingStatus::SCHEDULING_ERROR, SchedulingStatus::SCHEDULING_SUCCESS, SchedulingStatus::CHECK_SUCCESS, SchedulingStatus::CHECK_ERROR]];
        } else {
            $where['status'] = ['>=', 0];
        }

        return $where;
    }

}