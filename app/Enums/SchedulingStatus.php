<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-24
 * Time: 09:51
 */

namespace App\Enums;

class SchedulingStatus
{
    const INIT = 0;

    const CHECK_SUCCESS = 1;

    const CHECK_ERROR = 2;

    const SCHEDULING_SUCCESS = 3;

    const SCHEDULING_ERROR = 4;

}