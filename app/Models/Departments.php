<?php

namespace App\Models;

use App\Models\Abstracts\Model;

class Departments extends Model
{
    //
    protected $table = 'departments';
    public $operators = false;
    ///////////////关联////////////////////////////////////
    ///关联新表
    public function user()
    {
        return $this->belongsToMany(User::class,'department_user_ref','department_id','user_id');
    }

    protected $fillable = ['name', 'ding_id','ding_pid'];


}
