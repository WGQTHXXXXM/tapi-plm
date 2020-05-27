<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$items = [
			[
				'user_id' => '76491176d8de4df9813141694442abe2',
				'name' => '李国栋',
				'phone' => '15210536909',
				'email' => 'liguodong@singulato.com',
				'status' => 'normal'
			]
		];

		foreach ($items as $item) {
			try {
				$user = new User();
				$user->fill($item)->save();
			} catch (\Exception $e) {
				throw new Exception($e->getMessage());
			}
		}
    }
}
