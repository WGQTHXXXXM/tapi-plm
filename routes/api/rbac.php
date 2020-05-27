<?php

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api',
    'middleware' => [
        'auth:api',
    ]
], function ($api) {
    //角色创建
    $api->post('role', 'RbacController@roleCreate')
        ->name('RbacController.roleCreate');

    //角色删除
    $api->delete('role/{roleId}', 'RbacController@roleDelete')
        ->name('RbacController.roleDelete');

    //角色更新
    $api->put('role/{roleId}', 'RbacController@roleUpdate')
        ->name('RbacController.roleUpdate');

    //角色查看
    $api->get('role/{roleId}', 'RbacController@roleView')
        ->name('RbacController.roleView');

    //角色列表------------------
    $api->get('role', 'RbacController@RoleIndex')
        ->name('RbacController.RoleIndex');
//////////////////////
    //通过角色Id查询相关联的用户-------------------
    $api->get('role/{roleId}/user', 'RbacController@roleUserIndex')
        ->name('RbacController.roleUserIndex');

    //给角色添加一个或多个用户
    $api->post('role/{roleId}/user', 'RbacController@roleUserAdd')
        ->name('RbacController.roleUserAdd');

    //给角色移除一个或多个用户
    $api->delete('role/{roleId}/user', 'RbacController@roleUserDel')
        ->name('RbacController.roleUserDel');

    //用户在某个项目下的角色
    $api->get('project/{pid}/roleuser/{uid}', 'RbacController@getUserRole')
        ->name('RbacController.getUserRole');
//////////////////////////////
    //添加权限
    $api->post('rsc', 'RbacController@rscCreate')
        ->name('RbacController.rscCreate');

    //通过主键删除权限
    $api->delete('rsc/{rscId}', 'RbacController@rscDelete')
        ->name('RbacController.rscDelete');

    //修改权限
    $api->put('rsc/{rscId}', 'RbacController@rscUpdate')
        ->name('RbacController.rscUpdate');

    //通过主键id查询权限
    $api->get('rsc/{rscId}', 'RbacController@rscView')
        ->name('RbacController.rscView');

    //带分页的权限列表
    $api->get('rsc', 'RbacController@rscIndex')
        ->name('RbacController.rscIndex');
//////////////////
    //给角色分配权限
    $api->post('role/{roleId}/rsc', 'RbacController@roleRscAdd')
        ->name('RbacController.roleRscAdd');

    //给角色移除权限
    $api->delete('role/{roleId}/rsc', 'RbacController@roleRscDelete')
        ->name('RbacController.roleRscDelete');

    //查询角色的权限
    $api->get('role/{roleId}/rsc', 'RbacController@roleRscIndex')
        ->name('RbacController.roleRscIndex');

//////////////////
    //查询用户拥有的权限列表
    $api->get('rscuser', 'RbacController@userRscIndex')
        ->name('RbacController.userRscIndex');

    //权限校验
    $api->get('rscuser/check', 'RbacController@userRscCheck')
        ->name('RbacController.userRscCheck');

//////////////////////
    //项目列表
    $api->get('project', 'RbacController@projectIndex')
        ->name('RbacController.projectIndex');

    $api->get('projects/{id}/users', 'RbacController@projectsUsers')
        ->name('RbacController.projectsUsers');
});
