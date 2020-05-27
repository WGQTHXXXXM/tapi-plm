<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\Role;
use App\Models\RoleUserRef;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RoleService
{

    /*
     * 通过id找模型
     */
    public function findModel($id)
    {
        if (($model = Role::find($id)) !== null) {
            return $model;
        } else {
            throw new LogicException('没找到该角色！');
        }
    }

    /**添加用户
     * @param $param
     */
    public function addUser($params)
    {
        DB::transaction(function () use ($params) {
            $type = $params['type'];
            $strUsers = $params['users'];
            $roleId = $params['role_id'];
            $arrUsers = explode(',', $strUsers);
            foreach ($arrUsers as $userId) {
                $newMdl = new RoleUserRef();
                if (empty(User::find($userId)))
                    throw new LogicException('没找到用户：' . $userId);
                try {
                    $newMdl->fill(['user_id' => $userId, 'role_id' => $roleId, "type" => $type])->save();
                } catch (LogicException $e) {
                    throw new LogicException('保存出错：' . $e->getMessage());
                }
            }
        });
    }

    /**
     * 删除角色
     */
    public function deleteRole($id)
    {
        $num = 0;
        DB::transaction(function () use ($id, &$num) {
            $this->findModel($id)->delete();
            $num = RoleUserRef::where(['role_id' => $id])->delete();
        });
        return $num;
    }

    /**
     * 角色列表
     */
    public function search($request, $paginator = [])
    {

        $whereData = $request->only(['project_id', 'project_code', 'name']);
        $query = Role::query()->with('createdBy:id,name')->where($whereData);

        $perPage = isset($paginator['per_page']) ? $paginator['per_page'] : config('app.default_per_page');
        $res = $query->paginate($perPage);

        return $res;
    }

}