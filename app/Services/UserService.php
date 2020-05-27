<?php

namespace App\Services;

use App\Models\Rbac;
use App\Models\User;
use App\Models\UserCenter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserService
{
	/**
	 * 查询列表|统计总数
	 * @param array $params
	 * @param array $paginator
	 * @param bool $count
	 * @return mixed
	 * @throws \Exception
	 */
	public function basicInquire($params = array(), $paginator = [], $count = false)
	{
		try {
			$user = new User();
			$where = $whereIn = $whereLike = $whereBetween = $orWhere = array();
			$whereRef = $whereInRef = $whereLikeRef = $whereBetweenRef = $orWhereRef = $with = array();
			$whereCondition = app(CommonService::class)->sqlPrepare($params, $user);
			//精确条件查询
			$where = $whereCondition['where'];
			//in查询条件
			$whereIn = $whereCondition['whereIn'];
			//模糊查询条件
			$whereLike = $whereCondition['whereLike'];
			//区间查询条件
			$whereBetween = $whereCondition['whereBetween'];
			//或条件查询
			$orWhere = $whereCondition['orWhere'];

			//关联表查询
			$whereRef = $whereCondition['whereRef'];
			$whereInRef = $whereCondition['whereInRef'];
			$whereLikeRef = $whereCondition['whereLikeRef'];
			$whereBetweenRef = $whereCondition['whereBetweenRef'];
			$orWhereRef = $whereCondition['orWhereRef'];
			$with = [
				//'eloquent' => 'pivot',//指定关联关系，''空字符串默认一对一 one2one一对一 one2many一对多 pivot多对多
				'table' => ['department'],//关联关系
//				'with' => []//关系表查询条件
			];
			if (!empty($whereRef)) {
				$with['with']['where'] = $whereRef;
			}
			if (!empty($whereInRef)) {
				$with['with']['whereIn'] = $whereInRef;
			}
			if (!empty($whereLikeRef)) {
				$with['with']['like'] = $whereLikeRef;
			}
			if (!empty($whereBetweenRef)) {
				$with['with']['between'] = $whereBetweenRef;
			}
			if (!empty($orWhereRef)) {
				$with['with']['or'] = $orWhereRef;
			}

			//$where['status'] = User::USER_NORMAL;
			$query = $user->where($where);
			$result = app(CommonService::class)->basicQuery($query, $with, $count, $paginator, $whereIn, $whereLike, $whereBetween, $orWhere);
			return $result;
		} catch (\Exception $e) {
			Log::info('No user data: '.$e->getMessage().'\n');
			throw new \Exception('没有用户数据'.$e->getMessage());
		}
	}

	/**
	 * 用户列表
	 * @param array $params
	 * @param array $paginator
	 * @return mixed
	 */
    public function getUser($params = array(), $paginator = [])
	{
		return $this->basicInquire($params, $paginator);
    }

	/**
	 * 返回所有用户，不分页
	 * @return User
	 */
    public function getAllUser($params = [])
	{
		$query = User::query();//->where(['status' => User::USER_NORMAL]);
		if (isset($params['name']) && !empty($params['name'])) {
			$query = $query->where('name', 'like', '%'.$params['name'].'%');
		}
		if (isset($params['phone']) && !empty($params['phone'])) {
			$query = $query->where('phone', $params['phone']);
		}
		if (isset($params['email']) && !empty($params['email'])) {
			$query = $query->where('email', 'like', '%'.$params['email'].'%');
		}
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $query = $query->where('user_id', $params['user_id']);
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $query = $query->where('status', $params['status']);
        }
        if (isset($params['projectId']) && !empty($params['projectId'])) {
            $data =  Rbac::getUsersByProjectId($params['projectId']);
            $userIds = $data->pluck('userId')->toArray();
            $query->whereIn('id',$userIds);
        }
		$result = $query->get();
		return is_null($result) ? [] : $result;
	}

	/**
	 * 根据id查询用户
	 * @param $id
	 * @return Collection|\Illuminate\Database\Eloquent\Model|mixed|null|static|static[]
	 */
	public function getUserById($id)
	{
		$userModel = new User();
		$where = ['id' => $id];
		$result = $userModel->where($where)->with('department')->first();
		return is_null($result) ? $userModel : $result;
	}

    /**
     * 根据user_id查询用户
     * @param $user_id
     * @return Collection|\Illuminate\Database\Eloquent\Model|mixed|null|static|static[]
     */
    public function getUserByUserId($user_id)
    {
        $userModel = new User();
        $where = ['user_id' => $user_id];
        $result = $userModel->where($where)->first();
        return is_null($result) ? $userModel : $result;
    }

    /**
     * 根据name查询用户
     * @param $user_id
     * @return Collection|\Illuminate\Database\Eloquent\Model|mixed|null|static|static[]
     */
    public function getUserByName($name)
    {
        $userModel = new User();
        $where = ['name' => $name];
        $result = $userModel->where($where)->first();
        return is_null($result) ? $userModel : $result;
    }

	/**
	 * 创建用户
	 * @param $params
	 * @return User
	 */
	public function addUser($params)
	{
		$user = new User();
		DB::transaction(function () use ($params, $user) {
			try {
				$user->fill($params)->save();
			} catch (\Exception $e) {
				Log::info('Create user error: '.$e->getMessage().'\n');
				throw new \Exception('创建用户失败'.$e->getMessage());
			}
		});
		return $user;
	}

	/**
	 * 编辑用户
	 * @param $where
	 * @param $params
	 */
	public function editUser($where, $params)
	{
		DB::transaction(function () use ($where, $params) {
			try {
				$user = new User();
				$user->where($where)->update($params);
				return $user;
			} catch (\Exception $e) {
				Log::info('Edit user error: '.$e->getMessage().'\n');
				throw new \Exception('编辑用户失败'.$e->getMessage());
			}
		});
	}

	/**
	 * 删除用户，支持批量删除
	 * @param $ids
	 * @return bool
	 * @throws \Exception
	 */
	public function delUser($ids)
	{
		try {
			$ids = explode(",", $ids);
			DB::transaction(function () use ($ids) {
				foreach ($ids as $id) {
					$user = new User();
					$where = ['id' => $id];
					$update = ['status' => User::USER_INVALID];
					$user->where($where)->update($update);
				}
			});
		} catch (\Exception $e) {
			Log::info('Delete user error: '.$e->getMessage().'\n');
			throw new \Exception('删除用户失败'.$e->getMessage());
		}
		return true;
	}

    /**用户激活切换
     * @param $id
     * @return bool
     * @throws \Exception
     */
	public function changeStatus($params)
    {
        try{
            $userIds = explode(',',$params['user_ids']);
            User::whereIn('id',$userIds)->where('status','<>',User::USER_LEAVE)->update(['status'=>$params['status']]);
        }catch (\LogicException $e){
            throw new \LogicException('改变用户状态失败');
        }
        return true;
    }


    public function getUsersByProjectId($projectId)
    {
        $data =  Rbac::getUsersByProjectId($projectId);
        $userIds = $data->pluck('userId')->toArray();
        if(!$userIds){
            return [];
        }

        $users =  UserCenter::getUsers($userIds);

        return $users;
    }
}
