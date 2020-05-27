<?php
namespace App\Models;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Models\Abstracts\Model;

class OldUser extends Model
{
	const USER_NORMAL  = 'normal';//正常
	const USER_INVALID = 'invalid';//禁用
    protected $table = 'old_users';
    protected $fillable = [
		'name', 'user_id', 'phone', 'email', 'status',
    ];

	public $fields_where = ['id', 'user_id', 'phone', 'created_by', 'updated_by'];
	public $fields_where_in = [//参数格式：status_in
		//'status_in' => 'status'
	];
	public $fields_where_like = ['name', 'email'];
	public $fields_where_between = ['created_at', 'updated_at'];
	public $fields_or_where = [//参数格式：status_or
		//'status_or' => 'status',
	];
	//关联表查询字段
	public $fields_where_ref = [//参数格式：with-groups-name
		//'groups' => ['name', 'description'],
	];
	public $fields_where_in_ref = [//参数格式：with-groups-id-in
		//'groups' => ['id', 'status'],
		//'test' => ['id'],//测试数据，支持多表
	];
	public $fields_where_like_ref = [//参数格式：with-groups-name-like
		//'groups' => ['name', 'description'],
	];
	public $fields_where_between_ref = [//参数格式：with-groups-created_at_start,with-groups-created_at_end
		//'groups' => ['created_at', 'updated_at']
	];
	public $fields_or_where_ref = [//参数格式：with-groups-id-or
		//'groups' => ['id', 'name'],
	];

	///关联新表
    public function dingUser()
    {
        return $this->hasOne(User::class, 'phone', 'phone');
    }

}
