<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-19
 * Time: 14:24
 */

namespace App\Http\Requests;

class AuthRequest extends Request
{
    protected $ruleConfig = [
        'loginByWxApp' => [
            'code'   => 'required|string',
            'nickname' => 'sometimes|required|string'
        ],
        'loginByAdmin' => [
            'username' => 'required|string',
            'password' => 'required|string',
        ]
    ];
}