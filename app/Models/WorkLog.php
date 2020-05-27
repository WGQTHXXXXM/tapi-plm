<?php

namespace App\Models;

use App\Models\Abstracts\Model;

class WorkLog extends Model
{
    //
    protected $table = 'work_log';
    protected $fillable = ['work_id', 'remark', 'type'];

    ///////////////关联////////////////////////////////////

}
