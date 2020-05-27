<?php

namespace App\Models;

use App\Models\Abstracts\Model;


class DepartmentUserRef extends Model
{
    //
    protected $table = 'department_user_ref';

    protected $fillable = ['department_id','user_id'];
    public $operators = false;

}
