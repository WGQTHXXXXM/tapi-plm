<?php

namespace App\Models;

use App\Models\Abstracts\Model;

class RoleUserRef extends Model
{
    //
    protected $table = 'role_user_ref';
    protected $fillable = ['user_id','role_id','type'];

    public $timestamps = false;
///////////////关联////////////////////////////////////

    public function user()
    {
        return $this->hasMany(User::class,'id','user_id');
    }

}
