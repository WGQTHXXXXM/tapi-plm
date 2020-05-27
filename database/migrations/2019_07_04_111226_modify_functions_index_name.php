<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyFunctionsIndexName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('functions', function (Blueprint $table) {
            $table->dropUnique('functions_name_unique');
            $table->unique(['name', 'parent_func_id']);
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
			$table->dropUnique(['name', 'parent_func_id']);
			$table->unique('name');
        });
    }
}
