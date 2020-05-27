<?php

/**
 * 因为功能清单的加入，修改以前的表的设计（往wiki上传）。
 */
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MdfFuncDocRefTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('function_document_ref', function (Blueprint $table) {
            $table->dropColumn('document_id');
            $table->dropIndex('function_document_ref_function_id_document_id_index');

            $table->string('type', 64)->comment('清单类型');
            $table->string('name', 64)->comment('文档名称');
            $table->integer('version')->comment('版本');
            $table->string('download_path', 256)->comment('下载路径');

            $table->index(['function_id']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('function_document_ref', function (Blueprint $table) {
            $table->string('document_id', 64)->comment('文档ID');
            $table->index(['function_id', 'document_id']);

            $table->dropIndex('function_document_ref_function_id_index');
            $table->dropColumn('type');
            $table->dropColumn('name');
            $table->dropColumn('version');
            $table->dropColumn('download_path');
        });

    }
}
