<?php
namespace App\Models;

use Carbon\Carbon;
use App\Models\Abstracts\Model;

class FunctionDel extends Model
{

    protected $table = 'function_del';

    protected $fillable = [
        'name', 'func_level', 'parent_func_id', 'owner_id', 'key_func_desc','project_id',
        'lead_ecu', 'belong_to_system', 'power_ecu', 'domain_ecu', 'chassis_ecu', 'adas_ecu', 'instrumentpanel_ecu',
        'decoration_ecu',  'signal_matrix_version_no','completion_time', 'milestone_time','hardware_version_no',
        'software_version_no','calibration_version_no','configuration_version_no','project_valve_point',
        'func_status','calibration_status','del_at','del_by'
    ];
}
