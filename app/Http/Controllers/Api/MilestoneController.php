<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\LogicException;
use App\Models\Milestone;
use App\Services\MilestoneService;
use App\Transformers\DefaultTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MilestoneController extends Controller
{

    public function create(Request $request,MilestoneService $serMil)
    {
        //var_dump(Auth::check());die;
        //检查
        $arrOnly = ['project_id', 'name', 'delivery_at','target','pre_lvl'];
        $arrColumn = [
            'project_id'=> 'required|string|max:64',
            'name'=>'required|string|max:64',
            'delivery_at' =>'required|date_format:Y-m-d H:i:s',
            'target' =>'required|string|max:256',
            'pre_lvl' =>'numeric',
        ];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);

        $serMil->create($params);

        return $this->response->noContent();
    }

    public function update(Request $request,MilestoneService $serMil,$id)
    {
        //检查
        $arrOnly = ['project_id', 'name', 'delivery_at','target','pre_lvl'];
        $arrColumn = [
            'project_id'=> 'required|string|max:64',
            'name'=>'required|string|max:64',
            'delivery_at' =>'required|date_format:Y-m-d H:i:s',
            'target' =>'required|string|max:256',
            'pre_lvl' =>'numeric',
        ];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, []);

        $serMil->update($params,$id);

        return $this->response->noContent();

    }

    /*
     * 查看项目下所有阀点
     */
    public function index(Request $request,MilestoneService $serMil,$id)
    {
        $roles = $serMil->search($request, $id);
        return $this->response->paginator($roles, new DefaultTransformer())->setStatusCode(200);
    }

    /**
     * 阀点启动
     */
    public function startMilestone($id)
    {
        try{
            $mdl = Milestone::find($id);
            if(empty($mdl->start_at)){
                $mdl->start_at = date('Y-m-d H:i:s',time());
                $mdl->save();
            }else{
                return $this->response->array(['msg'=>'阀点已经启动'])->setStatusCode(200);
            }
        }catch (LogicException $e){
            throw new LogicException('启动失败：'.$e->getMessage());
        }
        return $this->response->noContent();
    }

}