<?php

namespace App\Http\Controllers\Api;

use App\Console\Commands\UpdateNewUserTbl;
use App\Models\Auth;
use App\Models\AuthUser;
use App\Models\EmployeeCenter;
use App\Models\User;
use App\Services\DingTalkService;
use EasyDingTalk\Application;
use Illuminate\Http\Request;
use App\Jobs\DingEvent;

class DingTalkController extends Controller
{
    //const URL_CALL_BACK = 'https://app.dev.singulato.com/plm/api/dingtalk/eventReceive';
    //const URL_CALL_BACK = "http://plm.api.swdev.singulato.com/api/dingtalk/eventReceive";
    /**
     *注册事件
     * @return mixed
     */
    public function register(Application $dingObj)
    {
        $params = [
            'call_back_tag' => ['user_add_org', 'user_modify_org', 'user_leave_org',
                'user_add_org','org_dept_remove','user_modify_org'],
            'url' => ENV('APP_URL').'/api/dingtalk/eventReceive',
        ];

        $res = $dingObj->callback->register($params);

        return $res;

    }

    /**查看注册的事件
     * @param Application $dingObj
     * @return mixed
     */
    public function getCallBack(Application $dingObj)
    {
        return $dingObj->callback->list();
    }

    /**更新事件
     * @param Request $request
     * @param Application $dingObj
     * @return mixed
     */
    public function updateCallBack(Request $request,Application $dingObj)
    {
        $arrEvent = explode(',',$request->all()['call_back_tag']);
        $app_url = ENV('APP_URL');
        if(!empty($request->input('appurl'))){
            $app_url = $request->input('appurl');
        }

        $params = [
            'call_back_tag' => $arrEvent,
            'url' => $app_url.'/api/dingtalk/eventReceive',
        ];
        return $dingObj->callback->update($params);
    }

    /**如果钉钉事件发送失败，可得到失败的事件名。
     * @param Application $dingObj
     * @return mixed
     */
    public function getFailEvent(Application $dingObj)
    {
        return $dingObj->callback->failed();
    }

    /**响应钉钉的事件
     * @return mixed
     */
    public function eventReceive(Request $request)
    {
        $dingObj = app(Application::class);
        // 获取 server 实例
        $server = $dingObj->server;
        $server->push(function ($payload) /*use($dtService)*/ {
            try {
                //file_put_contents('../storage/logs/dingevent.log',serialize($payload));
                DingEvent::dispatch($payload);
            } catch (\LogicException $e) {
                Log::info('DingTalkController::eventReceive error: '.$e->getMessage().'\n');
                throw new \LogicException('响应钉钉的事件:'.$e->getMessage());
            }
        });
        return $server->serve();
    }

    /**同步钉钉数据
     * @param DingTalkService $ser
     */
    public function doSyncDingOrgUser(DingTalkService $ser)
    {
        $ser->doSyncDingOrgUser();
    }

    public function updateNewUserTbl()
    {
        $users = User::query()->get();
        foreach ($users as $user){
            $e = EmployeeCenter::getUserByGuid($user->user_id);
            if(empty($e))
                continue;
            $user->email = $e->employeeMailbox;
            $user->save();
        }
    }
//    public function aaa(Application $dingObj)
//    {
//
//        return unserialize(file_get_contents('../storage/logs/dingevent.log'));
//    }

}
