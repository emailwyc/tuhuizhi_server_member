<?php
/**
 * 调用第三方oauth授权接口，类似微信公众号第三方，有单独的接口，非公众号调用的接口
 * Created by PhpStorm.
 * User: zhang
 * Date: 11/12/2017
 * Time: 11:56
 */

namespace EnterpriseWechat\Controller\App\Oauth;


use Common\Controller\CommonController;
use EnterpriseWechat\Service\EnterpriseWechatCommonService;
use EnterpriseWechat\Service\Message\EventMessageService;

class OauthController extends CommonController
{

    public function oauthLogin()
    {
        $params = $_GET;
        if (!isset($params['appname'])){
            $this->assign('errorcode',4012)->display('Enterprisewechat:Oauth:getuserinfo_error');die;//没有获取到appname
        }

        if (!isset($params['oauthcallback'])){
            $this->assign('errorcode',4013)->display('Enterprisewechat:Oauth:getuserinfo_error');die;//没有授权完后的回调URL
        }

        if (!in_array($params['scope'], ['snsapi_base','snsapi_userinfo','snsapi_privateinfo'])){
            $this->assign('errorcode',4001)->display('Enterprisewechat:Oauth:getuserinfo_error');die;//scope格式错误
        }

        $params['oauthcallback'] = urldecode(htmlspecialchars_decode($params['oauthcallback']));//以防出现编码问题，先解码
        //删除已知的参数，剩下的就是业务自带的参数
        $callbackParams = $params;
        unset($callbackParams['appname']);
        unset($callbackParams['oauthcallback']);
        unset($callbackParams['scope']);
        $callbackParams = http_build_query($callbackParams);
        $params['oauthcallback'] = stripos($params['oauthcallback'],'?') ? $params['oauthcallback'] . '&' . $callbackParams : $params['oauthcallback'] . '?' . $callbackParams;

        //APP信息
        $appInfo = EnterpriseWechatCommonService::getAppInfoByName($params['appname']);
        if (false == $appInfo){
            $this->assign('errorcode',4014)->display('Enterprisewechat:Oauth:getuserinfo_error');die;//appname错误
        }

        $oauthcallback = base_encode($params['oauthcallback']);//自定义函数，转义URL


        $codeCallbackUrl = C('DOMAIN') . '/EnterpriseWechat/App/Oauth/Oauth/codeCallback/oauthcallback/' . $oauthcallback . '/appname/' . $params['appname'] . '/scope/' . $params['scope'];

        $url = EnterpriseWechatCommonService::$oauthUrl;
        $url = str_replace('[APPID]', $appInfo['suiteid'], $url);
        $url = str_replace('[REDIRECT_URI]', urlencode($codeCallbackUrl), $url);
        $url = str_replace('[SCOPE]', $params['scope'], $url);
        $url = str_replace('[STATE]', time(), $url);
        header('Location:' . $url);
//        echo $url;


    }


    public function codeCallback()
    {
        $params['oauthCallback'] = base_decode($_GET['oauthcallback']);
        $params['code'] = $_GET['code'];
        $params['state'] = $_GET['state'];
        $params['appname'] = $_GET['appname'];
        $params['scope'] = $_GET['scope'];
        if (in_array('', $params)){
            $this->assign('errorcode',4015)->display('Enterprisewechat:Oauth:getuserinfo_error');die;//没获取到必要条件，返回错误
        }
        //APP信息
        $appInfo = EnterpriseWechatCommonService::getAppInfoByName($params['appname']);
        if (false == $appInfo){
            $this->assign('errorcode',4014)->display('Enterprisewechat:Oauth:getuserinfo_error');die;//appname错误
        }
        $url = EnterpriseWechatCommonService::$getUserInfo3rd;

        $suiteAccessToken = EventMessageService::getSuiteAccessToken($appInfo['suiteid']);
        if (!$suiteAccessToken){
            $this->assign('errorcode',4014)->display('Enterprisewechat:Oauth:getuserinfo_error');die;//appname错误
        }
        $url = str_replace('[SUITE_ACCESS_TOKEN]', $suiteAccessToken, $url);
        $url = str_replace('[CODE]', $params['code'], $url);
        $request = curl_https($url, [], [], 15, false, 'GET');
        $responseData = json_decode($request, true);
        if (!is_array($responseData) || $responseData['errcode'] != 0){
            $this->assign('errorcode',4017)->display('Enterprisewechat:Oauth:getuserinfo_error');die;//appname错误
        }
        if (isset($responseData['OpenId']) || $responseData['OpenId'] != false){
            unset($responseData['errcode']);
            unset($responseData['errmsg']);
            unset($responseData['corpid']);
            $authdata = http_build_query($responseData);
            $authdata = $authdata . '&errcode=4019';
            $callbackUrl = stripos($params['oauthCallback'],'?') ? $params['oauthCallback'] . '&'.$authdata : $params['oauthCallback'] . '?'.$authdata;
//            dump($callbackUrl);
            header('Location:' . $callbackUrl);
        }

        if ($params['scope'] == 'snsapi_base'){
            $callbackUrl = stripos($params['oauthCallback'],'?') ? $params['oauthCallback'] . '&UserId='.$responseData['UserId'].'&DeviceId='.$responseData['DeviceId'] : $params['oauthCallback'] . '?UserId='.$responseData['UserId'].'&DeviceId='.$responseData['DeviceId'];
            header('Location:' . $callbackUrl);
        }else{//周一加个判断elseif
            $url = EnterpriseWechatCommonService::$getuserdetail3rd;

            $suiteAccessToken = EventMessageService::getSuiteAccessToken($appInfo['suiteid']);
            if (!$suiteAccessToken){
                $this->assign('errorcode',4014)->display('Enterprisewechat:Oauth:getuserinfo_error');die;//appname错误
            }
            $url = str_replace('[SUITE_ACCESS_TOKEN]', $suiteAccessToken, $url);
            $requests = curl_https($url, json_encode(['user_ticket'=>$responseData['user_ticket']]), ['Content-Type:application/json;charset=UTF-8'], 15, true, 'POST');
            $responseDatas = json_decode($requests, true);
            if (!is_array($responseDatas) || $responseDatas['errcode'] != 0){
                $this->assign('errorcode','4018-'.$responseDatas['errcode'])->display('Enterprisewechat:Oauth:getuserinfo_error');die;//appname错误
            }

//            dump($responseDatas);

            unset($responseDatas['errcode']);
            unset($responseDatas['errmsg']);
            unset($responseDatas['corpid']);
            $data = http_build_query($responseDatas);
            $callbackUrl = stripos($params['oauthCallback'],'?') ? $params['oauthCallback'] . '&'.$data : $params['oauthCallback'] . '?'.$data;
//            dump($callbackUrl);
            header('Location:' . $callbackUrl);

        }

    }
}