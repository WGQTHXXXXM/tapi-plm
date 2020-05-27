<?php

namespace App\Models;

use App\Models\Abstracts\Model;

class WorkFront extends Model
{
    //
    protected $table = 'work_front';
    protected $fillable = ['work_id','front_work_id'];
    public $timestamps = false;
    public $operators = false;
///////////////关联////////////////////////////////////

}
