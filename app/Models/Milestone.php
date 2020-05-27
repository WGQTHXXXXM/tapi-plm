<?php

namespace App\Models;

use App\Models\Abstracts\Model;

class Milestone extends Model
{
    //
    protected $table = 'milestone';
    protected $fillable = ['project_id', 'name', 'delivery_at','target','order','start_at'];
    public $timestamps = false;
///////////////关联////////////////////////////////////

}
