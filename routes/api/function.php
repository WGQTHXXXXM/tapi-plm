<?php

$api->version('v1', [
    'namespace'  => 'App\Http\Controllers\Api',
    'middleware' => [
        'auth:api',
    ]
], function ($api) {
	//功能列表
	$api->get('functions', 'FunctionObjController@index')
		->name('functions.index');

	//功能树
	$api->get('functions/tree/{project_id}', 'FunctionObjController@tree')
		->name('functions.tree');

	//关联零部件
	$api->post('functions/refparts', 'FunctionObjController@refparts')
		->name('functions.refparts');

	//取消关联零部件
	$api->delete('functions/refparts', 'FunctionObjController@delrefparts')
		->name('functions.delrefparts');

	//查询上级功能
	$api->get('functions/parent/{id}', 'FunctionObjController@parent')
		->name('functions.parent');

	//查询子级功能
	$api->get('functions/child/{id}', 'FunctionObjController@child')
		->name('functions.child');

	//功能详情
	$api->get('functions/{id}', 'FunctionObjController@show')
		->name('functions.show');

	//关联部件详情
	$api->get('functions/ref/{function_id}', 'FunctionObjController@showref')
		->name('functions.showref');

	//关联部件详情，有分页
	$api->get('functions/refpage/{function_id}', 'FunctionObjController@showrefpage')
		->name('functions.showrefpage');

    //添加功能
    $api->post('functions', 'FunctionObjController@store')
        ->name('functions.store');

    //修改功能
    $api->put('functions/{id}', 'FunctionObjController@update')
        ->name('functions.update');

	//批量删除功能
	$api->delete('functions/{ids}', 'FunctionObjController@destroy')
		->name('functions.destroy');

	//单个删除功能
	$api->delete('functions/del/{id}', 'FunctionObjController@del')
		->name('functions.del');

    //功能excel文件导入
    $api->post('functions/batch', 'FunctionObjController@batch')
        ->name('functions.batch');

    //功能合并
    $api->post('functions/{masterId}/merge/{slaveId}', 'FunctionObjController@merge')
        ->name('functions.merge');

    //检查所有功能的wiki页面
    $api->post('functions/checkfuncwikipage', 'FunctionObjController@checkFuncWikiPage')
        ->name('functions.checkFuncWikiPage');

    //检查用户是否可以编辑功能
    $api->get('functions/{funcId}/checkfuncedit/{userId}', 'FunctionObjController@checkFuncEdit')
        ->name('functions.checkFuncEdit');

});
