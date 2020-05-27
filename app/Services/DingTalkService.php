<?php

namespace App\Services;


use App\Models\AuthUser;
use App\Models\EmployeeCenter;
use App\Models\User;
use App\Models\Departments;
use App\Models\DepartmentUserRef;
use App\Models\UserCenter;
use EasyDingTalk\Application;
use Illuminate\Support\Facades\DB;

class DingTalkService
{
//////////////////事件名///////////////////////
    const USER_ADD =    'user_add_org';
    const USER_LEAVE =  'user_leave_org';
    const USER_MODIFY = 'user_modify_org';
    const DPT_ADD =     'org_dept_create';
    const DPT_REMOVE =  'org_dept_remove';
    const DPT_MODIFY =  'org_dept_modify';
////////////////////////////////////////////////////


    /**执行钉钉的操作事件
     * @param $payload
     */
    public function doDingEvent($payload)
    {
        //file_put_contents('../storage/logs/dingevent.log',serialize($payload));
        DB::transaction(function () use ($payload) {
            switch ($payload['EventType']) {
                case self::USER_ADD:
                    $this->dingAddUser($payload);
                    break;
                case self::USER_LEAVE:
                    $this->dingLeaveUser($payload);
                    break;
                case self::USER_MODIFY:
                    $this->dingModifyUser($payload);
                    break;
                case self::DPT_ADD:
                    $this->dingAddDpt($payload);
                    break;
                case self::DPT_REMOVE:
                    $this->dingRemoveDpt($payload);
                    break;
                case self::DPT_MODIFY:
                    $this->dingModifyDpt($payload);
                    break;
                default:
                    break;
            }
        });
    }

    /**添加员工
     * @param $payload
     */
    private function dingAddUser($payload)
    {
        $dingObj = app(Application::class);
        $arrUsers = $payload['UserId'];
        foreach ($arrUsers as $userId){
            $this->addUser($dingObj,$userId);
        }
    }

    /**添加一个人
     * @param $payload
     */
    private function addUser($dingObj,$userId)
    {
        $detail = $dingObj->user->get($userId);
        $fillDate = ['ding_userid'=>$detail['userid'], 'ding_unionid'=>$detail['unionid'],
            'name'=>$detail['name'],'phone'=>$detail['mobile'], 'status'=>User::USER_NORMAL];

        $oldUser = User::where(['ding_unionid'=>$detail['unionid']])->first();

        if(empty($oldUser)){//如果用户表里不存在加入
            $center = EmployeeCenter::retrieveByPhone($detail['mobile']);
            if(!empty($center)) {
                $fillDate['user_id'] = $center->guid;
                $fillDate['email'] = $center->employeeMailbox;
                $newUser = new User(['id'=>$center->guid]);
                $newUser->fill($fillDate)->save();
            }
        } else {//有可能入职的是曾经的员工可能
            $oldUser->fill($fillDate)->save();
        }
    }

    private function dingModifyUser($payload)
    {
        $dingObj = app(Application::class);
        $arrUsers = $payload['UserId'];
        $curTime = date('Y-m-d H:i:s',time());
        foreach ($arrUsers as $userId){
            $mdlUser = User::where(['ding_userid'=>$userId])->first();
            if(empty($mdlUser)){
                $this->addUser($dingObj,$userId);
                continue;
            }
            $updateStatus = $mdlUser->status;
            if($updateStatus == User::USER_LEAVE)
                $updateStatus = User::USER_NORMAL;
            $detail = $dingObj->user->get($userId);
            $mdlUser->fill(['ding_userid'=>$detail['userid'], 'ding_unionid'=>$detail['unionid'],
                'name'=>$detail['name'],'phone'=>$detail['mobile'], 'status'=>$updateStatus])->save();
            //更新部门
            DepartmentUserRef::where(['user_id'=>$mdlUser->id])->delete();

            $departments = $detail['department'];
            foreach ($departments as $dptDingId){
                $dptId = Departments::where(['ding_id'=>$dptDingId])->first()->id;
                $mdlUser->department()->attach($dptId,['created_at'=>$curTime,'updated_at'=>$curTime,
                    'id'=>(new DepartmentUserRef())->generateUuid()]);
            }
        }
    }

    private function dingLeaveUser($payload)
    {
        $arrUsers = $payload['UserId'];
        foreach ($arrUsers as $userId){
            User::where(['ding_userid'=>$userId])->update(['status'=>User::USER_LEAVE]);
        }
    }

    private function dingAddDpt($payload)
    {
        $dingObj = app(Application::class);
        $arrDpts = $payload['DeptId'];
        foreach ($arrDpts as $dptId){

            $detail = $dingObj->department->get($dptId);
            $fillDate = ['ding_id'=>$detail['id'], 'ding_pid'=>$detail['parentid'], 'name'=>$detail['name']];

            $newDpt = new Departments();
            $newDpt->fill($fillDate)->save();
        }
    }

    private function dingModifyDpt($payload)
    {
        $dingObj = app(Application::class);
        $arrDpts = $payload['DeptId'];
        foreach ($arrDpts as $dptId){
            $mdlDpt = Departments::where(['ding_id'=>$dptId])->first();
            $detail = $dingObj->department->get($dptId);
            $mdlDpt->fill(['ding_id'=>$detail['id'], 'ding_pid'=>$detail['parentid'],
                'name'=>$detail['name']])->save();
        }
    }

    private function dingRemoveDpt($payload)
    {
        $arrDpts = $payload['DeptId'];
        foreach ($arrDpts as $dptId){
            Departments::where(['ding_id'=>$dptId])->delete();
        }

    }

    /**处理钉钉同步命令
     * @param Application $dingObj
     */
    public function doSyncDingOrgUser()
    {
        try {
            $dingObj = app(Application::class);
            DB::transaction(function () use($dingObj) {
                //1.找到钉钉的数据和系统数据
                $dpt = $dingObj->department->get(1);
                $arrdptDing[$dpt['id']] = ['name' => $dpt['name'], 'ding_id' => $dpt['id'], 'ding_pid' => 0];
                $dpts = $dingObj->department->list(1, true)['department'];
                foreach ($dpts as $dpt) {
                    $arrdptDing[$dpt['id']] = ['name' => $dpt['name'], 'ding_id' => $dpt['id'], 'ding_pid' => $dpt['parentid']];
                }
                $arrDptSys = Departments::where([])->select('name', 'ding_id', 'ding_pid')->get()->keyBy('ding_id')->toArray();
                //2.数组序列化比较
                $arrdptDingSrl = array_map(function ($item) {
                    return serialize($item);
                }, $arrdptDing);
                $arrDptSysSrl = array_map(function ($item) {
                    return serialize($item);
                }, $arrDptSys);
                $arrDiffDing = array_diff_assoc($arrdptDingSrl, $arrDptSysSrl);//以钉钉为基础的差异
                $arrDiffSys = array_diff_assoc($arrDptSysSrl, $arrdptDingSrl);//以系统为基础的差异
                //3.1如果有差异处理，以钉钉为基础如果ding_id一样更新，不一样增加。
                foreach ($arrDiffDing as $key=>$item) {
                    if (array_key_exists($key, $arrDiffSys)) {//如果存在说明ding_id一样,说明部门内容不一样。更新
                        unset($arrDiffSys[$key]);//排除掉，如果遍历完还有说明在系统里是多余的，要删除
                        Departments::where(['ding_id' => $key])->update($arrdptDing[$key]);
                    } else {//如果没有就要增加
                        $newDpt = new Departments();
                        $newDpt->fill($arrdptDing[$key])->save();
                    }
                }
                //3.2如果遍历完还有说明在系统里是多余的，要删除
                if (!empty($arrDiffSys)) {
                    Departments::whereIn('ding_id', array_keys($arrDiffSys))->delete();
                }
                ////////////////////////////////////////////////
                //4.取到系统的用户和关系的数据
                $arrUserSys = [];
                $arrTempRefSys = [];
                User::where([])->select('id','ding_userid','ding_unionid','name','phone','email','status')->orderBy('id')
                    ->chunk(50,function ($user)use(&$arrUserSys){
                        $arrUserSys = array_merge($arrUserSys,$user->keyBy('ding_unionid')->toArray());
                    });
                DepartmentUserRef::from('department_user_ref as ref')->where([])->leftJoin('users', 'users.id', '=', 'ref.user_id')
                    ->leftJoin('departments as dpt','dpt.id','=','ref.department_id')
                    ->select('users.ding_unionid as dingUserUnionId','dpt.ding_id as dptId')->orderBy('ref.user_id')
                    ->chunk(50,function ($ref)use(&$arrTempRefSys){
                        $arrTempRefSys = array_merge($arrTempRefSys,$ref->toArray());
                    });
                $arrRefSys = [];
                foreach ($arrTempRefSys as $item){
                    $arrRefSys[$item['dptId']][] = $item['dingUserUnionId'];
                }
                //var_dump($arrRefSys);die;
                /*
                 * 5。查看每个钉钉部门下的人员与系统对比
                 * 如果系统没有这个员工，要增加员工并添加进部门
                 * 如果系统里有这个员工：
                 *      1》要从系统总员工变量删掉，最后统计系统总员工变量还剩下的员，要把剩下的status改成离职
                 *      2》要看这个员工与钉钉的数据是否一致，不一致更新
                 *      3》部门里是否有这个员工如果没有加，如果有从系统部门人员变量里删，最后这个系统部门人员变量剩下的要踢出部门
                 */
                $delUser = $arrUserSys;//复制一份员工数据，如果钉钉里有这个删除，最后
                foreach ($arrdptDing as $dpt){//遍历钉钉部门下的员工
                    for ($i=0;;$i++){//每页20条，下页是否还有更多员工
                        $tmpDptUsers = $dingObj->user->getDetailedUsers($dpt['ding_id'],$i*100,$i*100+100);
                        $tmpUsers = $tmpDptUsers['userlist'];
                        foreach ($tmpUsers as $user){//部门员工与
                            $unionId = $user['unionid'];
                            if (array_key_exists($unionId,$arrUserSys)){//之前别的部门有可能已经存过了
                                if (!empty($delUser[$unionId]))//如果不是空要删掉，看最后系统里还有几个员工是钉钉里没有的
                                    unset($delUser[$unionId]);
//                                if(!empty(array_diff_assoc($arrUserSys[$unionId],$user))){//说明钉钉与系统数据不一样，要更新
//                                    User::where(['ding_unionid'=>$unionId])->update(['ding_userid'=>$user['userid'], 'ding_unionid'=>$user['unionid'],
//                                        'name'=>$user['name'],'phone'=>$user['mobile']]);
//                                }
                                if(empty($arrRefSys[$dpt['ding_id']])||false === array_search($unionId,$arrRefSys[$dpt['ding_id']])){//说明系统部门里没有这个人
                                    $newRef = new DepartmentUserRef();
                                    $dptId = Departments::where(['ding_id'=>$dpt['ding_id']])->first()->id;
                                    $newRef->fill(['department_id'=>$dptId, 'user_id'=>$arrUserSys[$unionId]['id']])->save();
                                } else {//如果部门里有这个人，删掉数组里这个值，最后总结系统里的部门哪里还有人跟钉钉不一样
                                    unset($arrRefSys[$dpt['ding_id']][array_search($unionId,$arrRefSys[$dpt['ding_id']])]);
                                }
                            } else {//如果钉钉里有，系统里没有,要新增数据
                                $center = EmployeeCenter::retrieveByPhone($user['mobile']);
                                if(!empty($center)) {
                                    $newUser = new User(['id'=>$center->guid]);
                                    $newUser->fill(['ding_userid'=>$user['userid'], 'ding_unionid'=>$user['unionid'],
                                        'name'=>$user['name'],'phone'=>$user['mobile'], 'status'=>User::USER_NORMAL,
                                        'email'=>empty($center->employeeMailbox)?'':$center->employeeMailbox/*有的用户邮箱就会为空*/,
                                        'user_id'=>empty($center->guid)?'':$center->guid])->save();
                                    $newRef = new DepartmentUserRef();
                                    $dptId = Departments::where(['ding_id'=>$dpt['ding_id']])->first()->id;
                                    $newRef->fill(['department_id'=>$dptId,'user_id'=>$newUser->id])->save();
                                }else{
                                    dump($user['mobile'].$user['name']);
                                }
                            }
                        }
                        if ($tmpDptUsers['hasMore']==false)
                            break;
                    }
                }
                //6.把钉钉里没有的系统里有的员工，改成离职状态
                if (!empty($delUser))
                    User::whereIn('ding_unionid',array_keys($delUser))->update(['status'=>User::USER_LEAVE]);
                //7.把钉钉部门里没有系统部门里有的员工删掉
                foreach ($arrRefSys as $dptDingId=>$dingUnionids){
                    foreach ($dingUnionids as $dingUnionid){
                        $tmpDptId = Departments::where(['ding_id'=>$dptDingId])->first()->id;
                        if(empty($dingUnionid)){//空部门
                            DepartmentUserRef::where(['id'=>$tmpDptId])->delete();
                            continue;
                        }
                        $tmpUserId = User::where(['ding_unionid'=>$dingUnionid])->first()->id;
                        DepartmentUserRef::where(['department_id'=>$tmpDptId,'user_id'=>$tmpUserId])->delete();
                    }
                }
            });
        } catch (\LogicException $e) {
            Log::info('DingTalkService::doSyncDingOrgUser error: '.$e->getMessage().'\n');
            throw new \LogicException('处理钉钉同步命令:'.$e->getMessage());
        }


    }

}