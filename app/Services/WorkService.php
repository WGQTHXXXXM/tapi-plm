<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\Work;
use App\Models\WorkFront;
use App\Models\WorkLog;
use Illuminate\Support\Facades\DB;

class WorkService
{

    /*
 * 通过id找模型
 */
    public function findModel($id)
    {
        if (($model = Work::find($id)) !== null) {
            return $model;
        } else {
            throw new LogicException('没找到该任务！');
        }
    }


    /**
     * 创建新阀点
     */
    public function create($params)
    {
        DB::transaction(function () use ($params) {
            try{
                $newWorkMdl = new Work();
                $WorkIds = $params['front_work_id'];
                unset($params['front_work_id']);
                $params['percent_complete']=0;
                $newWorkMdl->fill($params)->save();
                $wid = $newWorkMdl->id;
                if(!empty(trim($WorkIds))){
                    $arrWorkFrontIds = explode(',',$WorkIds);
                    foreach ($arrWorkFrontIds as $frontId){
                        $newWorkFrontMdl = new WorkFront();
                        $newWorkFrontMdl->fill(['work_id'=>$wid,'front_work_id'=>$frontId])->save();
                    }
                }
                $newWorkLogMdl = new WorkLog();
                $newWorkLogMdl->fill(['work_id'=>$wid,'remark'=>$params['remark'],'type'=>'发起'])->save();

            }catch (LogicException $e){
                throw new LogicException('保存出错：'.$e->getMessage());
            }
        });
    }

    /**
     * 更新阀点
     */
    public function update($params,$id)
    {
        DB::transaction(function () use ($params,$id) {
            $workMdl = $this->findModel($id);
            try{
                $WorkIds = $params['front_work_id'];
                unset($params['front_work_id']);
                $workMdl->fill($params)->save();
                WorkFront::where(['work_id'=>$id])->delete();
                if(!empty(trim($WorkIds))){
                    $arrWorkFrontIds = explode(',',$WorkIds);
                    foreach ($arrWorkFrontIds as $frontId){
                        $newWorkFrontMdl = new WorkFront();
                        $newWorkFrontMdl->fill(['work_id'=>$id,'front_work_id'=>$frontId])->save();
                    }
                }
                $newWorkLogMdl = new WorkLog();
                $strType = '更新';
                if($params['percent_complete'] == 100)
                    $strType = '完结';
                $newWorkLogMdl->fill(['work_id'=>$id,'remark'=>$params['remark'],'type'=>$strType])->save();
            }catch (LogicException $e){
                throw new LogicException('保存出错：'.$e->getMessage());
            }
        });
    }

    /**
     * 任务详情
     */
    public function view($id)
    {
        return $this->findModel($id);
    }

    /**
     * 任务列表
     */
    public function search($request, $paginator)
    {

        $projectId = $request->input('project_id');
        $name = $request->input('name');
        $description = $request->input('description');

        $query = Work::query()->with('createdBy:id,name')
            ->when($projectId, function ($query, $projectId) {return $query->where(['project_id'=>$projectId]);})
            ->when($name, function ($query, $name) {return $query->where(['name'=>$name]);})
            ->when($description, function ($query, $description) {return $query->where(['description'=>$description]);});

        $perPage = (isset($paginator['per_page']) && !empty($paginator['per_page'])) ? $paginator['per_page'] : config('app.default_per_page');
        $res = $query->paginate($perPage);
        return $res;
    }

    /**
     * 任务日志列表
     */
    public function searchWorkLog()
    {
        $perPage = config('app.default_per_page');
        $res = WorkLog::query()->paginate($perPage);
        return $res;
    }

    /**
     * 任务反馈
     */
    public function workFeedback($id,$remark)
    {
        try{
            $newWorkLogMdl = new WorkLog();
            $newWorkLogMdl->fill(['work_id'=>$id,'remark'=>$remark,'type'=>'反馈'])->save();
        }catch (LogicException $e){
            throw new LogicException('保存出错：'.$e->getMessage());
        }

    }

}