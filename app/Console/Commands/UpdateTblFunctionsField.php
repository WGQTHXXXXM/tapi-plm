<?php

/**
 * 原因：先开发的功能，后来说要把项目加进来。
 *
 * 所以上线后，新建一个项目，然后得到项目号，用命令把项目号更新功能的id
 */

namespace App\Console\Commands;

use App\Models\FunctionObj;
use Illuminate\Console\Command;

class UpdateTblFunctionsField extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'functionsObj:updateProjectId {proId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        try{
            FunctionObj::where([])->update(['project_id'=>$this->argument('proId')]);
        }catch (\Exception $e){
            throw new \Exception('更新失败:'.$e->getMessage());
        }
    }
}
