<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 05/12/2017
 * Time: 17:02
 */

namespace EnterpriseWechat\Service;


use Common\Service\PublicService;
use Common\Service\RedisService;
use EnterpriseWechat\Service\Message\EventMessageService;
use EnterpriseWechat\Service\Open\AppInfoService;

class EnterpriseWechatCommonService
{
    public static $redise;

    public static $returnstylee;

    public static $callbacke;

    public static $corpId = 'wx5ff36e7f35988f80';

    public static $getaccessTokenurl = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=[corpid]&corpsecret=[secrect]';

    public static $getlogininfo = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_login_info?access_token=[ACCESS_TOKEN]';

    public static $providertoken = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_provider_token';//获取应用提供商凭证

    public static $providersecret = 'g5FAuBs7MM9EdyiAHLfRCDmRp346_Xpv-9iml1dx4qZ3WJ0bPiy5szDzuuO90mXV';

//    public static $oauthcodeurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=[CORPID]&redirect_uri=[REDIRECT_URI]&response_type=code&scope=[SCOPE]&agentid=[AGENTID]&state=STATE#wechat_redirect';

    public static $oauthUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=[APPID]&redirect_uri=[REDIRECT_URI]&response_type=code&scope=[SCOPE]&state=[STATE]#wechat_redirect';//第三方oauth授权跳转地址

    public static $getUserInfo3rd = 'https://qyapi.weixin.qq.com/cgi-bin/service/getuserinfo3rd?access_token=[SUITE_ACCESS_TOKEN]&code=[CODE]';

    public static $getuserdetail3rd = 'https://qyapi.weixin.qq.com/cgi-bin/service/getuserdetail3rd?access_token=[SUITE_ACCESS_TOKEN]';

    public static $oauthinfo = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=[ACCESS_TOKEN]&code=[CODE]';

    public static $suiteAccessTokenUrl = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token';//获取第三方应用凭证

    public static $preAuthCode = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_pre_auth_code?suite_access_token=[SUITE_ACCESS_TOKEN]';//预授权码

    public static $getPermanentCode = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_permanent_code?suite_access_token=[SUITE_ACCESS_TOKEN]';//获取企业号的永久授权码

    public static $getAuthInfoUrl = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_auth_info?suite_access_token=[SUITE_ACCESS_TOKEN]';

    public static $getCorpTokenUrl = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_corp_token?suite_access_token=[SUITE_ACCESS_TOKEN]';

    public static $getAdminList = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_admin_list?suite_access_token=[SUITE_ACCESS_TOKEN]';

    public static $getuserinfobyticket = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserdetail?access_token=[ACCESS_TOKEN]';

    public static $menuurl = 'https://qyapi.weixin.qq.com/cgi-bin/menu/create?access_token=[ACCESS_TOKEN]&agentid=[AGENTID]';

    public static $delmenuurl = 'https://qyapi.weixin.qq.com/cgi-bin/menu/delete?access_token=[ACCESS_TOKEN]&agentid=[AGENTID]';

    public static $getmenuurl = 'https://qyapi.weixin.qq.com/cgi-bin/menu/get?access_token=[ACCESS_TOKEN]&agentid=[AGENTID]';

    public static $sendMessage = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=[ACCESS_TOKEN]';


    //菜单
    public static $setMenu = 'https://qyapi.weixin.qq.com/cgi-bin/menu/create?access_token=[ACCESS_TOKEN]&agentid=[AGENTID]';

    public static $getUserInfo = 'https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=[ACCESS_TOKEN]&userid=[USERID]';


    //企业授权链接
    public static $enterpriseurl = 'https://open.work.weixin.qq.com/3rdapp/install?suite_id=[SUITE_ID]&pre_auth_code=[PRE_AUTH_CODE]&redirect_uri=[REDIRECT_URI]&state=[STATE]';

    public static $getJsSdkTicket = 'https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=[ACCESS_TOKEN]';


    /**
     * 根据自定义的APP名，或是APPid，获取APP在微信企业号上的配置信息
     * @param $appName
     * @param $appId
     */
    public static function getAppInfoByName($appName = false, $appId =false)
    {
        if (false == $appName && false == $appId){
            return false;
        }
        $check = null;//区分是用APP name获取的还是appid获取的
        if ( false != $appName ){
            $where['appname'] = $appName;
            $check = $appName;
        }else{
            $where['suiteid'] = $appId;
            $check = $appId;
        }
        $data = RedisService::connectredis()->get('enterprise:' .$check. ':info:' . $appName);
        if (!$data) {
            $db = M('enterprise_app_info', 'total_');
            $data = $db->where($where)->find();
            if ($data){
                RedisService::connectredis()->set('enterprise:' .$check. ':info:' . $appName, json_encode($data));
                return $data;
            }else{
                return false;
            }
        }else{
            $data = json_decode($data, true);
            if ($data){
                return $data;
            }else{
                return false;
            }
        }
    }


    /**
     * 用suiteid和授权表内的id，获取客户的授权信息
     * @param $suiteId
     * @param $id
     * @return bool|mixed|string
     */
    public static function getAuthAppInfo($suiteId, $id)
    {
        if ($suiteId == false || $id == false) {
            return false;
        }

        $data = RedisService::connectredis()->get('enterprise:wechat:authappinfo:' . $suiteId . ':' . $id);
        if (!$data) {
            $db = M('total_enterprise_wechat_authcorpinfo');
            $data = $db->where(['suiteid'=>$suiteId, 'id'=>$id])->find();//echo $db->_sql();
            if ($data){
                RedisService::connectredis()->set('enterprise:wechat:authappinfo:' . $suiteId . ':' . $id, json_encode($data));
                return $data;
            }else{
                return false;
            }
        }else{
            $data = json_decode($data, true);
            if ($data){
                return $data;
            }else{
                return false;
            }
        }
    }


    /**
     * keyadmin和appname获取授权企业微信的accesstoken
     * @param $keyAdmin
     * @param $appName
     */
    public static function getCorpAccessToken($keyAdmin, $appName)
    {
        //商户信息
        $adminInfo = PublicService::getMerchant($keyAdmin);
        if (isset($adminInfo['code'])){
            return $adminInfo;
        }


        /**
         * 用户的授权映射
         */
        $authInfo = AppInfoService::getRelationShip($adminInfo, $appName);
        if (!is_array($authInfo) || $authInfo['data']['auth_id'] == false){
            return $authInfo;
        }

        /**
         *
         */
        $appInfo = self::getAppInfoByName($appName);
        if (!$appInfo){
            return ['code'=>4012];
        }

        //用第三方服务商的suiteid和客户的id，获取授权表的信息
        $authAppInfo = self::getAuthAppInfo($appInfo['suiteid'], $authInfo['data']['auth_id']);
        if (!$authAppInfo){
            return ['code'=>4012, 'data'=>['y'=>1]];
        }

        $corpAccessToken = EventMessageService::getCorpAccessToken($authAppInfo['corpid'], $authAppInfo['suiteid']);
        if (!$corpAccessToken){
            return ['code'=>4020];
        }

        return $corpAccessToken;
    }




}