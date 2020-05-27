<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
			$table->string('id', 64)->primary()->comment('文档ID');
			$table->string('doc_name', 128)->comment('文档名称');
			$table->string('mime_type', 32)->comment('文档文件类型');
			$table->string('last_version', 32)->comment('文档最新版本号');
			$table->string('status', 32)->default('normal')->comment('状态：正常: normal; 禁止: invalid');
			$table->string('created_by', 64)->nullable()->comment('创建人');
			$table->string('updated_by', 64)->nullable()->comment('最后更新人');
			$table->dateTime('created_at')->comment('创建时间');
			$table->dateTime('updated_at')->comment('最后更新时间')->useCurrent();

			$table->index(['mime_type']);
			$table->index(['last_version']);
        });

		DB::statement('ALTER TABLE `documents` COMMENT "文档表"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
