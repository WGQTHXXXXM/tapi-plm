<?php

namespace App\Services;


use App\Models\FunctionDocumentRef;
use App\Models\FunctionObj;
use Illuminate\Support\Facades\DB;

class FuncDocRefService
{

    /**
     * 功能下最高版本记录表
     * @param $function_id
     * @return mixed
     *
     */
    public function getMaxVersion($function_id)
    {
        return FunctionDocumentRef::from('function_document_ref as tbla')
            ->selectRaw('tbla.type,tbla.version,tbla.name,tbla.download_path,tbla.created_by,tbla.created_at,tbla.updated_at')
            ->rightJoin(DB::raw('(select type,max(version) as version from function_document_ref where function_id="'.$function_id.'" group by type) as tblb'),
                function ($join) use ($function_id){
                    $join->on('tbla.type', '=', 'tblb.type')->On('tbla.version', '=', 'tblb.version');//->on('tbla.function_id','=',$function_id);
                })->where(['tbla.function_id'=>$function_id])->with('createBy')->get();
    }

    /*
     * 上传的文件名
     */
    public function generateFileName($funcId,$proName)
    {
        $funcParent = app(FunctionObjService::class)->getParentFunctions($funcId);
        ksort($funcParent);
        $uploadName = $proName.'->';
        foreach ($funcParent as $item){
            $uploadName .= $item['name'].'->';
        }
        $funcName = FunctionObj::find($funcId)->name;
        $uploadName .= $funcName;//.'_'.$params['name'];
        return $uploadName;
    }

    /*
     * 保存一条记录
     */
    public function saveRecord($params)
    {
        try{
            $funcDocRefMdl = FunctionDocumentRef::where(['type'=>$params['type'],'function_id'=>$params['function_id']])
                ->max('version');
            if(empty($funcDocRefMdl)){
                $params['version'] = 1;
            } else {
                $params['created_at'] = FunctionDocumentRef::
                where(['type'=>$params['type'],'function_id'=>$params['function_id']])->min('created_at');
                $params['version'] = $funcDocRefMdl+1;
            }

            $newFuncDocRef = new FunctionDocumentRef();
            $params['status'] = $params['download_path'] = '';

            $newFuncDocRef->fill($params)->save();
        } catch (\LogicException $e) {
            throw new \Exception('保存出错：'.$e->getMessage());
        }
    }

}