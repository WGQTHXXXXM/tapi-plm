<?php

namespace App\Services;

use App\Exceptions\ClientHttpException;
use App\Models\Approval;
use App\Models\Email;
use App\Models\Rbac;
use App\Models\TaskFlow;
use App\Models\User;

class TaskService
{

    // 返回所有参与者
    public function participants($taskId)
    {
        $data = TaskFlow::participantsByTaskId($taskId);

        return $data;
    }

    public function show($taskId)
    {
        $task = TaskFlow::getTask($taskId);
        return $task;
    }

    // 审批决策
    public function decision($taskId, $data)
    {
        $insData = [
            'user_id' => $data['user_id'],
            'content' => $data['content'],
            'select_key' => $data['select_key']
        ];


        $task = TaskFlow::setTaskSelect($taskId, $insData);
        if(!empty($task)){
            $particpants=TaskFlow::participantsByTaskId($task['id']);

            $userIds = [];
            $roleUsers = [];
            foreach ($particpants as $p){
                if($p['type'] == Approval::PARTICPENT_INDIVIDUAL)
                    $userIds[]= $p['key_id'];
                else if($p['type'] == Approval::PARTICPENT_GROUP)
                    $roleUsers[] = $p['key_id'];
            }
            if(!empty($roleUsers)){
                $roleUsers = Rbac::getUsersByRoleIds($roleUsers);
                $roleUserIds = $roleUsers->pluck('userId')->toArray();
                //合并角色用户并且去重
                $userIds = array_merge($roleUserIds, $userIds);
            }
            $userIds = array_unique($userIds);
            $emails = User::whereIn('id', $userIds)->pluck('email')->toArray();
            Email::approvalApproval($emails, $data['title'], $data['owner_name']);
            //Email::approvalApproval($emails, '审批名', 'waiwai');
        }

        return [];
    }

    // 添加参与者
    public function addParticipant($taskId, $data)
    {

        if ($this->isPermissionParticipant($taskId) == false) {
            throw new ClientHttpException("当前用户不具备添加参与者权限", 10000);
        }

        //如果是角色，判断下角色是否为空
        if ($data['type']== 'group'){
            $users =  Rbac::getUsersByRoleIds([$data['key_id']]);
            if($users->count() == 0){
                throw new ClientHttpException("角色内无可用用户，请重新选择", 10001);
            }
            $userIds = $users->pluck('userId')->toArray();
            $count = User::whereIn('id',$userIds)->where(['status'=>User::USER_NORMAL])->count();
            if($count == 0){
                throw new ClientHttpException("角色里的人都已离职，请重新选择", 10002);
            }
        }



        $data = TaskFlow::addParticipant($taskId, $data);
        return $data;
    }

    // 判断是不是有参与者操作权限
    protected function isPermissionParticipant($taskId)
    {
        $task = TaskFlow::getTask($taskId);

        $approval = Approval::where('flow_instance_id', $task['instance_id'])->first();
        if (!$approval) {
            throw new ClientHttpException("实例不存在", 10000);
        }

        $authUserId = auth()->user()->user_id;

        $user = User::find($approval->created_by);


        return $authUserId == $approval->owner_id || $authUserId == $user->user_id;

    }

    // 删除参与者
    public function removeParticipant($id)
    {

        $data = TaskFlow::removeParticipant($id);
        return $data;
    }
}