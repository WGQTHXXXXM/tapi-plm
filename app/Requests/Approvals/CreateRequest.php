<?php

namespace App\Http\Requests\Approvals;


use App\Http\Requests\BaseRequest;

class CreateRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|between:3,50',
            'tpl_id' => 'required',
            'project_id' => 'required',
            'project_name' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '审批名称不能为空',
            'name.between' => '审批名称在3-50个字符之间',
            'tpl_id.required' => '必须选择一个模板',
            'project_id.required' => '项目不能为空必须填写',
            'project_name.required' => '项目名字不能为空必须填写',

        ];
    }

    public function errorCode(): array
    {
        return [
            'name' => 10001,
            'tpl_id' => 10002,
            'project_id' => 10003,
            'project_name' => 10004,
        ];

    }

}
