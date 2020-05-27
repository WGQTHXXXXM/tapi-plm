<?php

/**
 * 应产品要求functions表加减字段
 */
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyFunctionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('functions', function (Blueprint $table) {
            //删掉的字段
            $table->dropColumn('softhardware_version_no');
            $table->dropColumn('ots_time_funcdev');
            $table->dropColumn('ots_time_funccalib');
            $table->dropColumn('ots_time_procvalid');
            //加上的字段
            $table->string('hardware_version_no', 64)->nullable()->comment('硬件版本号')->after('signal_matrix_version_no');
            $table->string('software_version_no', 64)->nullable()->comment('软件版本号')->after('signal_matrix_version_no');
            $table->string('calibration_version_no', 64)->nullable()->comment('标定版本号')->after('signal_matrix_version_no');
            $table->string('configuration_version_no', 64)->nullable()->comment('配置表版本');
            $table->dateTime('completion_time')->nullable()->comment('完成时间');
            $table->string('project_valve_point', 64)->nullable()->comment('项目进度（过程阀点）');
            $table->dateTime('milestone_time')->nullable()->comment('里程碑节点');
            $table->string('func_status', 64)->nullable()->comment('功能状态（完成阀点');
            $table->string('calibration_status', 64)->nullable()->comment('标定状态（完成阀点）');
        });

        //
        Schema::table('function_del', function (Blueprint $table) {
            //删掉的字段
            $table->dropColumn('softhardware_version_no');
            $table->dropColumn('ots_time_funcdev');
            $table->dropColumn('ots_time_funccalib');
            $table->dropColumn('ots_time_procvalid');
            //加上的字段
            $table->string('hardware_version_no', 64)->nullable()->comment('硬件版本号')->after('signal_matrix_version_no');
            $table->string('software_version_no', 64)->nullable()->comment('软件版本号')->after('signal_matrix_version_no');
            $table->string('calibration_version_no', 64)->nullable()->comment('标定版本号')->after('signal_matrix_version_no');
            $table->string('configuration_version_no', 64)->nullable()->comment('配置表版本');
            $table->dateTime('completion_time')->nullable()->comment('完成时间');
            $table->string('project_valve_point', 64)->nullable()->comment('项目进度（过程阀点）');
            $table->dateTime('milestone_time')->nullable()->comment('里程碑节点');
            $table->string('func_status', 64)->nullable()->comment('功能状态（完成阀点');
            $table->string('calibration_status', 64)->nullable()->comment('标定状态（完成阀点）');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('functions', function (Blueprint $table) {
            $table->string('softhardware_version_no', 64)->nullable()->comment('软硬件版本号');
            $table->dateTime('ots_time_funcdev')->nullable()->comment('OTS时间阀点-功能开发（完成阀点）');
            $table->dateTime('ots_time_procvalid')->nullable()->comment('OTS时间阀点-工艺验证（完成阀点）');
            $table->dateTime('ots_time_funccalib')->nullable()->comment('OTS时间阀点-功能定标（完成阀点）');

            $table->dropColumn('hardware_version_no');
            $table->dropColumn('software_version_no');
            $table->dropColumn('calibration_version_no');
            $table->dropColumn('configuration_version_no');
            $table->dropColumn('completion_time');
            $table->dropColumn('project_valve_point');
            $table->dropColumn('milestone_time');
            $table->dropColumn('func_status');
            $table->dropColumn('calibration_status');
        });

        Schema::table('function_del', function (Blueprint $table) {
            $table->string('softhardware_version_no', 64)->nullable()->comment('软硬件版本号');
            $table->dateTime('ots_time_funcdev')->nullable()->comment('OTS时间阀点-功能开发（完成阀点）');
            $table->dateTime('ots_time_procvalid')->nullable()->comment('OTS时间阀点-工艺验证（完成阀点）');
            $table->dateTime('ots_time_funccalib')->nullable()->comment('OTS时间阀点-功能定标（完成阀点）');

            $table->dropColumn('hardware_version_no');
            $table->dropColumn('software_version_no');
            $table->dropColumn('calibration_version_no');
            $table->dropColumn('configuration_version_no');
            $table->dropColumn('completion_time');
            $table->dropColumn('project_valve_point');
            $table->dropColumn('milestone_time');
            $table->dropColumn('func_status');
            $table->dropColumn('calibration_status');
        });

    }
}
