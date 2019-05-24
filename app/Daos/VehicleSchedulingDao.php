<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-24
 * Time: 09:36
 */

namespace App\Daos;


use App\Models\VehicleScheduling;

class VehicleSchedulingDao extends BaseDao
{
    private $dao;

    public function __construct(VehicleScheduling $vehicleScheduling)
    {
        $this->dao = $vehicleScheduling;
    }

    protected function getDao()
    {
        return $this->dao;
    }

}