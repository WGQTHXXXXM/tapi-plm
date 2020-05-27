<?php

namespace App\Models;

use App\Exceptions\RestApiException;
use App\Models\Abstracts\Model;
use App\Models\Abstracts\RestApiModel;
use App\Models\Abstracts\RestApiModelWithConsul;
use App\Services\WikiService;
use Illuminate\Http\Request;

class Rbac extends RestApiModelWithConsul
{
    ////////服务//////////
    protected static $service_name = 'internal-permission-service';

    protected static $paginationKeyNames = [
        'request' => [
            'page' => 'page',
            'perPage' => 'pageSize',
        ],
        'response' => [
            'total' => 'total',
            'data' => 'list',
        ],
    ];

//    protected static function getBaseUri()
//    {
//        return 'http://172.16.7.244:8080/internal-permission-service/v2/';
//    }


    protected $primaryKey = 'guid';

    public static $apiMap = [
        //permission-controller : 03.查询用户拥有的权限与权限校验相关接口
        'userRscIndex' => ['method' => 'GET', 'path' => 'api/permission'],//查询用户拥有的权限列表
        'userRscCheck' => ['method' => 'GET', 'path' => 'api/permission/check'],//权限校验
        //resource-controller : 02.权限相关接口k
        'rscIndex' => ['method' => 'GET', 'path' => 'api/resource'],//带分页的权限列表
        'rscCreate' => ['method' => 'POST', 'path' => 'api/resource'],//添加权限
        'rscDelete' => ['method' => 'DELETE', 'path' => 'api/resource/:rsc_id'],//通过主键删除权限
        'rscView' => ['method' => 'GET', 'path' => 'api/resource/:rsc_id'],//通过主键id查询权限
        'rscUpdate' => ['method' => 'PUT', 'path' => 'api/resource/:rsc_id'],//修改权限

        //role-controller : 01.基于项目的角色的相关接口
        'roleIndex' => ['method' => 'GET', 'path' => 'api/role'],//带分页的权限列表
        'roleCreate' => ['method' => 'POST', 'path' => 'api/role'],//添加角色
        'roleDelete' => ['method' => 'DELETE', 'path' => 'api/role/:role_id'],//通过主键删除角色
        'roleUpdate' => ['method' => 'PUT', 'path' => 'api/role/:role_id'],//修改角色
        'roleView' => ['method' => 'GET', 'path' => 'api/role/:role_id'],//通过id查询角色
        'getUsersByRoleIds' => ['method' => 'GET', 'path' => 'api/role/roles/users'],//通过角色ids ，查询用户
        'getRolesByUserId' => ['method' => 'GET', 'path' => 'api/role/roles/userId'],//通过用户id查询所在角色
        'getUsersByRole' => ['method' => 'GET', 'path' => 'api/role/roles/users'],  //通过角色id查询所有用户
        'getUsersByProjectId' => ['method' => 'GET', 'path' => 'api/role/roles/roleUsers'],  //通过项目标识查询项目下的用户

        ////////////
        'roleRscDelete' => ['method' => 'DELETE', 'path' => 'api/role/permission/:roleId'],//给角色移除权限
        'roleRscIndex' => ['method' => 'GET', 'path' => 'api/role/permission/:roleId'],//查询角色的权限
        'roleRscAdd' => ['method' => 'POST', 'path' => 'api/role/permission/:roleId'],//给角色分配权限
        /////////////////
        'roleUserIndex' => ['method' => 'GET', 'path' => 'api/role/users'],//通过角色Id查询相关联的用户(管理员权限)
        'roleUserAdd' => ['method' => 'POST', 'path' => 'api/role/users/:roleId'],//给角色添加一个或多个用户
        'roleUserDel' => ['method' => 'DELETE', 'path' => 'api/role/users/:roleId'],//给角色移除一个或多个用户
        'getUserRole' => ['method' => 'GET', 'path' => 'api/role/roles/userId'],//给角色移除一个或多个用户
    ];

//////////////////////////////////
///
///
    //通过项目标识查询项目下的用户
    public static function getUsersByProjectId($projectId)
    {
        $response = self::getCollection('getUsersByProjectId', [
            'projectIdentify' => $projectId,
        ]);

        return $response;
    }

    // 获取角色信息
    public static function getRolesByUserId($userId, $projectId)
    {

        $response = self::getCollection('getRolesByUserId', [
            'userId' => $userId,
            'projectIdentify' => $projectId
        ]);

        return $response;
    }

    // 批量获取
    public static function getUsersByRoleIds(Array $roleIds)
    {
        $ids = implode(',', $roleIds);

        $response = self::getCollection('getUsersByRoleIds', [
            'roleIds' => $ids
        ]);

        return $response;
    }


    /*
     * 查询用户拥有的权限列表
     */
    public function userRscIndex($params)
    {
        try {
            $response = self::getCollection('userRscIndex', $params);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

    /*
     * 权限校验
     */
    public function userRscCheck($params)
    {
        try {
            $response = self::getItem('userRscCheck', $params);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

/////////////////////////////////

    /**
     *查询角色的权限
     */
    public function roleRscIndex($roleId)
    {
        $querys = [':roleId' => $roleId];
        try {
            $response = self::getCollection('roleRscIndex', $querys);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

    /**
     * 给角色分配权限
     */
    public function roleRscAdd($params, $roleId)
    {
        try {
            $response = self::getData('roleRscAdd', [':roleId' => $roleId], $params);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

    /**
     * 通过id删除角色
     */
    public function roleRscDelete($params, $roleId)
    {
        try {
            $response = self::getData('roleRscDelete', [':roleId' => $roleId], $params);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

////////////////////////////

    /**
     *通过角色Id查询相关联的用户
     */
    public function roleUserIndex($roleId)
    {
        $querys = ['roleId' => $roleId];
        try {
            $response = self::getCollection('roleUserIndex', $querys);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        foreach ($response as $item) {//增加项目名和创建用户名，如果没有的项目和用户直接显示ID
            $mdlUser = User::where(['user_id' => $item->userId])->first();
            $userName = empty($mdlUser) ? $item->userId : $mdlUser->name;
            $item->userName = $userName;
        }

        return $response;
    }

    /**
     * 给角色添加一个或多个用户
     */
    public function roleUserAdd($params, $roleId)
    {
        try {
            $response = self::getData('roleUserAdd', [':roleId' => $roleId], $params);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

    /**
     * 通过id删除角色
     */
    public function roleUserDel($params, $roleId)
    {
        try {
            $response = self::getData('roleUserDel', [':roleId' => $roleId], $params);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

    /**
     * 获得项目下用户角色
     */
    public function getUserRole($pid, $uid)
    {
        $querys = ['userId' => $uid, 'projectIdentify' => $pid];
        try {
            $response = self::getCollection('getUserRole', $querys);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }
///////////////////////////

    /**
     *角色列表
     */
    public function roleIndex(Request $request)
    {
        $querys = [
            'page' => empty($request->query('page')) ? 1 : $request->query('page'),
            'per_page' => empty($request->query('per_page')) ? config('app.default_per_page') : $request->query('per_page'),
            'projectIdentify' => $request->query('projectIdentify')];
        try {
            $response = self::getPaginator('roleIndex', $querys);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        $response->appends(['projectIdentify' => $request->query('projectIdentify')])->links();//附加参数到分页链接中
        //项目列表
        $projectIndex = app(WikiService::class)->getProjectIndex($request->header('authorization'));
        $projectIndex = collect($projectIndex)->pluck('name', 'id')->toArray();
        foreach ($response as $item) {//增加项目名和创建用户名，如果没有的项目和用户直接显示ID
            $projectName = empty($projectIndex[$item->projectIdentify]) ? $item->projectIdentify : $projectIndex[$item->projectIdentify];
            $item->projectName = $projectName;
            $mdlUser = User::where('user_id', $item->creator)->orWhere('id', $item->creator)->first();
            $creatorName = empty($mdlUser) ? $item->creator : $mdlUser->name;
            $item->creatorName = $creatorName;
        }
        return $response;
    }

    /**创建角色
     */
    public function roleCreate($params)
    {
        $response = self::getData('roleCreate', [], $params);
        return $response;
    }

    /**
     * @param $params
     * @return bool|null
     */
    public function roleUpdate($params, $id)
    {
        try {
            $response = self::getData('roleUpdate', [':role_id' => $id], $params);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

    /**
     * 通过id查询角色
     */
    public function roleView(Request $request, $id)
    {
        $roleId = [':role_id' => $id];
        try {
            $response = self::getItem('roleView', $roleId);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        //项目列表
        $projectIndex = app(WikiService::class)->getProjectIndex($request->header('authorization'));
        $projectIndex = collect($projectIndex)->pluck('name', 'id')->toArray();
        $projectName = empty($projectIndex[$response->projectIdentify]) ?
            $response->projectIdentify : $projectIndex[$response->projectIdentify];
        $response->projectName = $projectName;
        $mdlUser = User::where(['user_id' => $response->creator])->first();
        $creatorName = empty($mdlUser) ? $response->creator : $mdlUser->name;
        $response->creatorName = $creatorName;

        return $response;
    }

    /**
     * 通过id删除角色
     */
    public function roleDelete($id)
    {
        try {
            $response = self::getData('roleDelete', [':role_id' => $id]);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }
//////////////

    /**
     *角色列表
     */
    public function rscIndex(Request $request)
    {
        $querys = [
            'page' => empty($request->query('page')) ? 1 : $request->query('page'),
            'pageSize' => empty($request->query('per_page')) ? config('app.default_per_page') : $request->query('per_page'),
            'projectIdentify' => $request->query('projectIdentify')];
        try {
            $response = self::get('rscIndex', $querys);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

    /**创建角色
     */
    public function rscCreate($params)
    {
        try {
            $response = self::getData('rscCreate', [], $params);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

    /**修改权限
     * @param $params
     * @return bool|null
     */
    public function rscUpdate($params, $id)
    {
        try {
            $response = self::getData('rscUpdate', [':rsc_id' => $id], $params);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

    /**
     * 通过id查询角色
     */
    public function rscView($id)
    {
        $roleId = [':rsc_id' => $id];
        try {
            $response = self::getItem('rscView', $roleId);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

    /**
     * 通过id删除角色
     */
    public function rscDelete($id)
    {
        try {
            $response = self::getData('rscDelete', [':rsc_id' => $id]);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }


}
