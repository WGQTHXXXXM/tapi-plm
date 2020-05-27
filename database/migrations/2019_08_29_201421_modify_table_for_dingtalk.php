<?php
/*
 * 因为把钉钉组织架构和员工放到系统里：
 * 1.把以前的users表变成old_user;
 * 2.新建一个users
 * 3-4.新建一个department和dpt_ref
 * 5.更新functions表的负责人.把负责人改成新表的ID
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\OldUser;
use App\Models\FunctionObj;

class ModifyTableForDingtalk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            //1.把以前的users表变成old_user;
            Schema::rename('users', 'old_users');
            //2.新建一个users
            Schema::create('users', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->string('user_id', 64)->nullable()->comment('用户中心的用户ID');
                $table->string('ding_userid', 64)->comment('钉钉用户企业ID');
                $table->string('ding_unionid', 64)->comment('钉钉的用户ID');
                $table->string('name', 128)->nullable()->comment('用户名称');
                $table->string('phone', 64)->nullable()->comment('手机号码');
                $table->string('email', 128)->nullable()->comment('电子邮箱地址');
                $table->string('status', 32)->default('normal')->comment('状态：正常: normal; 禁止: invalid');
                $table->string('created_by', 64)->nullable()->comment('创建人');
                $table->string('updated_by', 64)->nullable()->comment('最后更新人');
                $table->dateTime('created_at')->comment('创建时间');
                $table->dateTime('updated_at')->comment('最后更新时间')->useCurrent();
                $table->index(['phone']);
                $table->unique(['ding_userid']);
                $table->unique(['ding_unionid']);
            });
            DB::statement('ALTER TABLE `users` COMMENT "PLM用户表"');

            //3.新建一个departments部门
            Schema::create('departments', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->string('name', 128)->comment('用户名称');
                $table->integer('ding_id')->comment('钉钉部门ID');
                $table->integer('ding_pid')->comment('钉钉部门的父ID');
                $table->dateTime('created_at')->comment('创建时间');
                $table->dateTime('updated_at')->comment('最后更新时间')->useCurrent();

                $table->unique(['ding_id']);
                $table->index(['ding_pid']);
            });
            DB::statement('ALTER TABLE `departments` COMMENT "部门表"');

            //4.新建一个department_user_ref部门用户关联表
            Schema::create('department_user_ref', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->string('department_id', 64)->comment('部门ID');
                $table->string('user_id', 64)->comment('用户ID');
                $table->dateTime('created_at')->comment('创建时间');
                $table->dateTime('updated_at')->comment('最后更新时间')->useCurrent();
                $table->index(['department_id']);
                $table->index(['user_id']);
                $table->unique(['department_id', 'user_id']);
            });
            DB::statement('ALTER TABLE `department_user_ref` COMMENT "部门用户关联表"');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function () {
            $mdl = new FunctionObj();
            $ownerClass = $mdl->select('owner_id')->groupBy('owner_id')->pluck('owner_id');
            foreach ($ownerClass as $ownerId){
                $phone = User::find($ownerId)->phone;
                $newOwnerId = OldUser::where(['phone'=>$phone])->first()->id;
                FunctionObj::where(['owner_id'=>$ownerId])->update(['owner_id'=>$newOwnerId]);
            }



            Schema::dropIfExists('department_user_ref');
            Schema::dropIfExists('departments');
            Schema::dropIfExists('users');

            Schema::rename('old_users', 'users');
        });
    }


}
