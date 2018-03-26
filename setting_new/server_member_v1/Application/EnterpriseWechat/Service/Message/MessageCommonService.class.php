<?php
/**
 * 传入解密以后的xml数据，从此类中分发消息的处理逻辑
 * Created by PhpStorm.
 * User: zhang
 * Date: 08/12/2017
 * Time: 17:46
 */

namespace EnterpriseWechat\Service\Message;


use EnterpriseWechat\Service\EnterpriseWechatCommonService;

class MessageCommonService
{

    public static function callbackMessageHandle($messageData, $appInfo)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($messageData);
        $infoType = $dom->getElementsByTagName('InfoType')->item(0)->nodeValue;  //发送的消息内容体


        if ($infoType != false){
            /**
             * 授权成功通知
             * 从企业微信第三方官网发起授权时，企业微信服务器会推送授权成功通知；从第三方服务商网站发起的应用授权流程，由于授权完成时会跳转第三方服务商管理后台，企业微信服务器不会向第三方服务商推送授权成功通知。
             */
            if ($infoType == 'create_auth'){
                $suiteId = $dom->getElementsByTagName('SuiteId')->item(0)->nodeValue;;
                $authCode = $dom->getElementsByTagName('AuthCode')->item(0)->nodeValue;;
                $timeStamp = $dom->getElementsByTagName('TimeStamp')->item(0)->nodeValue;;
                $data = EventMessageService::createAuth($suiteId, $authCode, $infoType, $timeStamp);
                return $data;
            }
            /**
             * 推送suite_ticket
             * 企业微信服务器会定时（每十分钟）推送ticket。ticket会实时变更，并用于后续接口的调用。
             */
            if ($infoType == 'suite_ticket'){
                if (false == $appInfo) {
                    return false;
                }
                $suiteId = $dom->getElementsByTagName('SuiteId')->item(0)->nodeValue;;
                $SuiteTicket = $dom->getElementsByTagName('SuiteTicket')->item(0)->nodeValue;;
                $TimeStamp = $dom->getElementsByTagName('TimeStamp')->item(0)->nodeValue;;
                $data = EventMessageService::suiteTicket($suiteId, $SuiteTicket, $TimeStamp, $appInfo);


            }
            /**
             * 变更授权通知
             * 当授权方（即授权企业）在企业微信管理端的授权管理中，修改了对应用的授权后，企业微信服务器推送变更授权通知。
             * 开发者接收到变更通知之后，需自行调用’获取企业授权信息’接口进行授权内容变更比对。
             */
            if ($infoType == 'change_auth'){
                return false;
            }

            /**
             * 取消授权通知
             * 当授权方（即授权企业）在企业微信管理端的授权管理中，取消了对应用的授权托管后，企业微信服务器会推送取消授权通知。
             */
            if ($infoType == 'cancel_auth'){
                return false;
            }
            /**
             * 通讯录变更事件通知
             * 当企业微信成员或者部门变更时，企业微信服务器会推送通讯录变更通知到权限范围内的应用的“指令回调URL“。注意：由第三方应用调用接口触发的变更事件不回调给该应用本身。
             */
            if ($infoType == 'change_contact'){
                return false;
            }

            else{
                return false;//暂时没有其他业务需求
            }
        }


    }
}