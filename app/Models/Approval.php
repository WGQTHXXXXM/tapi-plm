<?php

namespace App\Models;


class Approval extends BaseModel
{

    protected $fillable = ['level', 'plan_completed_time', 'last_limited_time', 'owner_id', 'project_id', 'sqer_id', 'purchaser_id','owner_name', 'project_name', 'sqer_name', 'purchaser_name', 'supplier_name', 'is_elec'];

    const STATUS_PROCESSING = 'processing'; //进行中;
    const STATUS_NEED = 'need';  //待处理
    const STATUS_FINISH = 'finish';   //结束
    const STATUS_CREATEING = 'createing';   //待启动

    const TASK_START = 'start';
    const TASK_READY = 'ready';
    const TASK_END = 'end';

    const PARTICPENT_INDIVIDUAL = 'individual';
    const PARTICPENT_GROUP = 'group';

    protected $appends = ['task_flow'];

    protected $taskFlowData;

    protected $casts = [
        'is_elec' => 'boolean',
    ];

    ///关联表
    public function files()
    {
        return $this->hasMany(ApprovalFile::class, 'approval_id', 'id');
    }

    public function setTaskFlowAttribute($data)
    {
        $data = json_encode($data);
        $this->taskFlowData = json_decode($data,true);
    }

    public function getTaskFlowAttribute()
    {
        if (!$this->taskFlowData) {
            $data = TaskFlow::getInstance($this->flow_instance_id);
            $this->taskFlowData = $data;
            return $data;
        } else {
            return $this->taskFlowData;
        }
    }
}
