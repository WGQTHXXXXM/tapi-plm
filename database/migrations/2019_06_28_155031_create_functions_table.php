<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFunctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('functions', function (Blueprint $table) {
			$table->string('id', 64)->primary()->comment('功能ID');
			$table->string('name', 128)->comment('功能名称');
			$table->tinyInteger('func_level')->default(1)->comment('功能级别');
			$table->string('parent_func_id', 64)->nullable()->comment('上级功能ID');
			$table->string('owner_id', 64)->nullable()->comment('负责人');
			$table->string('key_func_desc', 256)->nullable()->comment('关键功能描述');
			$table->string('lead_ecu', 64)->nullable()->comment('牵头控制器');
			$table->string('belong_to_system', 64)->nullable()->comment('责任专业');
			$table->string('power_ecu', 64)->nullable()->comment('动力系统控制器');
			$table->string('domain_ecu', 64)->nullable()->comment('域控制器');
			$table->string('chassis_ecu', 64)->nullable()->comment('底盘控制器');
			$table->string('adas_ecu', 64)->nullable()->comment('ADAS控制器');
			$table->string('instrumentpanel_ecu', 64)->nullable()->comment('仪表显示控制器');
			$table->string('decoration_ecu', 64)->nullable()->comment('内外饰控制器');
			$table->string('softhardware_version_no', 64)->nullable()->comment('软硬件版本号');
			$table->string('signal_matrix_version_no', 64)->nullable()->comment('信号矩阵版本');
			$table->dateTime('ots_time_funcdev')->nullable()->comment('OTS时间阀点-功能开发（完成阀点）');
			$table->dateTime('ots_time_procvalid')->nullable()->comment('OTS时间阀点-工艺验证（完成阀点）');
			$table->dateTime('ots_time_funccalib')->nullable()->comment('OTS时间阀点-功能定标（完成阀点）');
			$table->string('status', 32)->default('normal')->comment('状态：正常: normal; 禁止: invalid');
			$table->string('created_by', 64)->nullable()->comment('创建人');
			$table->string('updated_by', 64)->nullable()->comment('最后更新人');
			$table->dateTime('created_at')->comment('创建时间');
			$table->dateTime('updated_at')->comment('最后更新时间')->useCurrent();

			$table->unique('name');
			$table->index(['func_level']);
			$table->index(['owner_id']);
        });

		DB::statement('ALTER TABLE `functions` COMMENT "功能表"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('functions');
    }
}
