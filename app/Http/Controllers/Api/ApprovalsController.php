<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Approvals\CreateRequest;
use App\Http\Requests\Approvals\LaunchRequest;
use App\Models\Wiki;
use Dingo\Api\Http\Request;
use App\Services\ApprovalService;

class ApprovalsController extends Controller
{
    /**
     * 审批创建
     *
     * @param CreateRequest $request
     * @param ApprovalService $service
     * @return
     */
    public function create(CreateRequest $request, ApprovalService $service)
    {

        $res = $service->create($request->all(), $request->header('Authorization'));
        return $this->setViewData($res);
    }

    // 审批列表，显示全部
    public function index(Request $request, ApprovalService $service)
    {

        $res = $service->all();
        return $this->setViewData($res);

    }


    /**
     * 审批会签
     *
     * @param Request $request
     * @param String $id 审批ID
     * @param ApprovalService $service
     * @return
     */
    public function signature(Request $request, string $id, ApprovalService $service)
    {
        return $this->setViewData([]);
    }


    // 获取审批详情
    public function show($id, ApprovalService $service)
    {
        $data = $service->show($id);
        return $this->setViewData($data->toArray());
    }

    /**
     * 模板流程
     *
     * @param Request $request
     * @param String $id 审批ID
     * @param ApprovalService $service
     * @return
     */
    public function template(Request $request, string $id, ApprovalService $service)
    {
        return $this->setViewData(['id' => $id]);
    }


    // 审批日志
    public function records(string $id, ApprovalService $service)
    {
        $data = $service->records($id);

        return $this->setViewData($data->toArray());
    }

    /**
     * 任务流模板
     *
     * @param Request $request
     * @param ApprovalService $service
     * @return
     */
    public function taskflowTemplates(Request $request, ApprovalService $service)
    {
        $data = $service->taskflowTemplates();

        return $this->setViewData($data->toArray());
    }

    // 发起审批
    public function launch($approvalId, LaunchRequest $request, ApprovalService $service)
    {


        $data = $request->all();

        $data['user_id'] = $this->user()['user_id'];
        $res = $service->launch($approvalId, $data);
        return $this->setViewData($res->toArray());
    }


    // 接收上传文件
    public function uploadFile($id, Request $request, ApprovalService $service)
    {
        $data = $service->uploadFile($id, $request->file('file'), $request->header('Authorization'));
        return $this->setViewData($data->toArray());
    }

    /*
     * 下载文件
     */
    public function downloadFile($id,$token,Request $request,ApprovalService $service)
    {
        $file = $service->getFile(['id'=>$id])[0];
        Wiki::downloadFile($file->wiki_dl_path,$file->file_name,$token);
    }

    public function addFileInfo(ApprovalService $service)
    {
        $service->addFileDloadPath();
    }


}

