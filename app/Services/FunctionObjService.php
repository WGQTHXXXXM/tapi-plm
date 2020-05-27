<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\FunctionDel;
use App\Models\FunctionObj;
use App\Models\FunctionPartRef;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FunctionObjService
{
	/**
	 * 查询列表|统计总数
	 * @param array $params
	 * @param array $paginator
	 * @param bool $count
	 * @return mixed
	 * @throws \Exception
	 */
	public function basicInquire($params = array(), $paginator = [], $count = false)
	{
		try {
			$function = new FunctionObj();
			$where = $whereIn = $whereLike = $whereBetween = $orWhere = array();
			$whereRef = $whereInRef = $whereLikeRef = $whereBetweenRef = $orWhereRef = $with = array();
			$whereCondition = app(CommonService::class)->sqlPrepare($params, $function);
			//精确条件查询
			$where = $whereCondition['where'];
			//in查询条件
			$whereIn = $whereCondition['whereIn'];
			//模糊查询条件
			$whereLike = $whereCondition['whereLike'];
			//区间查询条件
			$whereBetween = $whereCondition['whereBetween'];
			//或条件查询
			$orWhere = $whereCondition['orWhere'];

			//专项查询：根据等级，同时必须传选中的功能ID
			if ((isset($params['parent_func_level1']) && !empty($params['parent_func_level1']))
				&& (isset($params['parent_func_level2']) && !empty($params['parent_func_level2']))) {//既有一级也有二级，同只查询二级下所有功能
				$where['parent_func_id'] = $params['parent_func_level2'];
			} else if ((isset($params['parent_func_level1']) && !empty($params['parent_func_level1']))
				&& (!isset($params['parent_func_level2']) || empty($params['parent_func_level2']))) {//只查询一级下所有功能，需要根据一级ID获取下属所有的二级ID
				$where_ids_2 = ['parent_func_id' => $params['parent_func_level1']];
				$func_ids_2 = FunctionObj::where($where_ids_2)->selectRaw('id')->get();
				$tmp = [];
				foreach ($func_ids_2 as $value_id) {
					$tmp[] = $value_id->id;
				}
				$whereIn['parent_func_id'] = $tmp;
			} else if ((!isset($params['parent_func_level1']) || empty($params['parent_func_level1']))
				&& (isset($params['parent_func_level2']) && !empty($params['parent_func_level2']))) {//只查询二级下所有功能
				$where['parent_func_id'] = $params['parent_func_level2'];
			} else {//默认查询所有三级功能
				// TODO...
			}

			//关联表查询
			$whereRef = $whereCondition['whereRef'];
			$whereInRef = $whereCondition['whereInRef'];
			$whereLikeRef = $whereCondition['whereLikeRef'];
			$whereBetweenRef = $whereCondition['whereBetweenRef'];
			$orWhereRef = $whereCondition['orWhereRef'];
			$with = [
				//'eloquent' => 'pivot',//指定关联关系，''空字符串默认一对一 one2one一对一 one2many一对多 pivot多对多
				'table' => ['owner_name'],//关联关系
				//'with' => []//关系表查询条件
			];
			if (!empty($whereRef)) {
				$with['with']['where'] = $whereRef;
			}
			if (!empty($whereInRef)) {
				$with['with']['whereIn'] = $whereInRef;
			}
			if (!empty($whereLikeRef)) {
				$with['with']['like'] = $whereLikeRef;
			}
			if (!empty($whereBetweenRef)) {
				$with['with']['between'] = $whereBetweenRef;
			}
			if (!empty($orWhereRef)) {
				$with['with']['or'] = $orWhereRef;
			}

			$where['func_level'] = FunctionObj::LEVEL_THREE;//所有查询只返回三级功能
			$query = $function->where($where);
			$result = app(CommonService::class)->basicQuery($query, $with, $count, $paginator, $whereIn, $whereLike, $whereBetween, $orWhere);
			return $result;
		} catch (\Exception $e) {
			Log::info('No function data: '.$e->getMessage().'\n');
			throw new \Exception('没有功能数据'.$e->getMessage());
		}
	}

	/**
	 * 功能列表
	 * @param array $params
	 * @param array $paginator
	 * @return mixed
	 */
    public function getFunction($params = array(), $paginator = [])
	{
		return $this->basicInquire($params, $paginator);
    }

	/**
	 * 获取当前功能的上级功能信息
	 * @param $id
	 * @return array
	 */
    public function getParentFunctions($id)
	{
		$parent = [];
		$functionModel = new FunctionObj();
		$where = ['id' => $id];
		$function = $functionModel->where($where)->first();
		$level = $function['func_level'];//该功能等级
		$parent_id = $function['parent_func_id'];//上级ID
		$select_raw = 'id,name,func_level,parent_func_id,owner_id,lead_ecu,belong_to_system';

		do {
            $where_parent = ['id' => $parent_id];
            $result = $functionModel->selectRaw($select_raw)->where($where_parent)->first();
            if (isset($result['func_level']) && $result['func_level'] < $level) {//有上级功能信息
                $level--;
                $parent[$result['func_level']] = $result->toArray();
                $parent_id = $result['parent_func_id'];
            }
        } while (!empty($parent_id));
		return $parent;
	}

	/**
	 * 获取子级功能
	 * @param $id
	 * @return array
	 */
	public function getChildFunctions($id)
	{
		$child = [];
		$functionModel = new FunctionObj();
		$where = ['id' => $id];
		$function = $functionModel->where($where)->first();
		$level = $function['func_level'];//该功能等级
		$level_max = $functionModel->max('func_level');//最大等级数
		$child = $this->getChildFunctionByParent($id);

		return $child;
	}

	/**
	 * 递归处理获取子级功能
	 * @param $parent_id
	 * @return array
	 */
	public function getChildFunctionByParent($parent_id)
	{
        $child = [];
        $functionModel = new FunctionObj();
        $select_raw = 'id,name,func_level,parent_func_id,wiki_page_id';
        $functions = $functionModel->selectRaw($select_raw)->where(['parent_func_id' => $parent_id])->get();//查询该功能ID下的子级
        if (count($functions) > 0) {//有子级
            foreach ($functions as $key => $function) {
                $func_id = $function['id'];
                $level_current = $function['func_level'];//该功能等级
                $childFunctions = $this->getChildFunctionByParent($func_id);
                if (count($childFunctions) > 0) {
                    $child_tmp = $function->toArray();
                    $child_tmp['child'] = $this->getChildFunctionByParent($func_id);
                    $child[] = $child_tmp;
                } else {
                    $child[] = $function->toArray();
                }
            }
        } else {//没有子级，该功能是最后一级
            //
        }

        return $child;
	}

	/**
	 * 功能树
	 * @return array|mixed
	 */
    public function getFunctionTree($project_id)
	{
		$functionModel = new FunctionObj();
		$select_raw = 'id,name,func_level,parent_func_id';
		$functions = $functionModel->where(['project_id'=>$project_id])->selectRaw($select_raw)->get();
		$level_max = $functionModel->where(['project_id'=>$project_id])->max('func_level');

		$tree = [];
		foreach ($functions as $key => $function) {
			$function_id = $function['id'];
			$func_level = $function['func_level'];//功能级别
			$parent_func_id = $function['parent_func_id'];//上级功能ID，空为第一级
			for ($i = 1; $i <= $level_max; $i++) {
				if ($func_level == $i) {
					$tree[$func_level][$function_id] = $function->toArray();
				}
			}
		}

		ksort($tree);
		$tree_current = $tree[1] ?? [];//第一级功能
		$tree_next    = $tree[2] ?? [];
		foreach ($tree_current as $id_current => $data_current) {
			$tree_current[$id_current]['next'] = $this->dealFunction($tree, $data_current, $tree_next);
		}

		return $tree_current;
	}

	/**
	 * 递归处理功能
	 * @param $tree 整个功能树
	 * @param $data_current 当前执行等级的一项数据
	 * @param array $tree_next 当前执行等级的下一级数据
	 * @return array
	 */
	public function dealFunction($tree, $data_current, $tree_next = [])
	{
		$data_current_next = [];
		if (empty($tree_next)) {//没有下一级

		} else {//有下一级
			$id_current = $data_current['id'];
			foreach ($tree_next as $id => $data) {
				$func_level      = $data['func_level'];
				$parent_func_id  = $data['parent_func_id'];
				$func_level_next = $func_level + 1;
				if ($parent_func_id == $id_current) {//处理当前功能的子级功能
					if (isset($tree[$func_level_next]) && !empty($tree[$func_level_next])) {
						$tree_next[$id]['next'] = $this->dealFunction($tree, $data, $tree[$func_level_next]);
						$data_current_next[] = $tree_next[$id];
					} else {
						$data_current_next[] = $data;
					}
				}
			}
		}
		return $data_current_next;
	}

	/**
	 * 根据功能ID查询功能
	 * @param $id
	 * @param $select 查询字段
	 * @return Collection|\Illuminate\Database\Eloquent\Model|mixed|null|static|static[]
	 */
	public function getFunctionById($id, $select = '*')
	{
		$result = null;
		$functionModel = new FunctionObj();
		$where = ['id' => $id];
		if ($select == '*') {
			$result = $functionModel->where($where)->first();
		} else {
			$result = $functionModel->selectRaw($select)->where($where)->first();
		}
		if (!is_null($result)) {
			$parent = $this->getParentFunctions($id);
			if (!empty($parent)) {
				$result->parent = $parent;
			} else {
				$result->parent = '';
			}
			//下级数量
            $result->sonCount = $this->getSonCount($result);
			//前后项
            $this->getFuncSibling($result);
		}
		
        return is_null($result) ? $functionModel : $result;
    }

	/*
	 * 功能的前后项目ID
	 */
	public function getFuncSibling(FunctionObj &$mdl)
    {
        $id = $mdl->id;
        $arrTmp = FunctionObj::where(['parent_func_id'=>$mdl->parent_func_id,'project_id'=>$mdl->project_id])
            ->pluck('id')->toArray();
        $index = array_search($id,$arrTmp);
        $mdl->preFunc = isset($arrTmp[$index-1])?$arrTmp[$index-1]:'';
        $mdl->nextFunc = isset($arrTmp[$index+1])?$arrTmp[$index+1]:'';
    }

    /**下级数量显示
     * @param FunctionObj $mdl
     * @return string
     */
	public function getSonCount(FunctionObj $mdl)
    {
        $strCnt = '0';
        if($mdl->func_level==1){
            $mdlTmp = FunctionObj::where(['parent_func_id'=>$mdl->id])->pluck('id')->toArray();
            if (empty($mdlTmp))
                $strCnt = '0';
            $strCnt = count($mdlTmp);
            $mdlTmp = FunctionObj::whereIn('parent_func_id',$mdlTmp)->count();
            if (empty($mdlTmp))
            {
                $strCnt = $strCnt.'/0';
                return $strCnt;
            }
            return $strCnt.'/'.$mdlTmp;
        }
        if($mdl->func_level==2){
            $mdlTmp = FunctionObj::where(['parent_func_id'=>$mdl->id])->count();
            if (empty($mdlTmp))
                $strCnt = '0';
            $strCnt = $mdlTmp;
        }
        return $strCnt;
    }

	/**
	 * 根据功能ID查找关联的零部件信息
	 * @param $function_ids
	 * @return mixed
	 */
	public function getRefByFunctionId($function_ids, $select = '*')
	{
		$functionPartRefModel = new FunctionPartRef();
		if ($select == '*' ) {
			$result = $functionPartRefModel->whereIn('function_id', $function_ids)->get();
		} else {
			$result = [];
			foreach ($function_ids as $function_id) {
				$where = ['function_id' => $function_id];
				$partIds = $functionPartRefModel->selectRaw($select)->where($where)->get()->toArray();
				foreach ($partIds as $part_id) {
					$result[$function_id][] = $part_id['part_id'];
				}
			}
		}

		return $result;
	}

	public function getSonThreeId(FunctionObj $mdlFunction)
    {
        if($mdlFunction->func_level == FunctionObj::LEVEL_THREE)
            return [$mdlFunction->id];
        if($mdlFunction->func_level == FunctionObj::LEVEL_TWO){
            return FunctionObj::where(['parent_func_id'=>$mdlFunction->id])->pluck('id')->toArray();
        }
        $mdl2Ids = FunctionObj::where(['parent_func_id'=>$mdlFunction->id])->pluck('id')->toArray();

        return FunctionObj::whereIn('parent_func_id',$mdl2Ids)->pluck('id')->toArray();
    }

	/**
	 * 根据功能ID查找关联的零部件信息
	 * @param $function_id
	 * @return mixed
	 */
	public function getRefPageByFunctionId($function_id, $select = '*', $paginator = [], $count = false)
	{
        try {
            $mdlCur = FunctionObj::find($function_id);
            if (empty($mdlCur)){
                throw new \Exception('这个功能可能已经被别人删除,请返回功能列表页。');
            }

            $arrIds = $this->getSonThreeId($mdlCur);
            $strIds = implode(',',$arrIds);

			$params = ['function_id_in' => $strIds];

			$functionPartRef = new FunctionPartRef();
			$where = $whereIn = $whereLike = $whereBetween = $orWhere = array();
			$whereRef = $whereInRef = $whereLikeRef = $whereBetweenRef = $orWhereRef = $with = array();
			$whereCondition = app(CommonService::class)->sqlPrepare($params, $functionPartRef);
			//精确条件查询
			$where = $whereCondition['where'];
			//in查询条件
			$whereIn = $whereCondition['whereIn'];

			//模糊查询条件
			$whereLike = $whereCondition['whereLike'];
			//区间查询条件
			$whereBetween = $whereCondition['whereBetween'];
			//或条件查询
			$orWhere = $whereCondition['orWhere'];

			//关联表查询
			$whereRef = $whereCondition['whereRef'];
			$whereInRef = $whereCondition['whereInRef'];
			$whereLikeRef = $whereCondition['whereLikeRef'];
			$whereBetweenRef = $whereCondition['whereBetweenRef'];
			$orWhereRef = $whereCondition['orWhereRef'];
			$with = [
				//'eloquent' => 'pivot',//指定关联关系，''空字符串默认一对一 one2one一对一 one2many一对多 pivot多对多
				//'table' => ['created_name'],//关联关系
				//'with' => []//关系表查询条件
			];
			if (!empty($whereRef)) {
				$with['with']['where'] = $whereRef;
			}
			if (!empty($whereInRef)) {
				$with['with']['whereIn'] = $whereInRef;
			}
			if (!empty($whereLikeRef)) {
				$with['with']['like'] = $whereLikeRef;
			}
			if (!empty($whereBetweenRef)) {
				$with['with']['between'] = $whereBetweenRef;
			}
			if (!empty($orWhereRef)) {
				$with['with']['or'] = $orWhereRef;
			}

			$query = $functionPartRef->selectRaw($select)->where($where)->groupBy('part_id');

			$result = app(CommonService::class)->basicQuery($query, $with, $count, $paginator, $whereIn, $whereLike, $whereBetween, $orWhere);
			return $result;
		} catch (\Exception $e) {
			Log::info('No ref data: '.$e->getMessage().'\n');
			throw new \Exception('没有功能关联零部件数据:'.$e->getMessage());
		}
	}

	/**
	 * 根据功能名称查询功能
	 * @param $name
	 * @return Collection|\Illuminate\Database\Eloquent\Model|mixed|null|static|static[]
	 */
	public function getFunctionByName($name)
	{
		$functionModel = new FunctionObj();
		$result = $functionModel->where(['name' => $name])->first();
		return is_null($result) ? $functionModel : $result;
	}

	/**
	 * 创建功能
	 * @param $params
	 * @return FunctionObj
	 */
	public function addFunction($params,$token,$ancestor,$proName)
	{
		$function = new FunctionObj();
		DB::transaction(function () use ($params, $function) {
			try {
				$function->fill($params)->save();
			} catch (\Exception $e) {
				Log::info('Create function error: '.$e->getMessage().'\n');
				throw new \Exception('创建功能失败'.$e->getMessage());
			}
		});
        //新建wiki页面
//        $serWiki = app(WikiService::class);
//        try{
//            $pageName = app(FuncDocRefService::class)->generateFileName($function->id,$proName);
//            $wikiPageId = $serWiki->createPage($pageName,$ancestor,$token);
//        } catch (\Exception $e){
//            $function->delete();
//            throw new \Exception('wiki新建页面失败：'.$e->getMessage());
//        }
//        try{
//            $function->wiki_page_id = $wikiPageId;
//            $function->save();
//        } catch (\Exception $e){
//            $function->delete();
//            $serWiki->deletePage($wikiPageId,$token);
//            throw new \Exception('wiki新建页面失败：'.$e->getMessage());
//        }

        return $function;
	}

	/**
	 * 关联零部件
	 * @param $params
	 * @return bool
	 */
	public function addFunctionPartRef($params)
	{
		DB::transaction(function () use ($params) {
			try {
				foreach ($params as $param) {
					$functionPart = new FunctionPartRef();
					$functionPart->fill($param)->save();
				}
			} catch (\Exception $e) {
				Log::info('Create function part ref error: '.$e->getMessage().'\n');
				throw new \Exception('关联零部件失败'.$e->getMessage());
			}
		});
		return true;
	}

    /**
     * 取消关联零部件
     * @param $params
     * @return bool
     */
    public function delFunctionPartRef($function_id,$parts_id)
    {
        try {
            FunctionPartRef::where(['function_id'=>$function_id])->whereIn('part_id',$parts_id)->delete();
        } catch (\Exception $e) {
            Log::info('Delete function part ref error: '.$e->getMessage().'\n');
            throw new \Exception('取消关联零部件失败'.$e->getMessage());
        }
        return true;
    }

    /**
     * 取消功能关联的所有零部件
     * @param $params
     * @return bool
     */
    public function delFunctionPartRefAll($function_id)
    {
        try {
            FunctionPartRef::where(['function_id'=>$function_id])->delete();
        } catch (\Exception $e) {
            Log::info('Delete function part ref all error: '.$e->getMessage().'\n');
            throw new \Exception('取消功能关联的所有零部件失败'.$e->getMessage());
        }
        return true;
    }

    /**
	 * 编辑功能
	 * @param $where
	 * @param $params
	 */
	public function editFunction($id, $params,$token)
	{
        DB::beginTransaction();
        try {
            $function = FunctionObj::find($id);
            $function->update($params);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Edit function error: '.$e->getMessage().'\n');
            throw new \Exception('编辑功能失败'.$e->getMessage());
        }

        //更新wiki页面
//        $serWiki = app(WikiService::class);
//        $proName = $serWiki->getProjectById($function->project_id,$token)->businessObj->name;
//        try{
//            $pageName = app(FuncDocRefService::class)->generateFileName($function->id,$proName);
//            $serWiki->updatePage(['title'=>$pageName],$function->wiki_page_id,$token);
//        } catch (\Exception $e){
//            DB::rollBack();
//            throw new \Exception('wiki页面更新失败：'.$e->getMessage());
//        }
//
//        $arrChilds = $this->getChildFunctionByParent($id);
//        $this->updateChildWikiPage($arrChilds,$serWiki,$proName,$token);
        DB::commit();

        return $function;
	}

	public function updateChildWikiPage($arrChilds,$serWiki,$proName,$token)
    {
        //更新wiki页面
        foreach ($arrChilds as $child){
            try{
                $pageName = app(FuncDocRefService::class)->generateFileName($child['id'],$proName);
                $serWiki->updatePage(['title'=>$pageName],$child['wiki_page_id'],$token);
            } catch (\Exception $e){
                DB::rollBack();
                throw new \Exception('wiki页面更新失败：'.$e->getMessage());
            }
            if(!empty($child['child'])){
                $this->updateChildWikiPage($child['child'],$serWiki,$proName,$token);
            }
        }
    }

	/**
	 * 删除功能
	 * @param $ids
	 * @return bool
	 * @throws \Exception
	 */
	public function delFunction($ids,$uid,$token)
	{
        DB::beginTransaction();
        foreach ($ids as $id){
            try {
            //先删除再保存
                $delMdl = FunctionObj::where('id',$id)->first();
                if(empty($delMdl))
                    throw new \Exception('这个功能可能已经被别人删除,请返回功能列表页。');
                $wikiPageId = $delMdl->wiki_page_id;
                $arrFill = $delMdl->toArray();
                $delMdl->delete();

                //把删除的功能保存到删除表
                unset($arrFill['id']);
                $arrFill['del_at'] = date('Y-m-d H:i:s',time());
                $arrFill['del_by'] = $uid;
                $mdlFuncDel = new FunctionDel();
                $mdlFuncDel->fill($arrFill)->save();

            } catch (\Exception $e) {
                DB::rollBack();
                Log::info('Delete function error: '.$e->getMessage().'\n');
                throw new \Exception('删除功能失败:'.$e->getMessage());
            }
            //删除wiki页面
//            $serWiki = app(WikiService::class);
//            try{
//                $serWiki->deletePage($wikiPageId,$token);
//            } catch (\Exception $e){
//                DB::rollBack();
//                throw new \Exception('wiki页面更新失败：'.$e->getMessage());
//            }
        }
        DB::commit();
		return true;
	}

	public function syncWikiPage($serWiki,$token)
    {
        $arrWikiId = $serWiki->getWikiPageAll($token);//wiki上PLM空间下所有的页面

         //变化的功能页面
        $funcChang =[];
        FunctionObj::orderBy('func_level')->chunk(50,function ($funcs) use ($serWiki,$token,&$funcChang,&$arrWikiId){
            $ADD = '增加';$UPD = '变化';
            $proIdName=[];//项目id对应的项目名字
            foreach ($funcs as $func){
                if(empty($func->wiki_page_id)){//如果页面不存在要新建
                    $funcChang[$ADD][] = $func->id;
                    $this->syncWikiCreatePage($serWiki,$proIdName,$token,$func,$arrWikiId);
                } else {//如果wiki页面存在，要检查页面名字对不对。
                    unset($arrWikiId[$func->wiki_page_id]);
                    if(empty($proIdName[$func->project_id])){//如果没有就请求项目接口，然后保存起来。
                        $project = $serWiki->getProjectById($func->project_id,$token);
                        $proIdName[$func->project_id] = $project->businessObj;
                        unset($arrWikiId[$project->businessObj->wikiId]);
                    }
                    //得到wiki页面的父id与系统功能的父wiki_page_id对比//
                    $wikiPage = $serWiki->findPage($token,$func->wiki_page_id,['expand'=>'ancestors']);
                    if(empty($wikiPage)){//如果没找到wiki上对应的页面要新建一个
                        $funcChang[$ADD][] = $func->id;
                        $this->syncWikiCreatePage($serWiki,$proIdName,$token,$func,$arrWikiId);
                    }else{//否则就去检查是否一至，不一至就改
                        //系统功能的父wiki_page_id
                        if($func->func_level == FunctionObj::LEVEL_ONE)
                            $sysFuncParentWikiId = $proIdName[$func->project_id]->wikiId;
                        else
                            $sysFuncParentWikiId = FunctionObj::find($func->parent_func_id)->wiki_page_id;
                        $wikiFuncParentWikiId = end($wikiPage->ancestors)->id;//wiki页面的父id
                        if($wikiFuncParentWikiId != $sysFuncParentWikiId){//对比是不是在相同的树下
                            try{
                                $wikiData = ['title'=>$wikiPage->title,"ancestors"=>[["id"=> $sysFuncParentWikiId]]];
                                $serWiki->updatePage($wikiData, $func->wiki_page_id,$token);
                            } catch (\GuzzleHttp\Exception\RequestException $e){
                                throw new \Exception('检查wiki页面失败：'.$e->getResponse()->getBody()->getContents());
                            }
                            $funcChang[$ADD][] = $func->id.'---调整位置';
                        }
                        $pageName = app(FuncDocRefService::class)->generateFileName($func->id,$proIdName[$func->project_id]->name);
                        if($pageName!=$wikiPage->title){//对比名字是不是一样的
                            try{
                                $serWiki->updatePage(['title'=>$pageName],$func->wiki_page_id,$token);
                            } catch (\GuzzleHttp\Exception\RequestException $e){
                                throw new \Exception('检查wiki页面失败：'.$e->getResponse()->getBody()->getContents());
                            }
                            $funcChang[$ADD][] = $func->id.'---调整名字';
                        }
                    }
                }
            }
        });

        //删除多余的页面。
        foreach ($arrWikiId as $pageId=>$aa){
            $serWiki->deletePage($pageId,$token);
            $funcChang['删除'][] = $pageId;
        }

        return $funcChang;
    }

    public function syncWikiCreatePage($serWiki,&$proIdName,$token,$func,&$arrWikiId)
    {
        if(empty($proIdName[$func->project_id])){//如果没有就请求项目接口，然后保存起来。
            $project = $serWiki->getProjectById($func->project_id,$token);
            $proIdName[$func->project_id] = $project->businessObj;
            unset($arrWikiId[$project->businessObj->wikiId]);
        }
        $proName = $proIdName[$func->project_id]->name;
        $pageName = app(FuncDocRefService::class)->generateFileName($func->id,$proName);
        if($func->func_level == FunctionObj::LEVEL_ONE)
            $ancestor =$proIdName[$func->project_id]->wikiId;
        else
            $ancestor = FunctionObj::find($func->parent_func_id)->wiki_page_id;
        try{
            $wikiPageId = $serWiki->createPage($pageName,$ancestor,$token);
        } catch (\GuzzleHttp\Exception\RequestException $e){
            throw new \Exception('创建wiki页面失败：'.$e->getResponse()->getBody()->getContents());
        }

        $func->wiki_page_id= $wikiPageId;$func->save();
    }

    /*
     * 判断用户是否可以编辑功能
     */
    public function checkEdit($userId,$funcId)
    {
        $func = FunctionObj::find($funcId);
        if(empty($func))
            throw new LogicException('没有此功能');
        if($func->owner_id == $userId)
            return true;
        $parents = $this->getParentFunctions($funcId);
        foreach ($parents as $parent){
            if($parent['owner_id'] == $userId)
                return true;
        }
        return false;
    }

}
