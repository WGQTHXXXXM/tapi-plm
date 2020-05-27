<?php

namespace App\Models;

use App\Models\Abstracts\Model;

class Role extends Model
{
    //
    protected $fillable = ['project_id','project_code','name','description'];

///////////////关联////////////////////////////////////

    public function roleUserRef()
    {
        return $this->hasMany(RoleUserRef::class,'role_id','id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class,'role_user_ref', 'role_id','user_id')->withPivot('type');
    }

    public function createdBy()
    {
        return $this->hasOne(User::class,'id','created_by');
    }

}
