<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_versions', function (Blueprint $table) {
			$table->string('id', 64)->primary()->comment('文档版本ID');
			$table->string('document_id', 64)->comment('文档ID');
			$table->string('doc_version', 32)->comment('文档版本号');
			$table->string('doc_url', 1024)->comment('文档URL地址');
			$table->string('status', 32)->default('normal')->nullable()->comment('状态：正常: normal; 禁止: invalid');
			$table->string('created_by', 64)->nullable()->comment('创建人');
			$table->string('updated_by', 64)->nullable()->comment('最后更新人');
			$table->dateTime('created_at')->comment('创建时间');
			$table->dateTime('updated_at')->comment('最后更新时间')->useCurrent();

			$table->index(['document_id']);
			$table->index(['doc_version']);
        });

		DB::statement('ALTER TABLE `document_versions` COMMENT "文档版本表"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_versions');
    }
}
