<?php

namespace App\Models;

use App\Models\Abstracts\Model;

class FunctionDocumentRef extends Model
{
    //
    protected $table = 'function_document_ref';

    protected $fillable = [
        'function_id','status','type','name','version','download_path','created_at'
    ];


    /**
     * 执行人与用户关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function createBy()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }


}
