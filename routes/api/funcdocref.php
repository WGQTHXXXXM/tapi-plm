<?php

$api->version('v1', [
    'namespace'  => 'App\Http\Controllers\Api',
    'middleware' => [
        'auth:api',
    ]
], function ($api) {
    //交付物清单列表
    $api->get('funcdocref/{function_id}/show', 'FunctionDocumentRefController@show')
        ->name('FunctionDocumentRefController.show');

    //新建一个上传的交付物清单
    $api->post('funcdocref', 'FunctionDocumentRefController@create')
        ->name('FunctionDocumentRefController.create');

    //修改一个上传的交付物清单
    $api->put('funcdocref/{id}', 'FunctionDocumentRefController@update')
        ->name('FunctionDocumentRefController.update');

    //交付物清单一个类型的历史版本
    $api->get('funcdocref/type', 'FunctionDocumentRefController@getType')
        ->name('FunctionDocumentRefController.getType');


});
