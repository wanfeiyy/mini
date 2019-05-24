<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-20
 * Time: 17:06
 */

namespace App\Models;

class VehicleScheduling extends Base
{
    protected $fillable = [
        'department',
        'name',
        'num',
        'phone',
        'address',
        'route',
        'start_time',
        'days',
        'security_name',
        'task_desc',
        'user_id',
        'status',
    ];

    protected $casts = [
        'check_info' => 'json',
        'scheduling_info' => 'json',
    ];

    public function getCheckInfoAttribute()
    {
        if (empty($this->attributes['check_info'])) {
            return new \stdClass();
        }

        return json_decode($this->attributes['check_info'], true);
    }


    public function getSchedulingInfoAttribute()
    {
        if (empty($this->attributes['scheduling_info'])) {
           return new \stdClass();
        }

        return json_decode($this->attributes['scheduling_info'], true);
    }
    
}