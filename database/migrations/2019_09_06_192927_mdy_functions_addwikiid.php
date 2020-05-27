<?php

/*
 * 上传wiki要加wiki页面ID
 */
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MdyFunctionsAddwikiid extends Migration
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
            //加上的字段
            $table->string('wiki_page_id', 64)->nullable()->comment('wiki页面ID号');
            $table->unique('wiki_page_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('functions', function (Blueprint $table) {
            //加上的字段
            $table->dropUnique('functions_wiki_page_id_unique');
            $table->dropColumn('wiki_page_id');
        });


    }
}
