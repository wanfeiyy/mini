### 接口文档说明
1. 返回参数说明
```json
"errcode": 0, // 为0是代表成功, 其余都为失败
"errmsg": "success",
"data": {}
```

2.登录状态说明
```json
"errcode": 3, // 为3表示登录过期,需重新登录
"errmsg": "success",
"data": {}
```

```json
"errcode": 7, // 为7表示管理员登录过期,需重新登录
"errmsg": "success",
"data": {}
```
3.全局接口说明

所有请求接口都应加上下面登录接口返回的sess值 `sess=xxxxx`

###1. 登录
 - 请求地址: api/login
 - 请求方式: POST
 - 请求参数
 
| 参数 | 是否必填 | 描述 | 类型 | 值 |
| --------| ----- |-------| -------- |
| code| 是 | 微信code | string | 1234 |
| nickname |是 |昵称 | string | xxx|
| avatar |否 |头像 | string | http://sss.com |

#### 响应结果
```json
{
    "errcode": 0,
    "errmsg": "success",
    "data": {
        "sess": "928d2562c5d7d0dafa30535c6d0ec68e", // 登录用
        "userInfo": {
            "name": "收到货好多事",
            "avatar": "",
            "updatedAt": "1558439613",
            "createdAt": "1558439613",
            "id": 2
        }
    }
}
```

###2. 超管登录
 - 请求地址: api/admin/login
 - 请求方式: POST
 - 请求参数
 
| 参数 | 是否必填 | 描述 | 类型 | 值 |
| --------| ----- |-------| -------- |
| username |是 |用户名 | string | xxx|
| password |是 |密码 | string | http://sss.com |

#### 响应结果
```json
{
    "errcode": 0,
    "errmsg": "success",
    "data": {
        "id": 1,
        "name": "收到货好多事",
        "avatar": "",
        "createdAt": "1558276381",
        "updatedAt": "1558439467",
        "role": 2,
        "origin": 0
    }
}
```

###3. 申请用户列表
 - 请求地址: api/admin/user/list
 - 请求方式: get
 - 请求参数: 
 
| 参数 | 是否必填 | 描述 | 类型 | 值 |
| --------| ----- |-------| -------- |
| start |是 |起始页 | int | 0|
| limit |是 |每页条数 | int | 20 |

#### 响应结果
```json
{
    "errcode": 0,
    "errmsg": "success",
    "data": {
        "list": [
            {
                "name": "收到货好多事",
                "avatar": "",
                "id": 2,
                "role": 0,
                "createdAt": "1558439613"
            },
            {
                "name": "收到货好多事",
                "avatar": "",
                "id": 1,
                "role": 2,
                "createdAt": "1558276381"
            }
        ],
        "more": 0, // 1 表示还有下页
        "start": 1 // 获取下页传的start值
    }
}
```

###4. 更新用户权限
 - 请求地址: api/admin/user/role
 - 请求方式: post
 - 请求参数: 
 
| 参数 | 是否必填 | 描述 | 类型 | 值 |
| --------| ----- |-------| -------- |
| userId |是 |用户id | int | 0|
| role |是 |用户角色(0 普通人员 1 调度人员 2 超管) | int | 1 |

#### 响应结果
```json
{
    "errcode": 0,
    "errmsg": "success",
    "data": {
    }
}
```

###5. 获取用户信息
 - 请求地址: api/user
 - 请求方式: get
 - 请求参数: 
 
| 参数 | 是否必填 | 描述 | 类型 | 值 |
| --------| ----- |-------| -------- |
| sess |是 |登录参数 | string | xxx |

#### 响应结果
```json
{
    "errcode": 0,
    "errmsg": "success",
    "data": {
        "name": "收到货好多事",
        "avatar": "",
        "role": 0,
        "updatedAt": "1558445082",
        "createdAt": "1558445082",
        "id": 11
    }
}
```
 
 
