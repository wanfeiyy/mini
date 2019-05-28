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
    const INIT = 0; // 审核中

    const CHECK_SUCCESS = 1; // 审核成功

    const CHECK_ERROR = 2; // 审核失败

    const SCHEDULING_SUCCESS = 3; // 调度成功

    const SCHEDULING_ERROR = 4; // 调度失败

}