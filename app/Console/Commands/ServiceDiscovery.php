<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\ServiceFactory\Consul;
use Illuminate\Support\Facades\Log;

class ServiceDiscovery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:discovery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Implement the service registration function';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ip = env('HOST_IP')?:gethostbyname(gethostname());


        $body = [
            "id" => env('APP_CONSUL_NAME').'-'.gethostname(),
            "name" => env('APP_CONSUL_NAME'),
            "address"=> $ip,
            "port" => 80,
            "tags" => ["version=v2","author=weijinlong","secure=false","contextPath=/api"],
            "checks" => [
                [
                    "http" => 'http://'.$ip.'/health',
                    "interval" => "5s"
                ]
            ]
        ];

        $result = resolve(Consul::class)->registerService($body);
        if ($result != 200) {
            echo "服务注册失败！  \n";
        }else{
            echo "服务注册成功！ \n";
        }
    }
}
