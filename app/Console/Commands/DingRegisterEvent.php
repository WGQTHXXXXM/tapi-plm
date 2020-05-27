<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use EasyDingTalk\Application;

class DingRegisterEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ding:registerEvent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register dingtalk Event';

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
        $dingObj = app(Application::class);

        $params = [
            'call_back_tag' => ['user_add_org', 'user_modify_org', 'user_leave_org',
                'user_add_org','org_dept_remove','user_modify_org'],
            'url' => url('/api/dingtalk/eventReceive'),
        ];

        $res = $dingObj->callback->register($params);

        dd($res);
    }
}
