<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-19
 * Time: 15:42
 */

namespace App\Models;


class User extends Base
{
    protected $fillable = [
        'name',
        'avatar',
        'openid',
        'role'
    ];

    
    protected $hidden = [
        'openid'
    ];

    protected $casts = [
        'role' => 'int',
    ];
}