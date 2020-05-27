<?php

namespace App\Models;

use App\Models\Abstracts\Model;

class Work extends Model
{
    //
    protected $table = 'work';
    protected $fillable = ['project_id', 'name', 'level','start_at','end_at','parent_id','assign_type','assign_obj_id',
        'file_upload','milestone_id','delivery_name','percent_complete'];
    public $timestamps = false;
///////////////关联////////////////////////////////////

}
