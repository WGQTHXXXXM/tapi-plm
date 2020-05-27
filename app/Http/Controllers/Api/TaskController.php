<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Tasks\AddParticipantRequest;
use App\Http\Requests\Tasks\DecisionRequest;
use App\Services\TaskService;

class TaskController extends Controller
{

    // 返回所有参与者
    public function participants($taskId, TaskService $service)
    {
        return $service->participants($taskId);
    }

    // 添加参与者
    public function addParticipant($taskId,AddParticipantRequest $request, TaskService $service)
    {
        return $service->addParticipant($taskId,$request->all());
    }

    // 删除参与者
    public function removeParticipant($id,TaskService $service)
    {
        return $service->removeParticipant($id);
    }


    // 审批决策
    public function decision($taskId, DecisionRequest $request, TaskService $service)
    {
        $data = $service->decision($taskId, $request->all());
        return $this->setViewData($data);
    }

    public function show($taskId, TaskService $service)
    {
        $data = $service->show($taskId);
        return $this->setViewData($data);
    }

}

