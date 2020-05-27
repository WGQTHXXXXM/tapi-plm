<?php

namespace App\Models;

use App\Models\Abstracts\RestApiModelWithConsul;

/**
 * 任务流
 * 文档地址： http://doc.api-console.singulato.com/project/274/interface/api/cat_1765
 */
class TaskFlow extends RestApiModelWithConsul
{
    protected static $service_name = 'tapi-task-flow';

    public static $apiMap = [
        'getInstanceRecords' => ['method' => 'GET', 'path' => 'instances/:id/records'],
        'templates'  => ['method' => 'GET', 'path' => 'templates'],
        'createInstance' => ['method' => 'POST', 'path' => 'instances'],
        'removeInstance' => ['method' => 'DELETE', 'path' => 'instances/:id'],
        'launchInstance' => ['method' => 'PUT', 'path' => 'instances/:id'],
        'setTaskSelect' => ['method' => 'POST', 'path' => 'tasks/:id/decision'],
        'getInstanceInIds' => ['method' => 'POST', 'path' => 'instances/in_ids'],
        'getInstance' => ['method' => 'GET', 'path' => 'instances/:id'],
        'getTask' => ['method' => 'GET', 'path' => 'tasks/:id'],
        'participantsByTaskId' => ['method' => 'GET', 'path' => '/api/tasks/:id/participants'],
        'addParticipant' => ['method' => 'POST', 'path' => '/api/tasks/:id/participant'],
        'removeParticipant' => ['method' => 'DELETE', 'path' => '/api/tasks/participants/:id'],
        'getCurtask'=>['method' => 'GET', 'path' => 'instances/:id/curtask'],//获得实例正在进行的任务

    ];

    //获得实例正在进行的任务
    public static function getCurtask($id)
    {
        $response = self::getData('getCurtask', [':id'=> $id]);
        return $response;
    }

    // 获取任务详情
    public static function getTask($taskId)
    {

        $response = self::getData('getTask', [
            ':id'=> $taskId
        ]);
        return $response;
    }

    // 获取指定实例
    public static function participantsByTaskId($taskId)
    {
        $response = self::getData('participantsByTaskId', [
            ':id'=> $taskId
        ]);
        return $response;
    }

    // 添加参与者
    public static function addParticipant($taskId,$data)
    {
        $response = self::getData('addParticipant', [
            ':id'=> $taskId
        ],$data);
        return $response;
    }

    // 删除参与者
    public static function removeParticipant($id)
    {
        $response = self::getData('removeParticipant', [
            ':id'=> $id,
        ]);
        return $response;
    }

    // 获取指定实例
    public static function getInstanceInIds(Array $ids)
    {
        $response = self::getCollection('getInstanceInIds', [], [
            'ids'=> $ids
        ]);
        return $response;
    }


    // 获取指定实例
    public static function getInstance($id)
    {
        $response = self::getData('getInstance', [
            ':id' => $id
        ]);
        return $response;
    }

    // 删除指定实例
    public static function removeInstance($id)
    {
        $response = self::getData('removeInstance', [
            ':id' => $id
        ]);
        return $response;
    }

    // 任务结果确认
    public static function setTaskSelect($taskId,$data){
        $response = self::getData('setTaskSelect', [
            ':id' => $taskId
        ], $data);
        return $response;
    }

    // 启动实例
    public static function launchInstance($instanceId,$data){
        $response = self::getData('launchInstance', [
            ':id' => $instanceId
        ], $data);

        return $response;
    }


    // 获取实例日志
    public static function getInstanceRecords($instanceId)
    {
        $response = self::getCollection('getInstanceRecords', [
            ':id' => $instanceId
        ]);
        return $response;
    }

    // 创建实例
    public static function createInstance($data)
    {
        $response = self::getData('createInstance', [], $data);
        return $response;
    }


    /**
     * 所有任务流模板
     * 
     */
    public static function templates()
    {
       $data = self::getCollection('templates');
       return $data;
    }

}
