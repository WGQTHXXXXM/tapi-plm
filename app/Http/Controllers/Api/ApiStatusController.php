<?php
namespace App\Http\Controllers\Api;

use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ApiStatusController extends ApiController
{
    public function status(Request $request)
    {
        shell_exec('chmod 777 /data/www/storage/logs/laravel-2019-11-09.log');
        try {
            $pdo = DB::connection()->getPdo();
        } catch (\Exception $e) {
            throw new \Exception('数据库不可用');
        }

        try {
            $redis = Redis::connection();
            $redis->client()->connect();
            $redis->client()->disconnect();
        } catch (\Exception $e) {
            throw new \Exception('Redis不可用');
        }

        $this->checkDisk();

        return $this->response->array(['status' => 'UP']);
    }

    protected function checkDisk()
    {
        $file = storage_path('api-status.tmp');
        $ret = file_put_contents($file, date('Y-m-d H:i:s'));
        if (false === $ret) {
            throw new \Exception('写磁盘失败');
        }
        //unlink($file);
    }

}

