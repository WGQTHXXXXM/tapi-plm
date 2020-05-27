<?php

namespace App\Http\Controllers\Api;

use App\Models\FunctionObj;
use App\Services\CommonService;
use App\Services\FuncDocRefService;
use App\Services\FunctionObjService;
use App\Services\WikiService;
use App\Transformers\DefaultTransformer;
use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;


class FunctionObjController extends Controller
{
	/**
	 * 获取功能列表
	 * @param Request $request
	 * @return $this
	 */
	public function index(Request $request, FunctionObjService $functionObjService)
	{
		$paginator = app(CommonService::class)->doPaginator($request);
        $param = $request->all();
		$functions = $functionObjService->getFunction($param, $paginator);
		return $this->response->paginator($functions, new DefaultTransformer())->setStatusCode(200);
	}

	/**
	 * 获取功能树
	 * @param Request $request
	 * @return $this
	 */
	public function tree($project_id,FunctionObjService $functionObjService)
	{
		$functions = $functionObjService->getFunctionTree($project_id);
		return $this->response->array($functions, new DefaultTransformer())->setStatusCode(200);
	}

	/**
	 * 获取当前功能的上级功能信息
	 * @param Request $request
	 * @param $id
	 * @param FunctionObjService $functionObjService
	 * @return mixed
	 */
	public function parent(Request $request, $id, FunctionObjService $functionObjService)
	{
		$functions = $functionObjService->getParentFunctions($id);
		return $this->response->array($functions, new DefaultTransformer());
	}

	/**
	 * 获取当前功能的子级功能信息
	 * @param Request $request
	 * @param $id
	 * @param FunctionObjService $functionObjService
	 * @return mixed
	 */
	public function child(Request $request, $id, FunctionObjService $functionObjService)
	{
		$functions = $functionObjService->getChildFunctions($id);
		return $this->response->array($functions, new DefaultTransformer());
	}

	/**
	 * 获取某个功能信息
	 * @param $id
	 * @return  $user
	 */
	public function show($id, FunctionObjService $functionObjService)
	{
        $function = $functionObjService->getFunctionById($id);
		return $this->response->item($function, new DefaultTransformer())->setStatusCode(200);
	}

	/**
	 * 新建功能
	 * @param Request $request
	 * @return $this|void
	 */
	public function store(Request $request,WikiService $serWiki)
	{
        $arrOnly = [
			'name',
			'func_level',
			'parent_func_id',
			'project_id',
			'owner_id',
			'key_func_desc',
			'lead_ecu',
			'belong_to_system',
			'power_ecu',
			'domain_ecu',
			'chassis_ecu',
			'adas_ecu',
			'instrumentpanel_ecu',
			'decoration_ecu',
			'signal_matrix_version_no',

            'hardware_version_no',
            'software_version_no',
            'calibration_version_no',
            'configuration_version_no',
            'project_valve_point',
            'func_status',
            'calibration_status',
            'completion_time',
            'milestone_time',
        ];
		$arrColumn = [
			'name'                     => 'required|string|max:128',
			'func_level'               => 'required|numeric',
			'parent_func_id'           => 'nullable|string|max:64',
			'project_id'               => 'required|string|max:64',
			'owner_id'                 => 'nullable|string|max:64',
			'key_func_desc'            => 'nullable|string|max:1024',
			'lead_ecu'                 => 'nullable|string|max:64',
			'belong_to_system'         => 'nullable|string|max:64',
			'power_ecu'                => 'nullable|string|max:64',
			'domain_ecu'               => 'nullable|string|max:64',
			'chassis_ecu'              => 'nullable|string|max:64',
			'adas_ecu'                 => 'nullable|string|max:64',
			'instrumentpanel_ecu'      => 'nullable|string|max:64',
			'decoration_ecu'           => 'nullable|string|max:64',
			'signal_matrix_version_no' => 'nullable|string|max:64',

            'hardware_version_no'      => 'nullable|string|max:64',
            'software_version_no'      => 'nullable|string|max:64',
            'calibration_version_no'   => 'nullable|string|max:64',
            'configuration_version_no' => 'nullable|string|max:64',
            'project_valve_point'      => 'nullable|string|max:64',
            'func_status'              => 'nullable|string|max:64',
            'calibration_status'       => 'nullable|string|max:64',
            'completion_time'          => 'nullable|date_format:Y-m-d H:i:s',
            'milestone_time'           => 'nullable|date_format:Y-m-d H:i:s',

        ];
		$arrMessage = [
			'name.required'       => '功能名称不能为空',
			'func_level.required' => '功能级别不能为空',
			'project_id.required' => '项目名不能为空',
			'func_level.numeric'  => '功能级别必须为数字',
		];
		$params = $this->doValidate($request->all(), $arrOnly, $arrColumn, $arrMessage);
		$functionService = new FunctionObjService();

        $name = $params['name'];
		$func_level = $params['func_level'];
		//判断是否已有该功能，有则不再创建
        $mdlProject = $serWiki->getProjectById($params['project_id'],$request->header('authorization'));
        if ($func_level == FunctionObj::LEVEL_ONE) {//创建一级功能
            $functionCreate = FunctionObj::where(['func_level'=>FunctionObj::LEVEL_ONE,'name'=>$name,
                'project_id'=>$params['project_id']])->first();

            if (!empty($functionCreate)) {
				throw new \Exception('已有该'.$func_level.'级功能了，请不要重复创建');
			}
			unset($params['parent_func_id']);//一级功能上级功能不能有
            $ancestor = $mdlProject->businessObj->wikiId;
            $result = $functionService->addFunction($params,$request->header('authorization'),$ancestor,$mdlProject->businessObj->name);
			$id = $result->id;
		}else{//创建子级功能
            if (!isset($params['parent_func_id']) || empty($params['parent_func_id'])) {
                throw new \Exception('请确定上级功能！');
            }
            $functionCreate = FunctionObj::where(['func_level'=>$func_level,'name'=>$name,'parent_func_id'=>$params['parent_func_id'],
                'project_id'=>$params['project_id']])->first();

            if (!empty($functionCreate)) {
				throw new \Exception('已有该'.$func_level.'级功能了，请不要重复创建');
			}
			$functionParent = $functionService->getFunctionById($params['parent_func_id']);
			if ($functionParent['func_level'] != $func_level - 1) {
				throw new \Exception('请不要跨级建立功能！');
			}
            $ancestor = $functionParent->wiki_page_id;
            $result = $functionService->addFunction($params,$request->header('authorization'),$ancestor,$mdlProject->businessObj->name);
            $id = $result->id;
		}
        return $this->response->array(['id' => $id]);
	}

	/**
	 * 修改功能信息
	 *
	 * @param Request $request
	 * @param $id
	 * @return \Dingo\Api\Http\Response
	 */
	public function update(Request $request, $id)
	{
        $arrOnly = [
			'name',
            'func_level',
			'parent_func_id',
			'owner_id',
			'key_func_desc',
			'lead_ecu',
			'belong_to_system',
			'power_ecu',
			'domain_ecu',
			'chassis_ecu',
			'adas_ecu',
			'instrumentpanel_ecu',
			'decoration_ecu',
			'signal_matrix_version_no',

            'hardware_version_no',
            'software_version_no',
            'calibration_version_no',
            'configuration_version_no',
            'project_valve_point',
            'func_status',
            'calibration_status',
            'completion_time',
            'milestone_time',
        ];
		$arrColumn = [
			'name'                     => 'required|string|max:128',
            'func_level'               => 'required|numeric',
			'parent_func_id'           => 'nullable|string|max:64',
			'owner_id'                 => 'nullable|string|max:64',
			'key_func_desc'            => 'nullable|string|max:1024',
			'lead_ecu'                 => 'nullable|string|max:64',
			'belong_to_system'         => 'nullable|string|max:64',
			'power_ecu'                => 'nullable|string|max:64',
			'domain_ecu'               => 'nullable|string|max:64',
			'chassis_ecu'              => 'nullable|string|max:64',
			'adas_ecu'                 => 'nullable|string|max:64',
			'instrumentpanel_ecu'      => 'nullable|string|max:64',
			'decoration_ecu'           => 'nullable|string|max:64',
			'signal_matrix_version_no' => 'nullable|string|max:64',

            'hardware_version_no'      => 'nullable|string|max:64',
            'software_version_no'      => 'nullable|string|max:64',
            'calibration_version_no'   => 'nullable|string|max:64',
            'configuration_version_no' => 'nullable|string|max:64',
            'project_valve_point'      => 'nullable|string|max:64',
            'func_status'              => 'nullable|string|max:64',
            'calibration_status'       => 'nullable|string|max:64',
            'completion_time'          => 'nullable|date_format:Y-m-d H:i:s',
            'milestone_time'           => 'nullable|date_format:Y-m-d H:i:s',
		];
		$arrMessage = [
			'name.required'       => '功能名称不能为空',
			'func_level.required' => '功能级别不能为空',
			'func_level.numeric'  => '功能级别必须为数字',
		];
		$params = $this->doValidate($request->all(), $arrOnly, $arrColumn, $arrMessage);
		$functionService = new FunctionObjService();
		//判断是否已有该功能
		$name = $params['name'];
		$func_level = $params['func_level'];
		$functionInfo = $functionService->getFunctionById($id);//要修改的功能
		if (!isset($functionInfo['name']) || empty($functionInfo['name']) || is_null($functionInfo['name'])) {
			throw new \Exception('没有该功能！');
		}
		if ($func_level == FunctionObj::LEVEL_ONE) {//编辑一级功能
            //根据要修改成的名称查询是否有该名称的功能
			$functionEdit = FunctionObj::where(['func_level'=>FunctionObj::LEVEL_ONE,
                'name'=>$name,'project_id'=>$functionInfo->project_id])->first();

			if (!empty($functionEdit) && $functionEdit['id'] != $id) {
				throw new \Exception('已有该一级功能了，请不要更新此名');
			}
			unset($params['parent_func_id']);
			$functionService->editFunction($id, $params,$request->header('authorization'));
		} else {//编辑子级功能
			if (!isset($params['parent_func_id']) || empty($params['parent_func_id'])) {
				throw new \Exception('请确定上级功能！');
			}
			if (isset($params['parent_func_id']) && !empty($params['parent_func_id'])) {
				$functionParent = $functionService->getFunctionById($params['parent_func_id']);//上级功能
				if ($functionParent['func_level'] != $func_level - 1) {
					throw new \Exception('请不要跨级建立功能！');
				}
			}
            $functionEdit = FunctionObj::where(['func_level'=>$func_level,
                'name'=>$name,'project_id'=>$functionInfo->project_id,'parent_func_id'=>$params['parent_func_id']])->first();
			if (!empty($functionEdit) && $functionEdit['id'] != $id) {
				throw new \Exception('已有该'.$func_level.'级功能了，请不要重复创建');
			}

			$functionService->editFunction($id, $params,$request->header('authorization'));
		}

		return $this->response->noContent();
	}

	/**
	 * 批量删除功能
	 * @param $ids
	 * @return \Dingo\Api\Http\Response
	 * @throws \Exception
	 */
	public function destroy(Request $request,$ids)
	{
		$functionService = new FunctionObjService();
		//判断是否有子级，有则不能删除
		$arrids = explode(",", $ids);
		foreach ($arrids as $key => $id) {
			$funcInfo = $functionService->getFunctionById($id);
			$funcChild = $functionService->getChildFunctions($id);
			if (count($funcChild) > 0 && !empty($funcChild[0]['id'])) {
				throw new \Exception('功能'.$funcInfo['name'].'有子级功能，请先删除子级功能！');
			}
		}
		if (!empty($arrids)) {
			$result = $functionService->delFunction($arrids,$this->user()['id'],$request->header('authorization'));
		}

		return $this->response->noContent();
	}

	/**
	 * 删除功能
	 * @param $id
	 * @return \Dingo\Api\Http\Response
	 * @throws \Exception
	 */
	public function del(Request $request,$id)
	{
        $functionService = new FunctionObjService();
		//判断是否有子级，有则不能删除
		$funcInfo = $functionService->getFunctionById($id);
		$funcChild = $functionService->getChildFunctions($id);
		if (count($funcChild) > 0 && !empty($funcChild[0]['id'])) {
			throw new \Exception('功能'.$funcInfo['name'].'有子级功能，请先删除子级功能！');
		}
		$result = $functionService->delFunction([$id],$this->user()['id'],$request->header('authorization'));

		return $this->response->noContent();
	}

	/**
	 * 取消关联零部件
	 * @param Request $request
	 * @return \Dingo\Api\Http\Response
	 */
	public function delrefparts(Request $request)
	{
		$arrOnly = [
			'function_id',
			'part_ids',
		];
		$arrColumn = [
			'function_id' => 'required|string',
			'part_ids'     => 'required|string',
		];
		$arrMessage = [
			'function_id.required' => '功能ID不能为空',
			'part_ids.required'     => '零部件ID不能为空',
		];
		$params = $this->doValidate($request->all(), $arrOnly, $arrColumn, $arrMessage);
		$functionService = new FunctionObjService();
		$arrPartId = explode(',',$params['part_ids']);
		$functionService->delFunctionPartRef($params['function_id'],$arrPartId);

		return $this->response->noContent();
	}

	/**
	 * 关联零部件
	 *
	 * @param Request $request
	 * @return \Dingo\Api\Http\Response
	 */
	public function refparts(Request $request)
	{
		$arrOnly = [
			'function_ids',
			'part_ids',
		];
		$arrColumn = [
			'function_ids' => 'required|string',
			'part_ids'     => 'required|string',
		];
		$arrMessage = [
			'function_ids.required' => '功能ID不能为空',
			'part_ids.required'     => '零部件ID不能为空',
		];
		$params = $this->doValidate($request->all(), $arrOnly, $arrColumn, $arrMessage);
		$functionService = new FunctionObjService();

		//判断是否已有该关联，有则不再关联
		$data = [];
		$functionIds = explode(",", $params['function_ids']);
		$partIds     = explode(",", $params['part_ids']);
		foreach ($functionIds as $func_id) {
			//只有三级功能才可以关联
			$function = app(FunctionObjService::class)->getFunctionById($func_id);
			if ($function['func_level'] == FunctionObj::LEVEL_THREE) {
				foreach ($partIds as $part_id) {
					$data[] = ['function_id' => $func_id, 'part_id' => $part_id];
				}
			}
		}
		$functionInfo = $functionService->getRefByFunctionId($functionIds);
		if (count($functionInfo) == 0) {//没有相关关系，全部新创建关联
			$functionService->addFunctionPartRef($data);
		} else {//部分创建
			foreach ($functionInfo as $info) {
				foreach ($data as $key => $value) {
					if ($info['function_id'] == $value['function_id'] && $info['part_id'] == $value['part_id']) {
						unset($data[$key]);
					}
				}
			}
			$functionService->addFunctionPartRef($data);
		}

		return $this->response->noContent();
	}

	/**
	 * @param $function_id
	 * @param FunctionObjService $functionObjService
	 * @return $this
	 */
	public function showref($function_ids, FunctionObjService $functionObjService)
	{
		$function_ids = explode(",", $function_ids);
		$function = $functionObjService->getRefByFunctionId($function_ids, 'part_id');
		return $this->response->array($function, new DefaultTransformer())->setStatusCode(200);
	}

	/**
	 * 根据功能ID查询其关联的零部件，分页返回
	 * @param Request $request
	 * @param $function_id
	 * @param FunctionObjService $functionObjService
	 * @return $this
	 */
	public function showrefpage(Request $request, $function_id, FunctionObjService $functionObjService)
	{
		$paginator = app(CommonService::class)->doPaginator($request);
		$function = $functionObjService->getRefPageByFunctionId($function_id, $selectRaw = 'part_id', $paginator);

		return $this->response->paginator($function, new DefaultTransformer())->setStatusCode(200);
	}

    /**excel功能文件导入数据库
     * @param Request $request
     * @return mixed返回导入数量
     * @throws \Exception
     */
    public function batch(Request $request,WikiService $serWiki)
    {
        $projectId = $request->project_id;
        if(empty($projectId)){
            throw new \Exception('项目名不可以为空！');
        }
        $filePath = $request->func_file;
        if(empty($filePath)) {
            throw new \Exception('没有文件上传！');
        }
        $filePath = $filePath->storeAs('/public', 'func.xls', 'local');

        $filePath = '../storage/app/'.$filePath;

        $reader = Excel::selectSheetsByIndex(0)->load($filePath);
        $data = $reader->toArray();

        $this->verifyExcel($data[0]);//验证excel模板对不对。
        unset($data[0]);//第一行，字段不要
        if(empty($data[1]['lvl1'])){//开始不能没有一线
            throw new \Exception('一级功能里不能为空');
        }
        if(empty($data[2]['lvl2'])){//开始不能没有二线
            throw new \Exception('二级功能里不能为空');
        }
        //存的父id   一级父id     二级父id      功能级
        $name = $Pid = $tmpParent1Id = $tmpParent2Id = $lvl= null;
        //遍历excel数据
        $token = $request->header('authorization');
        $mdlProject = $serWiki->getProjectById($projectId,$token);
        $proName = $mdlProject->businessObj->name;
        $ancestors = $proWikiId1 = $proWikiId2 = $proWikiId = $mdlProject->businessObj->wikiId;
        $delWikiPageId = [];
        DB::beginTransaction();
        foreach ($data as $rowid=>$row) {
            //功能级和父ID的生成
            $this->generatePrtAndLvl($lvl,$Pid,$name,$tmpParent1Id,$tmpParent2Id,$row,
                $proWikiId1,$proWikiId2,$ancestors,$proWikiId);
            //负责人
            $owner_id = User::where(['name'=>$row['owner_id'],'status'=>'normal'])->first();
            if(empty($owner_id)){
                DB::rollBack();
                throw new \Exception('负责人“'.$row['owner_id'].'”不能为空或禁用了');
            }
            $owner_id = $owner_id->id;
            //新增一条
            $mdlFunction = new FunctionObj();
            $mdlFunction->setCreatedAt(time());
            $mdlFunction->setUpdatedAt(time());
            $mdlFunction->func_level = $lvl;
            $mdlFunction->parent_func_id = $Pid;
            $mdlFunction->owner_id = $owner_id;
            $mdlFunction->key_func_desc = $row['key_func_desc'];
            $mdlFunction->lead_ecu = $row['lead_ecu'];
            $mdlFunction->belong_to_system = $row['belong_to_system'];
            $mdlFunction->power_ecu = $row['power_ecu'];
            $mdlFunction->domain_ecu = $row['domain_ecu'];
            $mdlFunction->chassis_ecu = $row['chassis_ecu'];
            $mdlFunction->adas_ecu = $row['adas_ecu'];
            $mdlFunction->instrumentpanel_ecu = $row['instrumentpanel_ecu'];
            $mdlFunction->decoration_ecu = $row['decoration_ecu'];
            $mdlFunction->name = $name;
            $mdlFunction->project_id = $projectId;

            //检查数据库是否有一级重名
            if($lvl == 1){
                $mdlCheck = FunctionObj::where(['name'=>$name,'func_level'=>1,'project_id'=>$projectId])->first();
                if(!empty($mdlCheck)){
                    DB::rollBack();
                    throw new \Exception('一级功能重复“'.$name.'”');
                }
            }

            try{//同一子集里功能名重复
                $mdlFunction->save();
            } catch (\Exception $e) {
                $this->delBatch($delWikiPageId,$token,$serWiki);
                throw new \Exception('导入错误：'.$e->getMessage());
            }
            //wiki页面创建///
//            try{
//                $pageName = app(FuncDocRefService::class)->generateFileName($mdlFunction->id,$proName);
//                $wikiPageId = $serWiki->createPage($pageName,$ancestors,$token);
//            } catch (\GuzzleHttp\Exception\RequestException $e){
//                $this->delBatch($delWikiPageId,$token,$serWiki);
//                throw new \Exception('wiki新建页面失败：'.$e->getResponse()->getBody()->getContents());
//            }
//            try{
//                $mdlFunction->wiki_page_id = $wikiPageId;
//                $mdlFunction->save();
//            } catch (\Exception $e){
//                $this->delBatch($delWikiPageId,$token,$serWiki);
//                throw new \Exception('wiki新建页面失败：'.$e->getMessage());
//            }
            /////////////
            if($lvl==1){
                $tmpParent1Id = $mdlFunction->id;
                //$proWikiId1 = $wikiPageId;
                //$delWikiPageId[] = $wikiPageId;
            }
            if($lvl==2){
                $tmpParent2Id = $mdlFunction->id;
                //$proWikiId2 = $wikiPageId;
            }

        }
        DB::commit();
        return $this->response->array(['num' => $rowid,'status_code'=>200,'message'=>'成功']);
    }

    /**
     * 批量删除页面
     */
    public function delBatch($delWikiPageId,$token,$serWiki)
    {
        foreach ($delWikiPageId as $wikiPageId)
        {
            $serWiki->deletePage($wikiPageId,$token);
        }
    }

    /**
     * 验证模板
     */
    private function verifyExcel($data)
    {
        $mdl = new FunctionObj();
        $arrTemp = $mdl->getFillable();
        unset($arrTemp[array_search('func_level',$arrTemp)]);
        unset($arrTemp[array_search('parent_func_id',$arrTemp)]);
        unset($arrTemp[array_search('signal_matrix_version_no',$arrTemp)]);
        unset($arrTemp[array_search('name',$arrTemp)]);
        unset($arrTemp[array_search('project_id',$arrTemp)]);
        unset($arrTemp[array_search('hardware_version_no',$arrTemp)]);
        unset($arrTemp[array_search('software_version_no',$arrTemp)]);
        unset($arrTemp[array_search('calibration_version_no',$arrTemp)]);
        unset($arrTemp[array_search('configuration_version_no',$arrTemp)]);
        unset($arrTemp[array_search('completion_time',$arrTemp)]);
        unset($arrTemp[array_search('project_valve_point',$arrTemp)]);
        unset($arrTemp[array_search('milestone_time',$arrTemp)]);
        unset($arrTemp[array_search('func_status',$arrTemp)]);
        unset($arrTemp[array_search('calibration_status',$arrTemp)]);
        foreach ($arrTemp as $field){
            if(!isset($data[$field])){
                DB::rollBack();
                throw new \Exception('模板不对，没有'.$field.'字段');
            }
        }
    }

    /**
     * 根据EXCEL表的各级的位置判断，级数，和分配父id。并得到名字
     * @param $func_level
     * @param $parent_id
     * @param $name
     * @param $parent1_id
     * @param $parent2_id
     * @param $row
     * @throws \Exception
     */
    private function generatePrtAndLvl(&$func_level,&$parent_id,&$name,$parent1_id,$parent2_id,$row,
                                       $proWikiId1,$proWikiId2,&$ancestors,$proWikiId)
    {
        $parent_id = null;
        if(!empty(trim($row['lvl1']))){
            $func_level=1;
            $name = $row['lvl1'];
            $ancestors=$proWikiId;
        }elseif(!empty(trim($row['lvl2']))){
            $func_level=2;
            $parent_id = $parent1_id;
            $name = $row['lvl2'];
            $ancestors=$proWikiId1;
        }elseif(!empty(trim($row['lvl3']))){
            $func_level=3;
            $parent_id = $parent2_id;
            $name = $row['lvl3'];
            $ancestors=$proWikiId2;
        }else{
            DB::rollBack();
            throw new \Exception('三级功能里不能为空');
        }
    }

    /**
     * 功能合并
     */
    public function merge(Request $request,FunctionObjService $funcSer,$masterId,$slaveId)
    {
        $arrOnly = [
            'name',
            'func_level',
            'parent_func_id',
            'owner_id',
            'key_func_desc',
            'lead_ecu',
            'belong_to_system',
            'power_ecu',
            'domain_ecu',
            'chassis_ecu',
            'adas_ecu',
            'instrumentpanel_ecu',
            'decoration_ecu',
            'signal_matrix_version_no',

            'hardware_version_no',
            'software_version_no',
            'calibration_version_no',
            'configuration_version_no',
            'project_valve_point',
            'func_status',
            'calibration_status',
            'completion_time',
            'milestone_time',
        ];
        $arrColumn = [
            'name'                     => 'required|string|max:128',
            'func_level'               => 'required|numeric',
            'parent_func_id'           => 'nullable|string|max:64',
            'owner_id'                 => 'nullable|string|max:64',
            'key_func_desc'            => 'nullable|string|max:1024',
            'lead_ecu'                 => 'nullable|string|max:64',
            'belong_to_system'         => 'nullable|string|max:64',
            'power_ecu'                => 'nullable|string|max:64',
            'domain_ecu'               => 'nullable|string|max:64',
            'chassis_ecu'              => 'nullable|string|max:64',
            'adas_ecu'                 => 'nullable|string|max:64',
            'instrumentpanel_ecu'      => 'nullable|string|max:64',
            'decoration_ecu'           => 'nullable|string|max:64',
            'signal_matrix_version_no' => 'nullable|string|max:64',

            'hardware_version_no'      => 'nullable|string|max:64',
            'software_version_no'      => 'nullable|string|max:64',
            'calibration_version_no'   => 'nullable|string|max:64',
            'configuration_version_no' => 'nullable|string|max:64',
            'project_valve_point'      => 'nullable|string|max:64',
            'func_status'              => 'nullable|string|max:64',
            'calibration_status'       => 'nullable|string|max:64',
            'completion_time'          => 'nullable|date_format:Y-m-d H:i:s',
            'milestone_time'           => 'nullable|date_format:Y-m-d H:i:s',
        ];
        $arrMessage = [
            'name.required'       => '功能名称不能为空',
            'func_level.required' => '功能级别不能为空',
            'func_level.numeric'  => '功能级别必须为数字',
        ];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, $arrMessage);

        DB::transaction(function () use($funcSer,$params,$masterId,$slaveId,$request){

            //更新主功能
            $funcSer->editFunction($masterId,$params,$request->header('authorization'));
            $funcSer->delFunctionPartRefAll($masterId);

            //删副功能
            $funcSer->delFunction([$slaveId],$this->user()['id'],$request->header('authorization'));
            $funcSer->delFunctionPartRefAll($slaveId);
        });
    }

    public function checkFuncWikiPage(Request $request,FunctionObjService $serFuncObj,WikiService $serWiki)
    {
        $token = $request->header('authorization');
        $data = $serFuncObj->syncWikiPage($serWiki,$token);
        return $this->response->array(['status_code'=>200,'message'=>'成功','data' => $data]);
    }

    public function checkFuncEdit($funcId,$userId,FunctionObjService $obj)
    {
        $is = $obj->checkEdit($userId,$funcId);
        return $this->response->array(['status_code'=>200,'message'=>'','data' => $is]);
    }


}
