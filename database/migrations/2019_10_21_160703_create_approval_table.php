<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('flow_instance_id')->comment('流实例ID');
            $table->integer('level')->nullable()->comment('优先级');
            $table->bigInteger('plan_completed_time')->nullable()->comment('计划完成日期');
            $table->bigInteger('last_limited_time')->nullable()->comment('最后限定时间');
            $table->uuid('owner_id')->nullable()->comment('责任人');
            $table->string('owner_name')->nullable()->comment('责任人');
            $table->uuid('project_id')->nullable()->comment('关联项目');
            $table->string('project_name')->nullable()->comment('关联项目');
            $table->uuid('sqer_id')->nullable()->comment('SQE');
            $table->string('sqer_name')->nullable()->comment('SQE');
            $table->uuid('purchaser_id')->nullable()->comment('采购负责人');
            $table->string('purchaser_name')->nullable()->comment('采购负责人');
            $table->string('supplier_name', 64)->nullable()->comment('供应商');
            $table->boolean('is_elec')->nullable()->comment('是否带电');
            $table->integer('wiki_page_id')->comment('wiki页面ID');

            $table->string('created_by', 64)->comment('发起人');
            $table->bigInteger('created_at')->comment('发起日期')->nullable();
            $table->bigInteger('updated_at')->comment('最后更新时间，更新日期')->nullable();
			$table->string('updated_by', 64)->comment('最后更新人');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approvals');
    }
}
