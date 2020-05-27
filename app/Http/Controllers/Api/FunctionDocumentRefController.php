<?php

namespace App\Http\Controllers\Api;

use App\Models\FunctionDocumentRef;
use App\Services\FuncDocRefService;
use App\Transformers\DefaultTransformer;
use Illuminate\Http\Request;

class FunctionDocumentRefController extends Controller
{
    /**
     *交付物清单列表
     */
    public function show($function_id, FuncDocRefService $functionObjService)
    {
        $res = $functionObjService->getMaxVersion($function_id);

        return $this->response->array($res, new DefaultTransformer())->setStatusCode(200);
    }

    /**新建一个上传的交付物清单
     *
     */
    public function create(Request $request, FuncDocRefService $functionObjService)
    {
        //检查
        $arrOnly = [
            'function_id',
            'type',
            'name',
            'project_name'
        ];
        $arrColumn = [
            'function_id'         => 'required|string|max:128',
            'type'                => 'required|string|max:128',
            'name'                => 'required|string|max:128',
            'project_name'        => 'required|string|max:128',
        ];
        $arrMessage = [
            'function_id.required' => '功能名称不能为空',
            'type.required'        => '类型不能为空',
            'name.required'        => '文件名不能为空',
            'project_name.required'=> '项目名不能为空',
        ];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, $arrMessage);
        //上传的文件名
        $uploadName = $functionObjService->generateFileName($params);
        //保存数据
        $functionObjService->saveRecord($params);

        return $this->response->array(['file_name'=>$uploadName]);
    }

    /**
     * 修改一个上传的交付物清单
     */
    public function update(Request $request,$id)
    {
        //检查
        $arrOnly = [
            'download_path',
        ];
        $arrColumn = [
            'download_path'         => 'required|string|max:256',
        ];
        $arrMessage = [
            'download_path.required' => '下载路径不能为空',
        ];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, $arrMessage);
        //更新
        $funcDocRefMdl = FunctionDocumentRef::find($id);
        if(empty($funcDocRefMdl)){
            throw new \LogicException('没有这条清单记录');
        }
        $funcDocRefMdl->fill($params)->save();

        return $this->response->noContent();
    }

    /*
     * 功能下某一格式的下载
     */
    public function getType(Request $request)
    {
        //检查
        $arrOnly = [
            'function_id',
            'type',
        ];
        $arrColumn = [
            'function_id'        => 'required|string|max:64',
            'type'        => 'required|string|max:64',
        ];
        $arrMessage = [
            'function_id.required' => '功能ID不能为空',
            'type.required' => '清单格式不能为空',
        ];
        $params = $this->doValidate($request->all(), $arrOnly, $arrColumn, $arrMessage);

        $res = FunctionDocumentRef::where(['function_id'=>$params['function_id'],'type'=>$params['type']])->with('createBy')->get();

        return $this->response->array($res, new DefaultTransformer())->setStatusCode(200);
    }

}
