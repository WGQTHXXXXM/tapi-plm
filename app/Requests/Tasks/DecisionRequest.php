<?php

namespace App\Http\Requests\Tasks;


use App\Http\Requests\BaseRequest;

class DecisionRequest extends BaseRequest
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
            'content' => 'required',
            'select_key' => 'required',
            'user_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'content.required' => '审批描述不能为空',
            'select_key.required' => '选择结果不能为空',
            'user_id.required' => '用户id不能为空',

        ];
    }

    public function errorCode(): array
    {
        return [
            'content' => 10001,
            'select_key' => 10002,
            'user_id' => 10003
        ];

    }

}
