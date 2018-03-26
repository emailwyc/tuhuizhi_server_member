<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 11/12/2017
 * Time: 19:07
 */

namespace EnterpriseWechat\Controller\App\Management;


use Common\Controller\CommonController;
use EnterpriseWechat\Service\EnterpriseWechatCommonService;
use EnterpriseWechat\Service\Message\EventMessageService;

class AppManagementController extends CommonController
{

    public function BackendManagement()
    {
        //?auth_code=D39s4UwUsW5aYOvuQRNIpW4wuju1Ixg-VoTkn_paqVYeiuWlWQ9a85tZT1XQ2aqQjElMin6-_2C1kMrH4q2XAze-mAdUHmkI7Wj8KfwEgaI&state=&expires_in=1200
        $authCode = I('get.auth_code');
        $suiteName = I('get.state');
        $appInfo = EnterpriseWechatCommonService::getAppInfoByName($suiteName);
        $data = EventMessageService::createAuth($appInfo['suiteid'], $authCode, 'create_auth', time());
        if ($data == 'success'){
            header('Location:' . 'https://vip.rtmap.com/user/login');
        }else{
            $this->display('Enterprisewechat:EnterpriseOauth:getauthorizer_error');
        }
//        $appInfo = EnterpriseWechatCommonService::getAppInfoByName($suiteName);dump($appInfo);
//        EventMessageService::setPermanentCode($appInfo['suiteid'], $authCode);

    }
}