<?php

namespace App\Models;

use App\Exceptions\RestApiException;
use App\Models\Abstracts\RestApiModelWithConsul;

class Email extends RestApiModelWithConsul
{

    protected static $apiTimeout = 8;

    ////////服务//////////
    protected static $service_name = 'email-platform-service';


    public static $apiMap = [
        'sendEmailByContent' => ['method' => 'POST', 'path' => 'email/content/addition/pass'],
    ];


    /*
     * 权限校验
     * EmailContentQuery {
            addressee (string, optional): 收件人邮箱(多个使用逗号拼接) ,
            attachment (string, optional): 附件路径(多个使用逗号拼接) ,
            ccAddress (string, optional): 抄送人邮箱(多个使用逗号拼接) ,
            content (string, optional): 邮件内容 ,
            ruleIds (string, optional): 发送规则ID(多个规则使用逗号分隔) ,
            sourceId (string, optional): 发件人(邮件源)ID ,
            subject (string, optional): 邮件标题
          }
     */
    protected static function sendEmail($params)
    {

        try {
            $response = self::getData('sendEmailByContent',[], $params);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }


    /**
     * 审批通知
     * @param array $address  邮件地址数组
     * @param string $algorithmName    审批主题
     * @param string $initiatorName   发起人姓名
     * @return Email|null
     */
    public static function approvalApproval(Array $address,string $algorithmName, string $initiatorName)
    {

        $data = [
            'addressee' => implode(',',$address),
            'ruleIds' => '2c92369262d78b700162d78df2f80001',            //TODO 邮件设计缺陷
            'sourceId' => '2c92369262d78b700162d78b70760000',           //TODO 邮件设计缺陷
        ];
        $data['subject'] = $initiatorName . "发起" . $algorithmName . "请关注";
        $data['content'] = "您好！由 ".$initiatorName."发起的 《".$algorithmName."》，需您登陆系统进行审批，请通过PLM系统进行查看！\n登录地址如下：https://app.singulato.com/front-plm-admin\n此信息为PLM系统发出！无需回复！";
        $response = self::sendEmail($data);
        return $response;
    }

    public static function approvalFollow(Array $address,string $algorithmName, string $initiatorName)
    {

        $data = [
            'addressee' => implode(',',$address),
            'ruleIds' => '2c92369262d78b700162d78df2f80001',            //TODO 邮件设计缺陷
            'sourceId' => '2c92369262d78b700162d78b70760000',           //TODO 邮件设计缺陷
        ];
        $data['subject'] = $initiatorName . "发起" . $algorithmName . "请关注";
        $data['content'] = "您好！由 ".$initiatorName."发起的 《".$algorithmName."》，需您登陆系统进行关注，请通过PLM系统了解进展！\n登录地址如下：https://app.singulato.com/front-plm-admin\n此信息为PLM系统发出！无需回复！";
        $response = self::sendEmail($data);
        return $response;
    }


}
