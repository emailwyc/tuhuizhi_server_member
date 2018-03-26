<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 13/03/2018
 * Time: 15:20
 */

namespace EnterpriseWechat\Service\Users;


use EnterpriseWechat\Service\EnterpriseWechatCommonService;
use EnterpriseWechat\Service\Message\EventMessageService;
use EnterpriseWechat\Service\Open\AppInfoService;

class getUserInfoService
{

    public static function getUserInfo($appName, $adminInfo, $userId)
    {
        $authInfo = AppInfoService::getRelationShip($adminInfo, $appName);
        if (!is_array($authInfo) || $authInfo['data']['auth_id'] == false){
            return $authInfo;
        }

        //用APPname获取第三方服务商的应用信息
        $appInfo = EnterpriseWechatCommonService::getAppInfoByName($appName);
        if (!$appInfo){
            return ['code'=>4012];
        }

        //用第三方服务商的suiteid和客户的id，获取授权表的信息
        $authAppInfo = EnterpriseWechatCommonService::getAuthAppInfo($appInfo['suiteid'], $authInfo['data']['auth_id']);
        if (!$authAppInfo){
            return ['code'=>4012, 'data'=>['y'=>1]];
        }

        $corpAccessToken = EventMessageService::getCorpAccessToken($authAppInfo['corpid'], $authAppInfo['suiteid']);
        if (!$corpAccessToken){
            return ['code'=>4020];
        }



        $url = str_replace('[ACCESS_TOKEN]', $corpAccessToken, EnterpriseWechatCommonService::$getUserInfo);
        $url = str_replace('[USERID]', $userId, $url);
        $request = curl_https($url, [], [], 10, false, 'GET');
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




