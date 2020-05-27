<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFunctionPartsRefTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('function_parts_ref', function (Blueprint $table) {
			$table->string('id', 64)->primary()->comment('文档版本ID');
			$table->string('function_id', 64)->comment('功能ID');
			$table->string('part_id', 64)->comment('零部件ID');
			$table->string('status', 32)->default('normal')->comment('状态：正常: normal; 禁止: invalid');
			$table->string('created_by', 64)->nullable()->comment('创建人');
			$table->string('updated_by', 64)->nullable()->comment('最后更新人');
			$table->dateTime('created_at')->comment('创建时间');
			$table->dateTime('updated_at')->comment('最后更新时间')->useCurrent();

			$table->index(['function_id', 'part_id']);
        });

		DB::statement('ALTER TABLE `function_parts_ref` COMMENT "功能零部件关联表"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('function_parts_ref');
    }
}
