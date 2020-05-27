<?php

$api->version('v1', [
    'namespace'  => 'App\Http\Controllers\Api',
    'middleware' => [
        'auth:api',
    ]
], function ($api) {
    //创建
    $api->post('approvals', 'ApprovalsController@create')->name('approvals.create');

    //发起
    $api->put('approvals/{id}/launch', 'ApprovalsController@launch')->name('approvals.launch');

    //详情
    $api->get('approvals/{id}', 'ApprovalsController@show')->name('approvals.show');

    //搜索，列表
    $api->get('approvals', 'ApprovalsController@index')
    ->name('approvals.index');

    // 日志查询
    $api->get('approvals/{id}/records', 'ApprovalsController@records')->name('approvals.records');


    // 任务流模板
    $api->get('taskflow/templates', 'ApprovalsController@taskflowTemplates')->name('taskflow.templates');


    // 文件上传
    $api->post('approvals/{id}/file', 'ApprovalsController@uploadFile')->name('approvals.uploadFile');

    //添加approve_file表的wiki_dl_path字段
    $api->put('approvalfile/downloadinfo', 'ApprovalsController@addFileInfo')->name('approvals.addFileInfo');

});
