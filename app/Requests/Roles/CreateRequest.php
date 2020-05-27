<?php

namespace App\Http\Requests\Roles;


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
            'project_id' => 'required',
            'project_code' => 'required',
            'name' => 'required|between:3,50',
            'description' => 'string'
        ];
    }

    public function messages()
    {
        return [
            'project_id.required' => '项目id不能为空',
            'project_code.required' => '项目code不能为空',
            'name.required' => '角色名称不能为空',

        ];
    }

    public function errorCode(): array
    {
        return [
            'project_id' => 10001,
            'project_code' => 10002,
            'name' => 10003,
        ];

    }

}
