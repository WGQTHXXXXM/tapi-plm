<?php

namespace App\Services;

use App\Exceptions\ClientHttpException;
use App\Exceptions\LogicException;
use App\Exceptions\ServerHttpException;
use App\Models\Approval;
use App\Models\ApprovalFile;
use App\Models\AuthUser;
use App\Models\Email;
use App\Models\Rbac;
use App\Models\TaskFlow;
use App\Models\User;
use App\Models\UserCenter;
use App\Models\Wiki;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    // 文件上传
    public function uploadFile($id, $file, $token)
    {
        //上传的文件处理
        if (empty($file))
            throw new ClientHttpException("没有上传文件", 2001);
        $oriFileNmae = $file->getClientOriginalName();
        $oriFileInfo = pathinfo($oriFileNmae);
        $strTime = date('Y-m-d H:i:s', time());
        $fileName = $oriFileInfo['filename'] . '_' . $strTime . '.' . $oriFileInfo['extension'];
        $filePath = $file->path();
        rename($filePath, '/tmp/' . $fileName);
        $mdlApproval = Approval::find($id);
        if (empty($mdlApproval->wiki_page_id))
            throw new ClientHttpException("该审批没有wiki_page_id", 2000);
        $pageId = $mdlApproval->wiki_page_id;//上传页
        //文件上传到wiki
        $data = app(WikiService::class)->uploadFile($pageId, $fileName, $token);
        $parseUrl = parse_url(config('wiki.url'));
        $port = empty($parseUrl['port']) ? '' : ':' . $parseUrl['port'];
        $wikiUrl = $parseUrl['scheme'] . '://' . $parseUrl['host'] . $port .
            '/pages/worddav/preview.action?fileName=' . $fileName . '&pageId=' . $pageId;
        $d = [
            'approval_id' => $id,
            'file_id' => $data->id,
            'file_name' => $fileName,
            'wiki_url' => $wikiUrl,
            'wiki_dl_path' => $data->_links->download,
        ];
        //保存文件信息到数据库
        $approvalFile = $this->saveApprovalFile($d, '/tmp/' . $fileName);

        return $approvalFile;
    }

    public function saveApprovalFile($params, $filePath)
    {
        $mdl = new ApprovalFile();
        try {
            $mdl->fill($params)->save();
            unlink($filePath);
        } catch (LogicException $e) {

            throw new LogicException('保存文件表出错：' . $e->getMessage());
        }
        return $mdl;
    }

    // 发起审批
    public function launch($approvalId, $params)
    {

        $approvalModel = Approval::find($approvalId);
        if (!$approvalModel) {
            throw new ClientHttpException("审批信息不存在，无法启动", 2000);
        }

        $userIds = $this->getParticipantUserIds($approvalModel->flow_instance_id);
        if (count($userIds) === 0) {
            throw new ClientHttpException("请先添加参与者，再启动审批", 1000);
        }


        $data = [
            'user_id' => $params['user_id'],
            'content' => $params['content'],
            'select_key' => $params['select_key']
        ];
        $ins = TaskFlow::launchInstance($approvalModel->flow_instance_id, $data);

        $approvalModel->fill($params);
        $approvalModel->save();

        //发邮件
        $follow = User::where('id', $approvalModel->sqer_id)->orWhere('id', $approvalModel->purchaser_id)->pluck('email')->toArray();
        $follow = array_unique($follow);

        $tasks= TaskFlow::getCurtask($ins['id']);
        $particpants = [];
        foreach ($tasks as $task){
            if($task['status'] == Approval::TASK_START)
                $particpants[]=TaskFlow::participantsByTaskId($task['id']);
        }
        $userIds = [];
        $roleUsers = [];
        foreach ($particpants as $items){
            foreach ($items as $p){
                if($p['type'] == Approval::PARTICPENT_INDIVIDUAL)
                    $userIds[]= $p['key_id'];
                else if($p['type'] == Approval::PARTICPENT_GROUP)
                    $roleUsers[] = $p['key_id'];
            }
        }
        if(!empty($roleUsers)){
            $roleUsers = Rbac::getUsersByRoleIds($roleUsers);
            $roleUserIds = $roleUsers->pluck('userId')->toArray();
            $userIds = array_merge($roleUserIds, $userIds);
        }

        $userIds = array_unique($userIds);
        $emails = User::whereIn('id', $userIds)->pluck('email')->toArray();

        Email::approvalFollow($follow, $ins['name'], $approvalModel->owner_name);
        Email::approvalApproval($emails, $ins['name'], $approvalModel->owner_name);


        return $approvalModel;

    }

    // 获取所有参与者
    public function getParticipantUserIds($insId)
    {
        $data = TaskFlow::getInstance($insId);

        // 获取所有参与者
        $participants = [];
        foreach ($data['tasks'] as $task) {
            $participants = array_merge($participants, $task['participants']);
        }


        //去重，过滤
        $participants = collect($participants)->unique('key_id');

        $userIds = $participants->map(function ($item, $key) {
            if ($item['type'] === 'individual') {
                return $item['key_id'];
            }
        })->filter()->values()->toArray();


        $roleIds = $participants->map(function ($item, $key) {
            if ($item['type'] === 'group') {
                return $item['key_id'];
            }
        })->filter()->values()->toArray();


        // 如果角色id为空，则结束
        if (count($roleIds) === 0) {
            return $userIds;
        }

        $roleUsers = Rbac::getUsersByRoleIds($roleIds);
        $roleUserIds = $roleUsers->pluck('userId')->toArray();

        //合并角色用户并且去重
        $userIds = array_merge($roleUserIds, $userIds);
        $userIds = array_unique($userIds);


        return $userIds;

    }

    // 获取所有审批列表
    public function all()
    {

        $user = Auth::user();

        // 获取所有数据进行过滤
        $approvals = Approval::with('files')->get();

        if ($approvals->count() == 0) {
            return [];
        }

        // 获取所有的实例id
        $insIds = $approvals->map(function ($approval, $key) {
            return $approval->flow_instance_id;
        });

        if (count($insIds) === 0) {
            return [];
        }

        $instances = TaskFlow::getInstanceInIds($insIds->toArray());

        $instances = $instances->keyBy('id');

        //
        //
        //
        //   TODO 数据不多，前端过滤
        //   $approvals = $approvals->filter(function ($value, $key) {
        //       return $value->age < 35;
        //   });


        // 设置状态等关键值
        $approvals->transform(function ($approval, $key) use ($instances, $user) {
            //追加内容到服务中
            $approval->task_flow = $instances[$approval->flow_instance_id];

            //如果为空，表示还没启动
            if (!$approval->task_flow['start_time']) {
                $approval->status = Approval::STATUS_CREATEING;

                return $approval;
            }

            //设置status
            $data = $this->getApprovalStatus($user->user_id, $approval->task_flow['curtasks']);
            $approval->status = $data['status'];
            $approval->now_task = $data['now_task'];


            return $approval;
        });


        // TODO 性能极差，
        // 设置发起人角色
        $approvals->transform(function ($approval, $key) use ($instances) {

            $approval->created_group = $this->getCreatedGroup($approval);

            return $approval;

        });


        return $approvals->toArray();
    }

    // 获取发起者的角色
    protected function getCreatedGroup($approval)
    {
        $user = User::find($approval->created_by);
        if (!$user) {
            return [];
        }


        // 如果存在空就跳过
        if (!$user->user_id || !$approval->project_id) {
            return [];
        }

        try {
            $createdGroup = Rbac::getRolesByUserId($user->user_id, $approval->project_id)->toArray();
        } catch (\Exception $e) {
            $createdGroup = [];
        }

        return $createdGroup;
    }

    // 根据活跃的任务检测状态
    protected function getApprovalStatus($userId, $curTasks)
    {
        $returnData = [
            'status' => Approval::STATUS_FINISH,
            'now_task' => null
        ];

        // 如果活跃任务为0 表示已完结
        if (count($curTasks) === 0) {
            return $returnData;
        }


        foreach ($curTasks as $curtask) {


            $groupIds = [];
            //循环参与者
            foreach ($curtask['participants'] as $participant) {
                if ($participant['type'] === 'individual') {
                    if ($userId === $participant['key_id'] && !$participant['approve']) {
                        $returnData = [
                            'status' => Approval::STATUS_NEED,
                            'now_task' => $curtask
                        ];
                        return $returnData;
                    }
                }

                //遍历角色
                if ($participant['type'] === 'group') {
                    $groupIds[] = $participant['key_id'];
                }
            }
            
            
            if (count($groupIds) > 0) {
                //TODO 因为是每个审计服务都循环，所以很消耗性能

                $roleUsers = Rbac::getUsersByRoleIds($groupIds);

                foreach ($roleUsers as $u) {
                    // 获取参与者
                    $p = $this->getParticipantByKeyId($curtask['participants'],$u->roleId);
                    // 如果存在并且参与者没有决策，返回待处理，
                    if ($u->userId == $userId &&  !$p['approve']) {
                        $returnData = [
                            'status' => Approval::STATUS_NEED,
                            'now_task' => $curtask
                        ];
                        return $returnData;
                    }
                }
            }
        }
        $returnData = [
            'status' => Approval::STATUS_PROCESSING,
            'now_task' => $curTasks[0]
        ];
        return $returnData;
    }

    // 获取参与者
    protected function getParticipantByKeyId($participants, $keyId)
    {
        foreach ($participants as $participant) {
            if($participant['key_id'] == $keyId){
                return $participant;
            }
        }

        return null;
    }

    // 获取审批详情
    public function show($id)
    {
        $approval = Approval::with('files')->find($id);
        if (!$approval) {
            throw new ClientHttpException("审批不存在");
        }

        return $approval;

    }


    //查询操作日志
    public function records(string $id)
    {
        $approval = Approval::find($id);
        if (!$approval) {
            throw new ClientHttpException('审批不存在', 1000);
        }
        return TaskFlow::getInstanceRecords($approval->flow_instance_id);
    }

    public function taskflowTemplates()
    {
        return TaskFlow::templates();
    }


    // 创建审批
    public function create($data, $token)
    {

        $ins = TaskFlow::createInstance($data);


        $count = Approval::where('flow_instance_id', $ins['id'])->count();
        if ($count) {
            throw new ClientHttpException('审批已经存在，不要重复创建', 1001);
        }

        try {
            DB::beginTransaction();

            //新建wiki页面
            $serWiki = app(WikiService::class);
            $wikiPageId = $serWiki->createPage($data['name'], config('wiki.page_approval'), $token);


            $approvalModel = new Approval($data);
            $approvalModel->flow_instance_id = $ins['id'];
            $approvalModel->wiki_page_id = $wikiPageId;
            $approvalModel->save();

            DB::commit();


            $approvalModel->task_flow = $ins;
            $approvalModel->created_group = $this->getCreatedGroup($approvalModel);

            return $approvalModel->toArray();

        } catch (\Exception $exception) {
            DB::rollBack();

            TaskFlow::removeInstance($ins['id']);

            throw new ServerHttpException("创建实例失败:" . $exception->getMessage());
        }
    }

    /**
     * 查找审批文件
     */
    public function getFile($query)
    {
        $files = ApprovalFile::where($query)->get();
        if(empty($files)){
            throw new LogicException('没找到文件');
        }
        return $files;
    }

    /**
     * 添加上审批文件的下载地址
     */
    public function addFileDloadPath()
    {
        DB::transaction(function () {
            $approves = Approval::query()->get();
            foreach ($approves as $key=>$item){
                $response = Wiki::getContentAttachment($item->wiki_page_id);
                if(!empty($response['results'])){
                    $result = $response['results'];
                    foreach ($result as $file){
                        ApprovalFile::where(['file_id'=>$file['id']])->update(['wiki_dl_path'=>$file['_links']['download']]);
                    }
                }else{
                    var_dump($key.'----'.$item->id);
                }
            }
        });

    }
}