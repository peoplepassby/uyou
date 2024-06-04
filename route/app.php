<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('/', function () {
    return 'hello,ThinkPHP6!';
});

Route::get('hello2/:name', 'index/hello2');
Route::get('test/', 'test1/respondWithJson');
Route::post('Accountcheck', 'Accountcheck/validateCredentials');
Route::post('CreateAccount', 'CreateAccount/createaccount');
Route::post('DeleteAccount', 'DeleteAccount/deleteaccount');
Route::post('Changepw', 'Changepw/changePassword');