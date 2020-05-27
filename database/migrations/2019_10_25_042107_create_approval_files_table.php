<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('approval_id')->index();

            $table->uuid('file_id')->comment('文件服务中的id');
            $table->string('file_name', 100)->comment('文件名');
            $table->string('wiki_url', 200)->comment('wiki中的url');
            $table->string('wiki_dl_path', 512)->comment('wiki文件下载地址');


            $table->string('created_by', 64)->comment('创建人');
            $table->bigInteger('created_at')->comment('创建日期')->nullable();
            $table->bigInteger('updated_at')->comment('更新日志')->nullable();
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
        Schema::dropIfExists('approval_files');
    }
}
