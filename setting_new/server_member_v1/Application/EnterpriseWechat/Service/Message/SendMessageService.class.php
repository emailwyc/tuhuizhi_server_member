<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 19/12/2017
 * Time: 11:57
 */

namespace EnterpriseWechat\Service\Message;


use EnterpriseWechat\Service\EnterpriseWechatCommonService;
use EnterpriseWechat\Service\Open\AppInfoService;

class SendMessageService
{

    public static function sendMessage($appName, $id = false, $content, $adminInfo = false)
    {
        if ($id == false){
            if ($adminInfo != false){
                $authInfo = AppInfoService::getRelationShip($adminInfo, $appName);
                if (!is_array($authInfo) || $authInfo['data']['auth_id'] == false){
                    return $authInfo;
                }
                $id = $authInfo['data']['auth_id'];
            }else{
                return ['code'=>102];
            }

        }



        //用APPname获取第三方服务商的应用信息
        $appInfo = EnterpriseWechatCommonService::getAppInfoByName($appName);
        if (!$appInfo){
            return ['code'=>4012];
        }

        //用第三方服务商的suiteid和客户的id，获取授权表的信息
        $authAppInfo = EnterpriseWechatCommonService::getAuthAppInfo($appInfo['suiteid'], $id);
        if (!$authAppInfo){
            return ['code'=>4012, 'data'=>['y'=>1]];
        }

        $corpAccessToken = EventMessageService::getCorpAccessToken($authAppInfo['corpid'], $authAppInfo['suiteid']);
        if (!$corpAccessToken){
            return ['code'=>4020];
        }

        $content = str_replace('"[agentid]"', $authAppInfo['agentid'], $content);

        $url = str_replace('[ACCESS_TOKEN]', $corpAccessToken, EnterpriseWechatCommonService::$sendMessage);
        $request = curl_https($url, $content, ['Content-Type:application/json;charset=UTF-8'], 10, true, 'POST');
        $responseData = json_decode($request, true);
        if (is_array($responseData) && isset($responseData['errcode'])){
            if ($responseData['errcode'] == 0){
                return ['code'=>200, 'data'=>$responseData];
            }else{
                return ['code'=>104, 'data'=>$responseData];
            }

        }else{
            return ['code'=>101];
        }
    }
}