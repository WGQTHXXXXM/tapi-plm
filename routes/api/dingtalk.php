<?php

$api->version('v1', [
    'namespace'  => 'App\Http\Controllers\Api',
    'middleware' => [
        //'token.auth',
    ]
], function ($api) {
    //注册业务事件
    $api->get('dingtalk/register', 'DingTalkController@register')->name('dingtalk.register');

    //查询回调
    $api->get('dingtalk/getCallBack', 'DingTalkController@getCallBack')->name('dingtalk.getCallBack');

    //更新事件回调
    $api->post('dingtalk/updateCallBack', 'DingTalkController@updateCallBack')->name('dingtalk.updateCallBack');

    //查询失败的回调
    $api->get('dingtalk/getFailEvent', 'DingTalkController@getFailEvent')->name('dingtalk.getFailEvent');

    //接收回调消息
    $api->post('dingtalk/eventReceive', 'DingTalkController@eventReceive')->name('dingtalk.eventReceive');

    //同步
    $api->post('dingtalk/sync', 'DingTalkController@doSyncDingOrgUser')->name('dingtalk.doSyncDingOrgUser');

    //同步
    $api->post('dingtalk/updatenewusertbl', 'DingTalkController@updateNewUserTbl')->name('dingtalk.updateNewUserTbl');

    //下载文件
    $api->get('approvals/file/{id}/tk/{token}', 'ApprovalsController@downloadFile')->name('approvals.downloadFile');

    //读文件
    //$api->get('dingtalk/aaa', 'DingTalkController@aaa')->name('dingtalk.aaa');


});