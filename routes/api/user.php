<?php

$api->version('v1', [
    'namespace'  => 'App\Http\Controllers\Api',
    'middleware' => [
        'auth:api'
    ]
], function ($api) {

	//根据中台用户ID获取PLM人员信息
	$api->get("getUser", 'UsersController@getUser')
		->name('users.getUser');

    //用户列表
    $api->get('users', 'UsersController@index')
        ->name('users.index');

	//所有用户
	$api->get('users/all', 'UsersController@all')
		->name('users.all');

	// 获取某个用户信息
	$api->get('users/{id}', 'UsersController@show')
		->name('users.show');

    // 新建用户
    $api->post('users', 'UsersController@store')
        ->name('users.store');

    //用户激活切换
    $api->put('users/changeStatus', 'UsersController@changeStatus')
        ->name('users.changeStatus');

    //修改用户信息
    $api->put('users/{id}', 'UsersController@update')
        ->name('users.update');

    //修改用户信息
    $api->get('aaa', 'UsersController@aaa')
        ->name('users.update');

    //删除用户
//    $api->delete('users/{ids}', 'UsersController@destroy')
//        ->name('users.destroy');


});
