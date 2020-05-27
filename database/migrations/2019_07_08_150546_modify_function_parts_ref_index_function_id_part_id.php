<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyFunctionPartsRefIndexFunctionIdPartId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('function_parts_ref', function (Blueprint $table) {
			$table->dropIndex('function_parts_ref_function_id_part_id_index');
			$table->unique(['function_id', 'part_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('function_parts_ref', function (Blueprint $table) {
			$table->dropUnique(['function_id', 'part_id']);
			$table->index(['function_id', 'part_id']);
        });
    }
}
