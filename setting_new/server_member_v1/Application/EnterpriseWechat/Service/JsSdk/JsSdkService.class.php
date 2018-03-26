<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 07/02/2018
 * Time: 11:28
 */
namespace EnterpriseWechat\Service\JsSdk;

use Common\Service\PublicService;
use Common\Service\RedisService;
use EnterpriseWechat\Service\EnterpriseWechatCommonService;
use EnterpriseWechat\Service\Message\EventMessageService;
use EnterpriseWechat\Service\Open\AppInfoService;
use Org\Util\Stringnew;
use Think\Cache\Driver\Redis;

class JsSdkService
{
    /**
     * 调用接口获取ticket
     * @param $keyAdmin
     * @param $appName
     * @param $corId
     * @return array
     */
    private static function setJsSdkTicket($keyAdmin,$appName, $corpId, $suiteId)
    {
        $corpAccessToken = EnterpriseWechatCommonService::getCorpAccessToken($keyAdmin, $appName);
        $url = str_replace('[ACCESS_TOKEN]', $corpAccessToken, EnterpriseWechatCommonService::$getJsSdkTicket);
        $request = curl_https($url, [], [], 10, false, 'GET');
        $responseData = json_decode($request, true);
        if (is_array($responseData) && isset($responseData['errcode'])){
            if ($responseData['errcode'] == 0){
                RedisService::connectredis()->set('enterprise:wechat:jssdkticket:'. $corpId . ':' . $suiteId, $responseData['ticket'], ['ex'=>$responseData['expires_in'] - 200]);
                return $responseData['ticket'];
            }else{
                return ['code'=>104, 'data'=>$responseData];
            }

        }else{
            return ['code'=>101];
        }
    }


    /**
     * 从本地获取ticket
     * @param $keyAdmin
     * @param $appName
     * @param $corpId
     * @return array|bool|string
     */
    public static function getJsSdkTicket($keyAdmin, $appName, $corpId, $suiteId)
    {
        $token = RedisService::connectredis()->get('enterprisewechat:corp:accesstoken:'. $corpId . ':' . $suiteId);
        if (false == $token){
            return self::setJsSdkTicket($keyAdmin, $appName, $corpId, $suiteId);
        }
        return $token;
    }


    public static function jsSdkSign($keyAdmin, $appName, $url)
    {

        //商户信息
        $adminInfo = PublicService::getMerchant($keyAdmin);
        if (isset($adminInfo['code'])){
            return $adminInfo;
        }

        $authInfo = AppInfoService::getRelationShip($adminInfo, $appName);
        if (!is_array($authInfo) || $authInfo['data']['auth_id'] == false){
            return $authInfo;
        }

        $appInfo = EnterpriseWechatCommonService::getAppInfoByName($appName);
        if (!$appInfo){
            return ['code'=>4012];
        }

        //用第三方服务商的suiteid和客户的id，获取授权表的信息
        $authAppInfo = EnterpriseWechatCommonService::getAuthAppInfo($appInfo['suiteid'], $authInfo['data']['auth_id']);
        if (!$authAppInfo){
            return ['code'=>4012, 'data'=>['y'=>1]];
        }

        $jsSdkTicket = self::getJsSdkTicket($keyAdmin, $appName, $authAppInfo['corpid'], $authAppInfo['suiteid']);
        if (isset($jsSdkTicket['code'])) {
            return $jsSdkTicket;
        }

        $timestamp = time();
        $string=new Stringnew();
        $noncestr=$string->randString(20,3);
        $str='jsapi_ticket='.$jsSdkTicket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.urldecode($url);

        $re=array(
            'appId'=>$authAppInfo['corpid'],
            'timeStamp'=>$timestamp,
            'nonceStr'=>$noncestr,
            'signature'=>sha1($str),
            'url'=>urldecode($url),
        );
        return ['code'=>200, 'data'=> $re];


    }




}