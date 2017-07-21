<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/4/7
 * Time: 19:02
 */

namespace EnterpriseWechat\Controller;


use Common\Controller\CommonController;
use EnterpriseWechat\Controller\Authorization\EnterpriseAccessTokenController;
use EnterpriseWechat\Controller\Authorization\ProviderAccessTokenController;

class EnterprisewConfigController extends CommonController
{
    protected static $redise;

    protected static $returnstylee;

    protected static $callbacke;

    protected static $corpId = 'wx5ff36e7f35988f80';

    protected $getaccessTokenurl = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=[corpid]&corpsecret=[secrect]';

    protected static $getlogininfo = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_login_info?access_token=[ACCESS_TOKEN]';

    protected static $providertoken = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_provider_token';//获取应用提供商凭证

    protected static $providersecret = 'g5FAuBs7MM9EdyiAHLfRCDmRp346_Xpv-9iml1dx4qZ3WJ0bPiy5szDzuuO90mXV';

    protected $oauthcodeurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=[CORPID]&redirect_uri=[REDIRECT_URI]&response_type=code&scope=[SCOPE]&agentid=[AGENTID]&state=STATE#wechat_redirect';

    protected $oauthinfo = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=[ACCESS_TOKEN]&code=[CODE]';

    private $suiteaccesstokenurl = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token';//获取应用套件令牌

    private $pre_auth_code = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_pre_auth_code?suite_access_token=[SUITE_ACCESS_TOKEN]';//预授权码

    private $getpermanentcode = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_permanent_code?suite_access_token=[SUITE_ACCESS_TOKEN]';//获取企业号的永久授权码

    private $getauthinfourl = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_auth_info?suite_access_token=[SUITE_ACCESS_TOKEN]';

    private $getcorptokenurl = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_corp_token?suite_access_token=[SUITE_ACCESS_TOKEN]';

    protected $getuserinfobyticket = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserdetail?access_token=[ACCESS_TOKEN]';

    protected $menuurl = 'https://qyapi.weixin.qq.com/cgi-bin/menu/create?access_token=[ACCESS_TOKEN]&agentid=[AGENTID]';

    protected $delmenuurl = 'https://qyapi.weixin.qq.com/cgi-bin/menu/delete?access_token=[ACCESS_TOKEN]&agentid=[AGENTID]';

    protected $getmenuurl = 'https://qyapi.weixin.qq.com/cgi-bin/menu/get?access_token=[ACCESS_TOKEN]&agentid=[AGENTID]';



    public function _initialize()
    {
        parent::__initialize();
        self::$redise = $this->redis;
        self::$returnstylee = $this->returnstyle;
        self::$callbacke = $this->callback;
//        $this->redis;
    }

    /**
     * 获取Accesstoken
     * @param $secure
     * @return mixed
     */
    protected function getAccessToken($secure)
    {
        $accesstoken = EnterpriseAccessTokenController::getAccessToken(self::$corpId, $secure, $this->getaccessTokenurl);
        if ($accesstoken['code'] === 200 ){
            return $accesstoken['data'];
        }else{
            returnjson($accesstoken, $this->returnstyle, $this->callback);
        }
    }


    /**
     * 获取应用提供商凭证
     * @return mixed
     */
    protected function getProviderAccessToken()
    {
        return ProviderAccessTokenController::getToken();
    }


    /**
     *下面的token获取文档
     * http://qydev.weixin.qq.com/wiki/index.php?title=第三方应用接口说明
     *
     *
     */



    /**
     * 获取应用套件令牌（suite_access_token）
     *
     * @param $suiteid
     * @return mixed
     */
    protected function getSuiteAccessToken($suiteid)
    {
        $token = $this->redis->get('enterprise:suite_access_token:' . $suiteid);
        if ($token == false ) {
            $data['suite_id'] = $suiteid;
            $data['suite_secret'] = $this->redis->get('enterprise:secret:suite_id:' . $suiteid);
            if ($data['suite_secret'] == false) {
                $db = M('enterprise_suite', 'total_');
                $find = $db->where(array('suiteid'=>$suiteid))->find();
                if ($find != false){
                    $data['suite_secret'] = $find['suite_secret'];
                }else{
                    return false;
                }
            }
            $data['suite_ticket'] = $this->redis->get('enterprise:suiteticket:' . $suiteid);
            $res = curl_https($this->suiteaccesstokenurl, json_encode($data), array(), 60, true);
            if (is_json($res)) {
                $arr = json_decode($res, true);
                if ( isset($arr['suite_access_token']) && $arr['suite_access_token'] != false) {
                    $this->redis->set('enterprise:suite_access_token:' . $suiteid, $arr['suite_access_token'], array('ex'=>$arr['expires_in']));
                    $token = $arr['suite_access_token'];
                }else{
                    returnjson(array('code'=>104, 'data'=>$arr), $this->returnstyle, $this->callback);
                }
            }else{
                returnjson(array('code'=>4000), $this->returnstyle, $this->callback);
            }
        }
        return $token;
    }


    /**
     * 获取预授权码
     * @param $suiteid
     * @return mixed
     */
    protected function getPreAuthCode($suiteid)
    {
        $token = $this->redis->get('enterprise:pre_auth_code:' . $suiteid);
        if ($token == false){
            $suite_access_token = $this->redis->get('enterprise:suite_access_token:' . $suiteid);
            $url =$this->pre_auth_code;
            $url = str_replace('[SUITE_ACCESS_TOKEN]', $suite_access_token, $url);
            $res = curl_https($url, json_decode(array('suite_id'=>$suiteid)), array(), 60, true);
            if (is_json($res)) {
                $arr = json_decode($res, true);
                if ( isset($arr['errcode']) && $arr['errcode'] == 0) {
                    $this->redis->set('enterprise:pre_auth_code:' . $suiteid, $arr['pre_auth_code'], array('ex'=>$arr['expires_in']));
                    $token = $arr['pre_auth_code'];
                }else{
                    returnjson(array('code'=>104, 'data'=>$arr), $this->returnstyle, $this->callback);
                }
            }else{
                returnjson(array('code'=>4000), $this->returnstyle, $this->callback);
            }
        }
        return $token;
    }


    /**
     * 设置授权配置
     * @param $suiteid
     */
    protected function setSessionInfo($suiteid)
    {

    }


    /**
     * 获取企业号的永久授权码，仅在授权时执行一次
     * @param $authCode
     * @param $suiteId
     * @return mixed
     */
    protected function getPermanentCode($authCode, $suiteId)
    {
//        $code = $this->redis->get('enterpriser:premanentcode:' . $suiteId . $arr['auth_corp_info']['corpid']);
        $url = $this->getpermanentcode;
        $token = $this->getSuiteAccessToken($suiteId);
        $url = str_replace('[SUITE_ACCESS_TOKEN]', $token, $url);
        $res = curl_https($url, json_encode(array('suite_id'=>$suiteId, 'auth_code'=>$authCode)), array(), 60, true);
        if (is_json($res)) {
            $arr = json_decode($res, true);
            if ( isset($arr['access_token']) && $arr['access_token'] != false) {
//                $this->redis->set('enterprise:premanentcode:fulljson:' . $suiteId, $arr);
//                $this->redis->set('enterprise:corp:accesstoken:' . $suiteId . ':' . $arr['auth_corp_info']['corpid'], $arr['access_token'], array('ex'=>$arr['expires_in']-20));//授权方（企业）access_token
                $this->redis->set('enterprise:corp:accesstoken:' . $arr['auth_corp_info']['corpid'], $arr['access_token'], array('ex'=>$arr['expires_in']-20));//授权方（企业）access_token

                $this->redis->set('enterpriser:premanentcode:' . $suiteId . $arr['auth_corp_info']['corpid'], $arr['permanent_code']);//企业号永久授权码。长度为64至512个字节
            }else{
                returnjson(array('code'=>104, 'data'=>array('wechat'=>$arr, 'type'=>'premanentcode')), $this->returnstyle, $this->callback);
            }
        }else{
            returnjson(array('code'=>4000), $this->returnstyle, $this->callback);
        }
        return $arr;
    }


    protected function getpermanent($suiteId, $corpid)
    {
        $code = $this->redis->get('enterpriser:premanentcode:' . $suiteId . $corpid);
        if (!$code) {
            $db = M('enterprise_corp_info', 'total_');
            $find = $db->where(array('corpid'=>$corpid, 'suiteid'=>$suiteId))->find();
            if ($find['permanent_code'] != false) {
                $this->redis->set('enterpriser:premanentcode:' . $suiteId . $corpid, $find['permanent_code']);//企业号永久授权码。长度为64至512个字节
                $code = $find['permanent_code'];
            }else{
                returnjson(array('code'=>104, 'data'=>'permanent error'), $this->returnstyle, $this->callback);
            }
        }
        return $code;
    }

    /**
     * 获取企业号的授权信息（好像和上面的getPermanentCode一样，这个只是单独获取一遍）
     * @param $suiteId
     * @param $authCorpid
     * @param $permanentCode
     * @return mixed
     */
    protected function getAuthInfo($suiteId, $authCorpid, $permanentCode)
    {
        $url = $this->getauthinfourl;
        $suite_access_token = $this->redis->get('enterprise:suite_access_token:' . $suiteId);
        $url = str_replace('[SUITE_ACCESS_TOKEN]', $suite_access_token, $url);
        $params=array('suite_id'=>$suiteId, 'auth_corpid'=>$authCorpid, 'permanent_code'=>$permanentCode);
        $res = curl_https($url, $params, array(), 60, true);
        if (is_json($res)) {
            $arr = json_decode($res, true);
            if ( isset($arr['auth_corp_info']) && $arr['auth_corp_info'] != false) {

            }else{
                returnjson(array('code'=>104, 'data'=>array('wechat'=>$arr, 'type'=>'authinfo')), $this->returnstyle, $this->callback);
            }
        }else{
            returnjson(array('code'=>4000), $this->returnstyle, $this->callback);
        }
        return $arr;
    }


    /**
     * 获取token
     * @param $suiteId
     * @param $authCorpid
     * @param $permanentCode
     * @return mixed
     *0VNFHCwujDUM3vJSo_YdI5r0cmouIkQu0lvq08v7M84WsogwvteXDllUH9_ne6_o
     */
    protected function getCorpAccessToken($suiteId, $authCorpid)
    {
//        $token = $this->redis->get('enterprise:corp:accesstoken:' . $suiteId . ':' . $authCorpid);
        $token = $this->redis->get('enterprise:corp:accesstoken:' . $authCorpid);

        if ($token ==false){
            $url = $this->getcorptokenurl;
            $suite_access_token = $this->getSuiteAccessToken($suiteId);
            $url = str_replace('[SUITE_ACCESS_TOKEN]', $suite_access_token, $url);
            $permanentCode = $this->getpermanent($suiteId, $authCorpid);//永久授权码
            $params=array('suite_id'=>$suiteId, 'auth_corpid'=>$authCorpid, 'permanent_code'=>$permanentCode);
            $res = curl_https($url, json_encode($params), array(), 60, true);
            if (is_json($res)) {
                $arr = json_decode($res, true);
                if ( isset($arr['access_token']) && $arr['access_token'] != false) {
//                    $this->redis->set('enterprise:corp:accesstoken:' . $suiteId . ':' . $authCorpid, $arr['access_token'], array('ex'=>$arr['expires_in']-20));
                    $this->redis->set('enterprise:corp:accesstoken:' . $authCorpid, $arr['access_token'], array('ex'=>$arr['expires_in']-20));
                    $token = $arr['access_token'];
                }else{
                    returnjson(array('code'=>104, 'data'=>array('wechat'=>$arr, 'type'=>'corpaccesstoken')), $this->returnstyle, $this->callback);
                }
            }else{
                returnjson(array('code'=>4000), $this->returnstyle, $this->callback);
            }
        }
        return $token;
    }
}