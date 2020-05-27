<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests\Rbac\CreateRoleRequest;
use App\Models\Rbac;
use App\Services\UserService;
use App\Services\WikiService;
use Illuminate\Http\Request;


class RbacController extends Controller
{

    public function __construct(Rbac $rbacMdl)
    {
        $this->rbacMdl = $rbacMdl;
    }


    /*
     * 角色创建
     */
    public function roleCreate(CreateRoleRequest $request)
    {
        //检查
        $params = $request->all();
        $params['creator'] = $this->user()['user_id'];
        $this->rbacMdl->roleCreate($params);
        return $this->setViewData();
    }

    /*
     * 角色删除
     */
    public function roleDelete($id)
    {
        return $this->rbacMdl->roleDelete($id);
    }

    /*
     * 角色更新
     */
    public function roleUpdate(Request $request, $id)
    {
        //检查
        $arrOnly = ['description', 'projectIdentify', 'roleName'];
        $str = 'required|string|max:64';
        $arrColumn = ['description' => $str, 'projectIdentify' => $str, 'roleName' => $str];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);
        $params['creator'] = $this->user()['id'];
        return $this->rbacMdl->roleUpdate($params, $id);
    }

    /*
     * 角色查看roleUserRef
     */
    public function roleView(Request $request, $id)
    {
        return $this->rbacMdl->roleView($request, $id);
    }

    /*
     * 角色列表
     */
    public function RoleIndex(Request $request)
    {
        $arrOnly = ['projectIdentify'];
        $arrColumn = ['projectIdentify' => 'required|string|max:64'];
        $this->doValidate($request->all(), $arrOnly, $arrColumn, []);

        return $this->rbacMdl->roleIndex($request);

    }

    /*
     * 通过角色Id查询相关联的用户
     */
    public function roleUserIndex($roleId)
    {
        return $this->rbacMdl->roleUserIndex($roleId);
    }

    /*
     * 给角色添加一个或多个用户
     */
    public function roleUserAdd(Request $request, $roleId)
    {
        //检查
        $arrOnly = ['userIds'];
        $arrColumn = ['userIds' => 'required|array'];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);

        return $this->rbacMdl->roleUserAdd($params, $roleId);
    }

    /*
     * 给角色移除一个或多个用户
     */
    public function roleUserDel(Request $request, $roleId)
    {
        //检查
        $arrOnly = ['userIds'];
        $arrColumn = ['userIds' => 'required|array'];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);

        return $this->rbacMdl->roleUserDel($params, $roleId);
    }

    /**获得项目下用户角色
     * @param Request $request
     * @return bool|null
     */
    public function getUserRole($pid, $uid)
    {
        return $this->rbacMdl->getUserRole($pid, $uid);
    }
/////////////////////////////

    /*
     * 添加权限
     */
    public function rscCreate(Request $request)
    {
        //检查
        $arrOnly = ['description', 'name', 'projectIdentify'];
        $str = 'required|string|max:64';
        $arrColumn = ['description' => $str, 'name' => $str, 'projectIdentify' => $str];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);
        return $this->rbacMdl->rscCreate($params);
    }

    /*
     * 通过主键删除权限
     */
    public function rscDelete($id)
    {
        return $this->rbacMdl->rscDelete($id);
    }

    /*
     * 修改权限
     */
    public function rscUpdate(Request $request, $id)
    {
        //检查
        $arrOnly = ['description', 'name', 'projectIdentify'];
        $str = 'required|string|max:64';
        $arrColumn = ['description' => $str, 'name' => $str, 'projectIdentify' => $str];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);
        return $this->rbacMdl->rscUpdate($params, $id);
    }

    /*
     * 通过主键id查询权限
     */
    public function rscView($id)
    {
        return $this->rbacMdl->rscView($id);
    }

    /*
     * 带分页的权限列表
     */
    public function rscIndex(Request $request)
    {
        $arrOnly = ['projectIdentify'];
        $arrColumn = ['projectIdentify' => 'required|string|max:64'];
        $this->doValidate($request->all(), $arrOnly, $arrColumn, []);

        return $this->rbacMdl->rscIndex($request);
    }
///////////////////////////////

    /*
     * 查询角色的权限
     */
    public function roleRscIndex($roleId)
    {
        return $this->rbacMdl->roleRscIndex($roleId);
    }

    /*
     * 给角色分配权限
     */
    public function roleRscAdd(Request $request, $roleId)
    {
        //检查
        $arrOnly = ['resourceIds'];
        $arrColumn = ['resourceIds' => 'required|array'];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);

        return $this->rbacMdl->roleRscAdd($params, $roleId);
    }

    /*
     * 给角色移除权限
     */
    public function roleRscDelete(Request $request, $roleId)
    {
        //检查
        $arrOnly = ['resourceIds'];
        $arrColumn = ['resourceIds' => 'required|array'];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);

        return $this->rbacMdl->roleRscDelete($params, $roleId);
    }
//////////////////////////////

    /*
     * 查询用户拥有的权限列表
     */
    public function userRscIndex(Request $request)
    {
        //检查
        $arrOnly = ['roleId', 'guid'];
        $arrColumn = ['roleId' => 'required|numeric', 'guid' => 'required|string|max:64'];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);

        return $this->rbacMdl->userRscIndex($params);
    }

    /*
     * 权限校验
     */
    public function userRscCheck(Request $request)
    {
        //检查
        $arrOnly = ['roleId', 'guid', 'resourceName'];
        $arrColumn = ['roleId' => 'required|numeric', 'guid' => 'required|string|max:64',
            'resourceName' => 'required|string|max:64'];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);

        return $this->rbacMdl->userRscCheck($params);
    }

    /**
     * 项目列表
     */
    public function projectIndex(Request $request, WikiService $wikiSer)
    {
        $token = $request->header('authorization');
        $projectIndex = $wikiSer->getProjectIndex($token);
        return $this->response->array($projectIndex);
    }


    // 获取项目下的所有用户信息
    public function projectsUsers($projectId, UserService $service)
    {

        $data = $service->getUsersByProjectId($projectId);

        return $this->setViewData($data);
    }


}