<?php

$api->version('v1', [
    'namespace'  => 'App\Http\Controllers\Api',
    'middleware' => [
        'auth:api',
    ]
], function ($api) {

    // 审批决策
    $api->put('tasks/{taskId}', 'TaskController@decision')->name('tasks.decision');

    $api->get('tasks/{id}', 'TaskController@show')->name('tasks.show');


    // 获取添加审批人员和角色列表
    $api->get('tasks/{id}/participants', 'TaskController@participants')->name('tasks.participants');

    // 删除审批者
    $api->delete('tasks/participants/{id}', 'TaskController@removeParticipant')->name('tasks.removeParticipant');


    // 添加添加审批人员和角色
    $api->post('tasks/{id}/participant', 'TaskController@addParticipant')->name('tasks.addParticipant');

});
