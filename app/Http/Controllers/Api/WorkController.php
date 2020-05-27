<?php

namespace App\Http\Controllers\Api;

use App\Models\Milestone;
use App\Services\MilestoneService;
use App\Services\WorkService;
use App\Transformers\DefaultTransformer;
use Illuminate\Http\Request;


class WorkController extends Controller
{
    /**
     * 任务创建
     */
    public function create(Request $request,WorkService $serWork)
    {
        //检查
        $arrOnly = ['project_id', 'name', 'level','start_at','end_at','parent_id','assign_type','assign_obj_id',
            'file_upload','milestone_id','delivery_name','front_work_id','remark'];
        $arrColumn = [
            'project_id'    => 'required|string|max:64',
            'name'          => 'required|string|max:64',
            'level'         => 'required|string|max:32',
            'start_at'      => 'required|date_format:Y-m-d H:i:s',
            'end_at'        => 'required|date_format:Y-m-d H:i:s',
            'parent_id'     => 'nullable|string|max:64',
            'assign_type'   => 'required|string|max:64',
            'assign_obj_id' => 'required|string|max:64',
            'file_upload'   => 'required|numeric',
            'milestone_id'  => 'string|max:64',
            'delivery_name' => 'required|string|max:64',
            'front_work_id' => 'nullable|string',
            'remark' => 'required|string|max:256',
        ];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);

        $serWork->create($params);

        return $this->response->noContent();
    }

    /**
     * 任务更新
     */
    public function update(Request $request,WorkService $serWork,$id)
    {
        //检查
        $arrOnly = ['project_id', 'name', 'level','start_at','end_at','parent_id','assign_type','assign_obj_id',
            'file_upload','milestone_id','delivery_name','front_work_id','percent_complete','remark'];
        $arrColumn = [
            'project_id'    => 'required|string|max:64',
            'name'          => 'required|string|max:64',
            'level'         => 'required|string|max:32',
            'start_at'      => 'required|date_format:Y-m-d H:i:s',
            'end_at'        => 'required|date_format:Y-m-d H:i:s',
            'parent_id'     => 'nullable|string|max:64',
            'assign_type'   => 'required|string|max:64',
            'assign_obj_id' => 'required|string|max:64',
            'file_upload'   => 'required|numeric',
            'milestone_id'  => 'string|max:64',
            'delivery_name' => 'required|string|max:64',
            'front_work_id' => 'nullable|string',
            'percent_complete' => 'nullable|numeric',
            'remark' => 'required|string|max:256',
        ];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);

        $serWork->update($params,$id);

        return $this->response->noContent();
    }

    /**
     * 任务处理详情
     */
    public function view(WorkService $serWork,$id)
    {
        $res = $serWork->view($id);
        return $res;
    }

    /**
     * 我的任务列表
     */
    public function index(Request $request,WorkService $serWork)
    {
        $paginator = app(CommonService::class)->doPaginator($request);
        $roles = $serWork->search($request, $paginator);
        return $this->response->paginator($roles, new DefaultTransformer())->setStatusCode(200);
    }


    /*
     * 任务反馈
     */
    public function workFeedback(Request $request,$id,WorkService $serWork)
    {
        $arrOnly = ['remark'];
        $arrColumn = [
            'remark' => 'required|string|max:256',
        ];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);
        $serWork->workFeedback($id,$params['remark']);

        return $this->response->noContent();
    }

    /**
     * 我的任务列表
     */
    public function indexWorkLog(WorkService $serWork)
    {
        $res = $serWork->searchWorkLog();
        return $this->response->paginator($res, new DefaultTransformer())->setStatusCode(200);
    }



}