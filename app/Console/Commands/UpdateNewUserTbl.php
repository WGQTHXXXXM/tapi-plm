<?php

namespace App\Console\Commands;

use App\Models\EmployeeCenter;
use App\Models\UserCenter;
use Illuminate\Console\Command;
use EasyDingTalk\Application;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Departments;
use App\Models\DepartmentUserRef;
use App\Models\OldUser;
use App\Models\FunctionObj;
use Illuminate\Support\Facades\Log;



class UpdateNewUserTbl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ding:UpdateNewUserTbl';

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
        DB::transaction(function () {
            $this->getUserByDing();//从钉钉得到员工数据
            $this->updateFuncOwner();//把旧user表的负责人改成新user表（从钉钉取得）的负责人
        });
    }


    /**
     * 从钉钉得到员工数据
     */
    private function getUserByDing()
    {
        try{
            $curTime = date('Y-m-d H:i:s',time());
            $dingObj = app(Application::class);
            //1.批量存部门
            $dpt =  $dingObj->department->get(1);
            $mdlDpt = new Departments();
            $arrdpt[] = ['id'=>$mdlDpt->generateUuid(),'name'=>$dpt['name'],'ding_id'=>$dpt['id'],'ding_pid'=>0
                ,'created_at'=>$curTime,'updated_at'=>$curTime];
            $dpts = $dingObj->department->list(1,true)['department'];
            foreach ($dpts as $dpt){
                $arrdpt[] = ['id'=>$mdlDpt->generateUuid(),'name'=>$dpt['name'],'ding_id'=>$dpt['id'],
                    'ding_pid'=>$dpt['parentid'],'created_at'=>$curTime,'updated_at'=>$curTime];
            }
            DB::table('departments')->insert($arrdpt);
            //存用户和关联部门
            $arrRef=[];$arrUsers =[];$mdlUser=new User(['id'=>'123']);$mdlRef=new DepartmentUserRef();
            foreach ($arrdpt as $dpt){//遍历部门

                for ($i=0;;$i++){//每页20条，下页是否还有更多员工
                    $tmpDptUsers = $dingObj->user->getDetailedUsers($dpt['ding_id'],$i*100,$i*100+100);
                    $tmpUsers = $tmpDptUsers['userlist'];
                    foreach ($tmpUsers as $user){//部门员工保存到数组
                        if (!array_key_exists($user['userid'],$arrUsers)){//之前别的部门有可能已经存过了
                            $center = EmployeeCenter::retrieveByPhone($user['mobile']);
                            if(!!$center->toArray()) {
                                $arrUsers[$user['userid']]=['id'=>$center->guid,'user_id'=>$center->guid,'ding_userid'=>$user['userid'],
                                    'ding_unionid'=>$user['unionid'], 'name'=>$user['name'],'phone'=>$user['mobile'],
                                    'email'=>empty($user['email'])?'':$user['email']/*导入时不添会没有这个字段*/,
                                    'status'=>User::USER_NORMAL, 'created_at'=>$curTime,'updated_at'=>$curTime];
                                $arrRef[] = ['id'=>$mdlRef->generateUuid(),'department_id'=>$dpt['id'],'user_id'=>$center->guid
                                    ,'created_at'=>$curTime,'updated_at'=>$curTime];
                            }
                        } else {
                            $arrRef[$dpt['id'].$arrUsers[$user['userid']]['id']] = [
                                'department_id'=>$dpt['id'],'user_id'=>$arrUsers[$user['userid']]['id'],
                                'created_at'=>$curTime,'updated_at'=>$curTime,'id'=>$mdlRef->generateUuid()];
                        }
                    }
                    if ($tmpDptUsers['hasMore']==false)
                        break;
                }
            }
            DB::table('users')->insert($arrUsers);
            DB::table('department_user_ref')->insert($arrRef);
        } catch (\Exception $e) {

            Log::info('migrate::getUserByDing: '.$e->getMessage().'\n');
            throw new \Exception('migrate::getUserByDing:'.$e->getMessage());
        }
    }

    /*
     * 把旧user表的负责人改成新user表（从钉钉取得）的负责人
     */
    private function updateFuncOwner()
    {
        $mdl = new FunctionObj();
        $ownerClass = $mdl->select('owner_id')->groupBy('owner_id')->pluck('owner_id');
        foreach ($ownerClass as $ownerId){
            $user1 = OldUser::find($ownerId);
            if (empty($user1))
                continue;
            //throw new \Exception('用户表里没有这个用户id'.$ownerId);
            $phone = $user1->phone;
            $user2 = User::where(['phone'=>$phone])->first();
            if (empty($user2))
                continue;
            //    throw new \Exception('钉钉里没有没有这个用户'.$phone);
            $newOwnerId = $user2->id;
            FunctionObj::where(['owner_id'=>$ownerId])->update(['owner_id'=>$newOwnerId]);
        }

    }

}