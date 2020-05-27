<?php

namespace App\Models;


class ApprovalFile extends BaseModel
{
    protected $fillable = ['approval_id','file_id', 'file_name','wiki_url','wiki_dl_path'];
}
