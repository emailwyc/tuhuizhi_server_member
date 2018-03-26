<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 25/01/2018
 * Time: 10:36
 */

namespace EnterpriseWechat\Controller\App\Oauth;


use Common\Controller\CommonController;
use EnterpriseWechat\Service\EnterpriseWechatCommonService;
use EnterpriseWechat\Service\Message\EventMessageService;

class EnterpriseOauthController extends CommonController
{

    private static function createUrl($appName)
    {
        $appInfo = EnterpriseWechatCommonService::getAppInfoByName($appName);
        $preAuthCode = EventMessageService::getPreAuthCode($appInfo['suiteid']);
        //https://open.work.weixin.qq.com/3rdapp/install?suite_id=[SUITE_ID]&pre_auth_code=[PRE_AUTH_CODE]&redirect_uri=[REDIRECT_URI]&state=[STATE]
        $url = EnterpriseWechatCommonService::$enterpriseurl;
        $url = str_replace('[SUITE_ID]', $appInfo['suiteid'], $url);
        $url = str_replace('[PRE_AUTH_CODE]', $preAuthCode, $url);
        $url = str_replace('[REDIRECT_URI]', urlencode('https://mem.rtmap.com/EnterpriseWechat/App/Management/AppManagement/BackendManagement'), $url);
        $url = str_replace('[STATE]', $appName, $url);
        return $url;
    }


    /**
     * 企业授权应用到其企业微信下
     */
    public function enterpriseOauth()
    {
        $appName = I('appname');
        if (!$appName){
            echo json_encode(['code'=>1030]);
        }
        $this->assign(['url'=>self::createUrl($appName)])->display('Enterprisewechat:EnterpriseOauth:authpage');
    }
}