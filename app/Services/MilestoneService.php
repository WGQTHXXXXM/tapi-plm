<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\Milestone;
use App\Models\Role;
use App\Models\RoleUserRef;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MilestoneService
{

    /*
     * 通过id找模型
     */
    public function findModel($id)
    {
        if (($model = Milestone::find($id)) !== null) {
            return $model;
        } else {
            throw new LogicException('没找到该阀点！');
        }
    }


    /**
     * 创建新阀点
     */
    public function create($params)
    {
        DB::transaction(function () use ($params) {
            $lvl = empty($params['pre_lvl'])?0:$params['pre_lvl'];
            $this->updateMilestoneOrder($params['project_id'],$lvl);
            $newMdl = new Milestone();
            try{
                $params['order']=$lvl+1;
                $newMdl->fill($params)->save();
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
            $lvl = empty($params['pre_lvl'])?0:$params['pre_lvl'];
            $this->updateMilestoneOrder($params['project_id'],$lvl);
            $Mdl = $this->findModel($id);
            try{
                $params['order']=$lvl+1;
                $Mdl->fill($params)->save();
            }catch (LogicException $e){
                throw new LogicException('保存出错：'.$e->getMessage());
            }
        });
    }

    /**
     * 更新节点排序
     */
    public function updateMilestoneOrder($projectId,$lvl)
    {
        if(empty($lvl))
            $mdls = Milestone::where(['project_id'=>$projectId])->get();
        else
            $mdls = Milestone::where(['project_id'=>$projectId])->where('order','>',$lvl)->get();
        foreach ($mdls as $milestone){
            $milestone->order = $milestone->order+1;
            $milestone->save();
        }
    }

    public function search($request, $id)
    {
        $perPage = empty($request->get('per_page'))? config('app.default_per_page'):$request->get('per_page');
        $res = Milestone::where(['project_id'=>$id])->orderBy('order')->paginate($perPage);
        return $res;
    }
}