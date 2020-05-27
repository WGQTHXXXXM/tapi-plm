<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\User;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
			$table->string('id', 64)->primary();
			$table->string('user_id', 64)->comment('用户中心的用户ID');
			$table->string('name', 128)->nullable()->comment('用户名称');
			$table->string('phone', 64)->nullable()->comment('手机号码');
			$table->string('email', 128)->nullable()->comment('电子邮箱地址');
			$table->string('status', 32)->default('normal')->comment('状态：正常: normal; 禁止: invalid');
			$table->string('created_by', 64)->nullable()->comment('创建人');
			$table->string('updated_by', 64)->nullable()->comment('最后更新人');
			$table->dateTime('created_at')->comment('创建时间');
			$table->dateTime('updated_at')->comment('最后更新时间')->useCurrent();

			$table->index(['phone']);
        });

		DB::statement('ALTER TABLE `users` COMMENT "PLM用户表"');

		$env = env('APP_ENV');
		$user_id = '659af41676d345c597316b82887e5a7d';
		$name = '刘新爱';
		$phone = '18210342582';
		$email = 'liuxinai@singulato.com';
		/*if ($env == 'local') {//本地环境
			$user_id = '';//对应环境指定用户信息
			$name = '';//对应环境指定用户信息
			$phone = '';//对应环境指定用户信息
			$email = '';//对应环境指定用户信息
		} else if ($env == 'dev') {//dev开发测试环境

		} else if ($env == 'pre') {//pre测试环境

		} else {//线上生产环境

		}*/
		$item = [
			'user_id' => $user_id,
			'name'    => $name,
			'phone'   => $phone,
			'email'   => $email,
			'status'  => 'normal'
		];
		try {
			$user = new User();
			$user->fill($item)->save();
		} catch (\Exception $e) {
			throw new Exception($e->getMessage());
		}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
