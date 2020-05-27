<?php

namespace App\Http\Controllers\Api;

use App\Models\Email;
use App\Models\Rbac;
use App\Models\TaskFlow;
use App\Models\User;
use App\Models\UserCenter;
use App\Models\Wiki;
use App\Services\CommonService;
use App\Transformers\DefaultTransformer;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Redis;

class UsersController extends Controller {

    protected $userService;

    /**
     * UsersController constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**测试用4
     * @return mixed
     */
    public function aaa(Request $request)
    {
        $data =  Rbac::getUsersByProjectId($request->get('pid'));
        dump($data);
        Email::approvalApproval(['songwei@singulato.com'],'审批名', 'sfrsp');
        Email::approvalFollow(['songwei@singulato.com'],'审批名', 'sfrgz');die;
//        $a = User::query()->get();
//        foreach ($a as $b){
//            $client = new Client(['base_uri' => 'https://app.singulato.com/employee/center/v1/employee/guid/']);
//            $res = $client->request('get', $b->id,['Accept'=>'application/json']);
//            $c = \GuzzleHttp\json_decode($res->getBody()->getContents())->businessObj;
//            if(empty($c))
//            {
//                var_dump($b->name);
//            }
//        }die;
        //return Wiki::getUser(['username'=>'songwei']);
        //return Wiki::getContentPms(['contentId'=>'5046276','spaceKey'=>'plm','atl_token'=>'331bae0da917933cf64c817fdfc1eb4083b88bfd']);
        //return Wiki::loginWiki();
//        return Wiki::setContentPms([
//            'viewPermissionsUsers'=>'2c9090bc6cae390b016cae3ad2fb0000,2c9090bc6d0465e0016d053ff15d0097',
//            'editPermissionsUsers'=>'2c9090bc6cae390b016cae3ad2fb0000,2c9090bc6d0465e0016d053ff15d0097',
//            'viewPermissionsGroups'=>'',
//            'editPermissionsGroups'=>'',
//            'contentId'=>'2884257',
//        ]);
//        return Wiki::addUsersToGroup([
//            "membersOfGroupTerm"=>"nt",
//            "usersToAdd"=>"zhangqinchao",
//        ]);
//        return Wiki::removeUserFromGroup([
//            "membersOfGroupTerm"=>"nt",
//            "username"=>"weifuxing",
//        ]);
//        Wiki::downloadFile('/download/attachments/2884223/CAP%282%29.xlsx?version=1&modificationDate=1574941500150&api=v2',
//            $request->header('authorization'));
//        return UserCenter::login(['name'=>'songwei','password'=>'123456a']);
    }

	/**
	 * 根据用户中心用户ID获取PLM人员信息
	 * @param Request $request
	 * @return \Dingo\Api\Http\Response
	 */
	public function getUser(Request $request)
	{
		//return $this->response->item($this->user(), new DefaultTransformer());
        return $this->response->array(['data'=>User::$detailUser]);
	}

    /**
     * 获取用户列表
     * @param Request $request
     * @return $this
     */
    public function index(Request $request)
    {
		$paginator = app(CommonService::class)->doPaginator($request);
    	$users = $this->userService->getUser($request->all(), $paginator);
        return $this->response->paginator($users, new DefaultTransformer())->setStatusCode(200);
    }

	/**
	 * 返回所有用户
	 * @param Request $request
	 * @return mixed
	 */
    public function all(Request $request)
	{
		$arrOnly = [
			'user_id',
			'name',
			'phone',
			'email',
			'status',
			'projectId',
		];
		$arrColumn = [
			'user_id' => 'string|max:64',
			'name'    => 'string|max:128',
			'phone'   => 'string|max:64',
			'status'   => 'string|max:64',
			'projectId'   => 'string|max:64',
			'email'   => 'string',
		];
		$arrMessage = [
			'user_id.max' => '用户ID最大长度64',
			'name.max'    => '用户姓名最大长度128',
		];
		$params = $this->doValidate($request->all(), $arrOnly, $arrColumn, $arrMessage);
		$users = $this->userService->getAllUser($params);
		return $this->response->array($users, new DefaultTransformer());
	}

    /**
     * 获取某个用户信息
     * @param $id
     * @return User $user
     */
    public function show($id)
    {
		$user = $this->userService->getUserById($id);
        return $this->response->item($user, new DefaultTransformer())->setStatusCode(200);
    }

	/**
	 * 新建用户
	 * @param Request $request
	 * @return $this|void
	 */
	public function store(Request $request)
	{
		$arrOnly = [
		'user_id',
		'name',
		'phone',
		'email',
	];
		$arrColumn = [
			'user_id' => 'required|string|max:64',
			'name'    => 'required|string|max:128',
			'phone'   => 'required|string|max:64',
			'email'   => 'email|nullable',
		];
		$arrMessage = [
			'user_id.required' => '用户ID不能为空',
			'name.required'    => '用户姓名不能为空',
			'phone.required'   => '用户手机号不能为空',
			'user_id.max'      => '用户ID最大长度64',
			'name.max'         => '用户姓名最大长度128',
			'email.email'      => '邮箱格式有误',
		];
		$params = $this->doValidate($request->all(), $arrOnly, $arrColumn, $arrMessage);
		$params['status'] = User::USER_NORMAL;
		$userService = new UserService();

		//判断是否已有该用户，有则不再创建
		$userInfo = $userService->getUserByUserId($params['user_id']);
		if (isset($userInfo['id']) && !empty($userInfo['id'])) {
			$id = $userInfo->id;
			throw new \Exception('已有该用户了，请不要重复创建');
		} else {
			$result = $userService->addUser($params);
			$id = $result->id;
		}

		return $this->response->array(['id' => $id]);
	}

	/**
     * 修改用户信息
     *
     * @param Request $request
	 * @param $id
     * @return \Dingo\Api\Http\Response
     */
    public function update(Request $request, $id)
    {
		$arrOnly = [
			'name',
			'phone',
			'email',
		];
		$arrColumn = [
			'name'    => 'string|max:128|nullable',
			'phone'   => 'string|max:64|nullable',
			'email'   => 'email|nullable',
		];
		$arrMessage = [
			'phone.max'        => '用户手机号最大长度64',
			'name.max'         => '用户姓名最大长度128',
			'email.email'      => '邮箱格式有误',
		];
		$params = $this->doValidate($request->all(), $arrOnly, $arrColumn, $arrMessage);
		$userService = new UserService();

		//判断是否已有该用户，没有则不能编辑
		$userInfo = $userService->getUserById($id);
		if (isset($userInfo['id']) && !empty($userInfo['id'])) {
			if ($userInfo['status'] == User::USER_INVALID) {//无效的用户不能编辑
				throw new \Exception('没有该用户');
			}
			$where = ['id' => $id];
			$userService->editUser($where, $params);
		} else {
			throw new \Exception('没有该用户');
		}

        return $this->response->noContent();
    }

    /**
     * 删除用户
     * @param $ids
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function destroy($ids)
    {
        $result = $this->userService->delUser($ids);

        return $this->response->noContent();
    }

    /**改变用户状态
     * @param $id
     * @return \Dingo\Api\Http\Response
     */
    public function changeStatus(Request $request)
    {
        $arrOnly = [
            'user_ids',
            'status',
        ];
        $arrColumn = [
            'user_ids'    => 'string|required',
            'status'   => 'string|required',
        ];
        $arrMessage = [
            'user_ids.required'        => '用户不可以为空',
            'status.required'         => '状态不可以为空',
        ];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, $arrMessage);

        $this->userService->changeStatus($params);

        return $this->response->noContent();
    }
}
