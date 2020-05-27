<?php

namespace App\Http\Requests\Tasks;


use App\Http\Requests\BaseRequest;

class AddParticipantRequest extends BaseRequest
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
            'name' => 'required',
            'key_id' => 'required',
            'type' => 'required|in:individual,group'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '名称不能为空',
            'key_id.required' => 'Id不能为空',
            'type.required' => '类型不能为空',
            'type.in' => '类型只能是角色或个人（individual，group）',

        ];
    }

    public function errorCode(): array
    {
        return [
            'name' => 10001,
            'key_id' => 10002,
            'type' => 10003,
        ];

    }

}
