<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-19
 * Time: 14:24
 */

namespace App\Http\Requests;

class SchedulingRequest extends Request
{
    protected $ruleConfig = [
        'create' => [
            'department' => 'required|string',
            'name'  => 'required|string',
            'num'  => 'required|int',
            'phone' => 'required|string',
            'address' => 'required|string',
            'route' => 'required|string',
            'startTime' => 'required|string',
            'days' => 'required|int',
            'securityName' => 'string',
            'taskDesc' => 'required|string',
        ],
        'show' => [
            'id' => 'required|int',
        ],
        'check' => [
            'id' => 'required|int',
            'safetyAccounting' => 'string',
            'opinion' => 'string',
            'state' => 'required|in:1,0'
        ],
        'scheduling' => [
            'id' => 'required|int',
            'driver' => 'string',
            'numberPlates' => 'string',
            'remarks' => 'string',
            'state' => 'required|in:1,0'
        ]
    ];
}