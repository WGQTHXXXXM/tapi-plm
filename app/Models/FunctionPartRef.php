<?php
namespace App\Models;

use Carbon\Carbon;
use App\Models\Abstracts\Model;

class FunctionPartRef extends Model
{
	const FUNCTION_PART_NORMAL  = 'normal';//正常
	const FUNCTION_PART_INVALID = 'invalid';//禁用

    protected $table = 'function_parts_ref';

    protected $fillable = [
        'function_id', 'part_id'
    ];
	protected $guarded = ['created_at'];
	protected $hidden = [];
	protected $primaryKey   = 'id';
	protected $keyType      = 'string';
	public    $incrementing = false;

	public $fields_where = ['id', 'function_id', 'part_id', 'created_by', 'updated_by'];
	public $fields_where_in = [//参数格式：status_in
		'function_id_in' => 'function_id'
	];
	public $fields_where_like = [];
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

    /*
    |----------------------------------------
    | 对象自身的方法
    |----------------------------------------
    */
	/**
	 * 创建者与用户关系
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function created_name()
	{
		return $this->hasOne(User::class, 'id', 'created_by');
	}

}
