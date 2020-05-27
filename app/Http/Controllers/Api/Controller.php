<?php

namespace App\Http\Controllers\Api;

use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class Controller extends BaseController {
    use Helpers;

    protected $reservedWords = ['page', 'per_page', 'filters'];

	/**
     * 重写user() 方法
     * @return mixed
     */
    public function user()
    {
//        return [
//
//        "id"=> "c35f13d0c4a511e98a40119de2e73ab0",
//        "user_id"=> "a9dd4db2bf6c49378044779f0cfc0cb3",
//        "ding_userid"=> "manager4894",
//        "ding_unionid"=> "mpiSQnVzw2NBbp7Ru0roCTwiEiE",
//        "name"=> "宋崴",
//        "phone"=> "18600209145",
//        "email"=> "songwei@singulato.com",
//        "status"=> "normal",
//        "created_by"=> null,
//        "updated_by"=> null,
//        "created_at"=> "2019-08-22 14:26:22",
//        "updated_at"=> "2019-08-30 16:54:03"
//
//        ];
        return User::$detailUser;
    }


    protected function ignoreReserved(array $params)
    {
        foreach ( $this->reservedWords as $reservedKey ) {
            if (array_key_exists($reservedKey, $params)) {
                unset($params[$reservedKey]);
            }
        }
        return $params;
    }

    protected function removeEmptyParameters(array $params)
    {
        foreach ($params as $key => $value) {
            if (is_string($value) && empty($value)) {
                unset($params[$key]);
            }
        }
        return $params;
    }

    /**
     * 获取字段验证允许的字段.
     * @param $request
     * @return array
     */
    public function allowRules($request)
    {
        $all_params = array_only($request->all(), array_keys($request->rules()));
        foreach ($all_params as $k=>$v){
            if( $v === null ){
                unset($all_params[$k]);
            }
        }
        return $all_params;
    }

    /**
     * 获取字段验证允许的字段.
     * @param $request
     * @param $expect
     * @return array
     */
    public function allowRulesParams($request,array $expect)
    {
        $filter = [];
        $params = $request->all();
        foreach ($expect as $v){
            if(isset($params[$v])){
                $filter[$v] = $params[$v];
            }
        }
        $rules = array_only(array_dot($params), array_keys($request->rules()));
        foreach($rules as $k => $v){
            $ex = explode('.',$k);
            if( count($ex) > 1 ){
                $rules[$ex[0]][$ex[1]] = $v;
                unset($rules[$k]);
            }
        }
        return array_merge($rules,$filter);
    }

	/**
	 * 校验参数
	 * @param $params 要校验的参数
	 * @param $arrOnly 允许的参数
	 * @param $arrColumn 允许的参数规则
	 * @param $arrMessage 提示信息
	 * @return array
	 * @throws \Exception
	 */
	public function doValidate($params, $arrOnly, $arrColumn, $arrMessage)
	{
		try {
			$reqData = Arr::only($params, $arrOnly);
			$validator = Validator::make($reqData, $arrColumn, $arrMessage);
			if ($validator->fails()) {
				throw new \Exception($validator->errors()->first());
			}
			return $reqData;
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
    }
    
    /**
     * 设置响应数据
     * 
     * @param array $data
     * @param int $code
     * @param string $message
     * @return Response
     */
    public function setViewData(array $data = [], int $code = 0, string $message = null) {
        return $this->response->array([
            'code'      => $code,
            'message'   => $message,
            'data'      => $data
            ]);
    }

}
