<?php

/*
 * key_func_desc增加到1024长度
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MdfKeyfuncdesclenFunctionTable extends Migration
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
            $table->string('key_func_desc', 1024)->nullable()->comment('关键功能描述')->change();
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
            $table->string('key_func_desc', 256)->nullable()->comment('关键功能描述')->change();
        });

    }
}
