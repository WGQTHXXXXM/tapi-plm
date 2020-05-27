<?php

$api->version('v1', [
    'namespace'  => 'App\Http\Controllers\Api',
    'middleware' => [
        'auth:api',
    ]
], function ($api) {
    //阀点创建
    $api->post('milestone', 'MilestoneController@create')
        ->name('MilestoneController.create');

    //阀点更新
    $api->put('milestone/{id}', 'MilestoneController@update')
        ->name('MilestoneController.update');

    //项目下的阀点
    $api->get('milestone/{project_id}/project', 'MilestoneController@index')
        ->name('MilestoneController.index');

    //阀点的启动
    $api->post('milestone/{id}/start', 'MilestoneController@startMilestone')
        ->name('MilestoneController.startMilestone');



});
