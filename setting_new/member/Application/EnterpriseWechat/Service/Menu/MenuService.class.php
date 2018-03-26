<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 28/12/2017
 * Time: 11:20
 */

namespace EnterpriseWechat\Service\Menu;


use EnterpriseWechat\Service\EnterpriseWechatCommonService;
use EnterpriseWechat\Service\Message\EventMessageService;

class MenuService
{
    public static function setMenu($appName, $id, $content)
    {
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

        $url = str_replace('[ACCESS_TOKEN]', $corpAccessToken, EnterpriseWechatCommonService::$setMenu);
        $url = str_replace('[AGENTID]', $authAppInfo['agentid'], $url);
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