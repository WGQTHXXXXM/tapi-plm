<?php
namespace App\Models;

use Carbon\Carbon;
use App\Models\Abstracts\Model;

class FunctionObj extends Model
{
    /*
     * 字段 func_level 的枚举值定义
     */
    const LEVEL_ONE   = 1;   // 1级功能
    const LEVEL_TWO   = 2;   // 2级功能
    const LEVEL_THREE = 3;   // 3级功能

	const FUNCTION_NORMAL  = 'normal';//正常
	const FUNCTION_INVALID = 'invalid';//禁用

    protected $table = 'functions';

    protected $fillable = [
        'name', 'func_level', 'parent_func_id', 'owner_id', 'key_func_desc','project_id',
        'lead_ecu', 'belong_to_system', 'power_ecu', 'domain_ecu', 'chassis_ecu', 'adas_ecu', 'instrumentpanel_ecu',
        'decoration_ecu',  'signal_matrix_version_no','completion_time', 'milestone_time','hardware_version_no',
        'software_version_no','calibration_version_no','configuration_version_no','project_valve_point',
        'func_status','calibration_status',
    ];
	protected $guarded = ['created_at'];
	protected $hidden = [];
	protected $primaryKey   = 'id';
	protected $keyType      = 'string';
	public    $incrementing = false;

    protected $dates = ['completion_time', 'milestone_time'];

	public $fields_where = ['id', 'func_level', 'owner_id', 'parent_func_id', 'created_by', 'updated_by','project_id'];
	public $fields_where_in = [//参数格式：status_in
		//'status_in' => 'status'
	];
	public $fields_where_like = ['name', 'key_func_desc', 'lead_ecu', 'belong_to_system', 'power_ecu', 'domain_ecu', 'chassis_ecu', 'adas_ecu', 'instrumentpanel_ecu',
		'decoration_ecu',  'signal_matrix_version_no','hardware_version_no','software_version_no','calibration_version_no',
        'configuration_version_no','project_valve_point','func_status','calibration_status'];
	public $fields_where_between = ['created_at', 'updated_at', 'completion_time', 'milestone_time'];
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

	/**
	 * 责任人与用户关系
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function owner_name()
	{
		return $this->hasOne(User::class, 'id', 'owner_id');
	}


    /*
    |----------------------------------------
    | 数据存取方法
    |----------------------------------------
    */

    public static function paginateList($perPage, $wheres = [], $orders = ['created_at', 'desc'])
    {
        $query = static::query();
        foreach ($wheres as $name => $value) {
            $query->where($name, $value);
        }
        $query->orderBy($orders[0], $orders[1]);
        return $query->paginate($perPage);
    }

}
