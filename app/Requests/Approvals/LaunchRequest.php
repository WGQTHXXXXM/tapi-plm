<?php

namespace App\Http\Requests\Approvals;


use App\Http\Requests\BaseRequest;

class LaunchRequest extends BaseRequest
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
            'content' => 'max:500',
            'level' => 'required|integer',
            'plan_completed_time' => 'integer',
            'last_limited_time' => 'integer',
            'owner_id' => 'string',
            'sqer_id' => 'string',
            'purchaser_id' => 'string',
            'supplier_name' => 'max:20',
            'is_elec' => 'required|boolean',
            'owner_name' => 'string',
            'sqer_name' => 'string',
            'purchaser_name' => 'string',


        ];
    }

    public function messages()
    {
        return [
            'content.max' => '备注内容不能大于500个字',
            'level.required' => '优先级不能为空',
            'level.integer' => '优先级必须是数字',
            'plan_completed_time.integer' => '计划完成日期必须是UTC毫秒值',
            'last_limited_time.integer' => '最后限定时间必须是UTC毫秒值',
            'project_id.required' => '关联项目不能为空',
            'supplier_name.max' => '供应商名字必须20字以内',

        ];
    }

    public function errorCode(): array
    {
        return [
            'content' => 10001,
            'level' => 10002,
            'plan_completed_time' => 10003,
            'last_limited_time' => 10004,
            'owner_id' => 10005,
            'sqer_id' => 10007,
            'purchaser_id' => 10008,
            'supplier_name' => 10009,
            'is_elec' => 10010,
            'owner_name' => 10011,
            'sqer_name' => 10013,
            'purchaser_name' => 10014,
        ];

    }

}
