<?php

$api->version('v1', [
    'namespace'  => 'App\Http\Controllers\Api',
    'middleware' => [
        //'auth:api',
    ]
], function ($api) {
    //任务创建
    $api->post('work', 'WorkController@create')
        ->name('WorkController.create');


    //任务更新
    $api->put('work/{id}', 'WorkController@update')
        ->name('WorkController.update');

    //任务浏览
    $api->get('work/{id}', 'WorkController@view')
        ->name('WorkController.view');

    //任务列表
    $api->get('work', 'WorkController@index')
        ->name('WorkController.index');

    //任务日志反馈
    $api->post('work/feedback/{id}', 'WorkController@workFeedback')
        ->name('WorkController.workFeedBack');

    //任务日志列表
    $api->get('work/log', 'WorkController@indexWorkLog')
        ->name('WorkController.indexWorkLog');



});
