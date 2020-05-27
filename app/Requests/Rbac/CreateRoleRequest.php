<?php

namespace App\Http\Requests\Rbac;


use App\Http\Requests\BaseRequest;

class CreateRoleRequest extends BaseRequest
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
            'description' => 'max:25',
            'projectIdentify' => 'required',
            'roleName' => 'required|max:50',
        ];
    }

    public function messages()
    {
        return [
            'description.max' => '描述最多25个',
            'projectIdentify.required' => '项目id不能为空',
            'roleName.required' => '角色名称不能为空',
            'roleName.max' => '角色名最多50个字符',
        ];
    }

    public function errorCode(): array
    {
        return [
            'description' => 10001,
            'projectIdentify' => 10002,
            'roleName' => 10003,
        ];

    }

}
