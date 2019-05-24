<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::get('/', function () {
//    $miniProgram = EasyWeChat::miniProgram();
//    dump($miniProgram);
//    return view('welcome');
//});

Route::any('/login', 'AuthController@loginByWxApp');


Route::group(['middleware' => ['admin.auth']], function () {
    Route::get('/user', 'AuthController@getUserInfo');
    Route::any('/admin/login', 'AuthController@loginByAdmin');
    Route::group(['prefix' => 'scheduling'], function () {
        Route::any('/create', 'SchedulingController@create');
        Route::get('/list', 'SchedulingController@paginate');
        Route::get('/detail', 'SchedulingController@show');
        Route::any('/check', 'SchedulingController@check');
        Route::any('/scheduling', 'SchedulingController@scheduling');
    });
});

Route::group(['prefix' => 'admin', 'middleware' => ['admin.auth:1']], function () {
    Route::get('/user/list', 'AdminController@getUserList');
    Route::any('/user/role', 'AdminController@updateRole');
});


