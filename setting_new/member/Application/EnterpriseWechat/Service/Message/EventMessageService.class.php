<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 08/12/2017
 * Time: 17:46
 */

namespace EnterpriseWechat\Service\Message;


use Common\Service\RedisService;
use EnterpriseWechat\Service\EnterpriseWechatCommonService;

class EventMessageService
{


    public static function createAuth($suiteId, $authCode, $infoType, $timeStamp)
    {
        self::setPermanentCode($suiteId, $authCode);
        return 'success';
    }


    /**
     * 微信每隔十分钟自动推一次suiteTicket
     * @param $suiteId
     * @param $SuiteTicket
     * @param $TimeStamp
     * @param $appInfo
     */
    public static function suiteTicket($suiteId, $suiteTicket, $TimeStamp = null, $appInfo)
    {
        if (!$appInfo) {
            return false;
        }
        RedisService::connectredis()->set('enterprisewechat:suiteticket:' . $suiteId, $suiteTicket);
//        self::setSuiteAccessToken($suiteId, $suiteTicket, $appInfo);
        return 'sucess';
    }


    /**
     *
     * 获取应用的accesstoken
     * @param $appSuiteid|应用id
     * @return bool|string
     */
    public static function getSuiteAccessToken($appSuiteid)
    {
        $token = RedisService::connectredis()->get('enterprisewechat:suiteaccesstoken:'. $appSuiteid);
        if (!$token){//redis里面没有则查库
            $db = M('total_enterprise_app_info');
            $find = $db->field('suiteaccesstoken,suiteaccesstokentime')->where(['suiteid'=>$appSuiteid])->find();
            if (!$find || $find['suiteaccesstoken'] ==false){//查不到货结果字段为空
                $token = self::setSuiteAccessToken($appSuiteid);
                return $token;
            }else{
                $time = time() - $find['suiteaccesstokentime'];
                if ( $time > 6000 ){//只要剩余时间大于1000秒，就再用一会，十好几分钟呢，不能败家
                    $token = self::setSuiteAccessToken($appSuiteid);
                    return $token;
                }else{
                    RedisService::connectredis()->set('enterprisewechat:suiteaccesstoken:'. $appSuiteid, $find['suiteaccesstoken'], ['ex'=>$find['suiteaccesstokentime']+7000-time()]);
                    return $find['suiteaccesstoken'];
                }
            }
        }
        return $token;


    }

    /**
     * 调用接口获取第三方应用凭证
     * https://work.weixin.qq.com/api/doc#10975搜：获取第三方应用凭证
     * @param $suiteId|应用id
     * @param null $suiteTicket|微信推送的ticket
     * @param $appInfo|数据库自己存的应用详情
     * @return bool
     */
    private static function setSuiteAccessToken($suiteId, $suiteTicket = null, $appInfo=null)
    {
        if ($suiteTicket == null){
            //ticket每隔十分钟存入接收一次，而且只能用最新的，没有存库，所以一旦清redis，就最多等10分钟吧，一会就好了，就不存库，没啥别的用处
            $suiteTicket = RedisService::connectredis()->get('enterprisewechat:suiteticket:' . $suiteId);
        }
        //和上面说的注释似的，最多等十分钟吧
        if ($suiteTicket == false){
            return false;
        }
        if (!$appInfo){
            $appInfo = EnterpriseWechatCommonService::getAppInfoByName(false, $suiteId);
        }
        if (!$appInfo){
            return false;
        }
        $params = [
            'suite_id'=>$suiteId,
            'suite_secret'=>$appInfo['secret'],
            'suite_ticket'=>$suiteTicket
        ];
        $response = curl_https(EnterpriseWechatCommonService::$suiteAccessTokenUrl, json_encode($params), ['Content-Type:application/json;charset=UTF-8'], 10, true);

        if (!is_json($response)) {
            return false;
        }

        $data = json_decode($response, true);
        if (isset($data['suite_access_token']) && $data['suite_access_token'] != false){//文档上有errcode，实际接口没有，草泥马的
            RedisService::connectredis()->set('enterprisewechat:suiteaccesstoken:'. $suiteId, $data['suite_access_token'], ['ex'=>$data['expires_in'] - 200]);
            $db = M('total_enterprise_app_info');
            $saveToken = $db->where(['suiteid'=>$suiteId])->save(['suiteaccesstoken'=>$data['suite_access_token'], 'suiteaccesstokentime'=>time()]);
            return $data['suite_access_token'];
        }else{
            return false;
        }
    }


    /**
     * 获取预授权码，建立好应用后，就可以用
     * @param $suiteId
     * @return bool|mixed|string
     */
    public static function getPreAuthCode($suiteId)
    {
        $preAuthCode = RedisService::connectredis()->get('enterprisewechat:suite:preauthcode:' . $suiteId);
        if (!$preAuthCode){
            $preAuthCode = self::setSuitePreAuthCode($suiteId);
        }
        return $preAuthCode;
    }

    /**
     * 调用接口获取应用的预授权吗
     * @param $suiteId|应用id
     * @return bool|mixed
     */
    private static function setSuitePreAuthCode($suiteId)
    {
        $suiteAccessToken = self::getSuiteAccessToken($suiteId);
        if (!$suiteAccessToken) {
            return false;
        }
        $url = EnterpriseWechatCommonService::$preAuthCode;
        $url = str_replace('[SUITE_ACCESS_TOKEN]', $suiteAccessToken, $url);
        $request = curl_https($url, [], [], 10, false, 'GET');
        $responseData = json_decode($request, true);
        if (is_array($responseData) && isset($responseData['errcode']) && $responseData['errcode'] == 0){
            RedisService::connectredis()->set('enterprisewechat:suite:preauthcode:' . $suiteId, $responseData['pre_auth_code'], ['ex'=>$responseData['expires_in'] - 100]);
            return $responseData['pre_auth_code'];
        }else{
            return false;
        }
    }


    /**
     * https://work.weixin.qq.com/api/doc#10975:获取企业永久授权码
     *  获取企业永久授权码和token：permanent_code、access_token，回调时可用，企业在微信号后台点登陆服务商后台按钮跳转时，也可以用
     * @param $suiteId
     * @param $authCode
     * @return bool|string
     */
    public static function setPermanentCode($suiteId, $authCode)
    {
        $suiteAccessToken = self::getSuiteAccessToken($suiteId);
        $url = str_replace('[SUITE_ACCESS_TOKEN]', $suiteAccessToken, EnterpriseWechatCommonService::$getPermanentCode);
        $request = curl_https($url, json_encode(['auth_code'=>$authCode]), ['Content-Type:application/json;charset=UTF-8'], 15, true, 'POST');
        $responseData = json_decode($request, true);
        if (is_array($responseData) && isset($responseData['access_token']) && isset($responseData['permanent_code']) && false != $responseData['access_token'] && false != $responseData['permanent_code']){
            RedisService::connectredis()->set('enterprisewechat:permanentcode:' . $responseData['auth_corp_info']['corpid'], $responseData['permanent_code']);//企业永久授权码
            RedisService::connectredis()->set('enterprisewechat:corp:accesstoken:' . $responseData['auth_corp_info']['corpid'], $responseData['access_token'],['ex'=>$responseData['expires_in'] - 200]);//企业token
            $data = [
                'suiteid'=>$suiteId,
                'access_token'=>$responseData['access_token'],
                'permanent_code'=>$responseData['permanent_code'],
                'corpid'=>$responseData['auth_corp_info']['corpid'],
                'corp_name'=>$responseData['auth_corp_info']['corp_name'],
                'corp_type'=>$responseData['auth_corp_info']['corp_type'],
                'corp_square_logo_url'=>$responseData['auth_corp_info']['corp_square_logo_url'],
                'corp_user_max'=>$responseData['auth_corp_info']['corp_user_max'],
                'corp_full_name'=>$responseData['auth_corp_info']['corp_full_name'],
                'subject_type'=>$responseData['auth_corp_info']['subject_type'],
                'verified_end_time'=>$responseData['auth_corp_info']['verified_end_time'],
                'corp_wxqrcode'=>$responseData['auth_corp_info']['corp_wxqrcode'],
                'corp_agent_max'=>$responseData['auth_corp_info']['corp_agent_max'],
                'agentid'=>$responseData['auth_info']['agent'][0]['agentid'],
                'agentname'=>$responseData['auth_info']['agent'][0]['name'],
                'square_logo_url'=>$responseData['auth_info']['agent'][0]['square_logo_url'],
                'appid'=>'旧的多应用套件中的对应应用id，新开发者请忽略',
                'allow_party'=>json_encode($responseData['auth_info']['agent'][0]['privilege']['allow_party']),
                'allow_tag'=>json_encode($responseData['auth_info']['agent'][0]['privilege']['allow_tag']),
                'allow_user'=>json_encode($responseData['auth_info']['agent'][0]['privilege']['allow_user']),
                'extra_party'=>json_encode($responseData['auth_info']['agent'][0]['privilege']['extra_party']),
                'extra_tag'=>json_encode($responseData['auth_info']['agent'][0]['privilege']['extra_tag']),
                'level'=>$responseData['auth_info']['agent'][0]['privilege']['level'],
                'email'=>$responseData['auth_user_info']['email'],
                'mobile'=>$responseData['auth_user_info']['mobile'],
                'userid'=>$responseData['auth_user_info']['userid'],
                'authusername'=>$responseData['auth_user_info']['name'],
                'avatar'=>$responseData['auth_user_info']['avatar'],
                'contentdata'=>$request
            ];
            $db = M('total_enterprise_wechat_authcorpinfo');
            $find = $db->where(['suiteid'=>$suiteId, 'corpid'=>$responseData['auth_corp_info']['corpid']])->find();
            if ($find){
                $db->where(['id'=>$find['id']])->save($data);
            }else{
                $db->add($data);
            }
            return 'success';
        }else{
            return false;
        }
    }



    public static function getPermanentCode($suiteId, $corpId)
    {
        $code = RedisService::connectredis()->get('enterprisewechat:permanentcode:' . $suiteId);
        if (false == $code){
            $db = M('total_enterprise_wechat_authcorpinfo');
            $find = $db->where(['suiteid'=>$suiteId, 'corpid'=>$corpId])->find();
            if ($find){
                RedisService::connectredis()->set('enterprisewechat:permanentcode:' . $suiteId, $find['permanent_code']);
                return $find['permanent_code'];
            }else{
                return false;
            }
        }else{
            return $code;
        }
    }

    /**
     * https://work.weixin.qq.com/api/doc#10975:获取企业授权信息
     * 这个接口仅仅是授权后，后台查看授权的信息，点查看的时候调用接口
     * @param $corpId|授权方的企业id
     */
    public static function getCorpAuthInfo($corpId, $suiteId)
    {
        if (false == $corpId || false == $suiteId){
            return false;
        }
        $suiteAccessToken = self::getSuiteAccessToken($suiteId);
        if ($suiteAccessToken == false){
            return false;
        }
        $permanentCode = self::getPermanentCode($suiteId, $corpId);
        if ($permanentCode == false){
            return false;
        }
        $url = str_replace('[SUITE_ACCESS_TOKEN]', $suiteAccessToken, EnterpriseWechatCommonService::$getAuthInfoUrl);
        $request = curl_https($url, json_encode([ 'auth_corpid'=>$corpId, 'permanent_code'=>$permanentCode ]), ['Content-Type:application/json;charset=UTF-8'], 15, true, 'POST');
        $responseData = json_decode($request, true);
        if (!is_array($responseData)){
            return false;
        }else{
            return $responseData;
        }
    }


    /**
     * https://work.weixin.qq.com/api/doc#10975：获取企业access_token
     * @param $suiteId
     * @param $corpId
     */
    private static function setCorpAccessToken($corpId, $suiteId)
    {
        if (false == $corpId || false == $suiteId){
            return false;
        }
        $suiteAccessToken = self::getSuiteAccessToken($suiteId);
        if ($suiteAccessToken == false){
            return false;
        }
        $permanentCode = self::getPermanentCode($suiteId, $corpId);
        if ($permanentCode == false){
            return false;
        }
        $url = str_replace('[SUITE_ACCESS_TOKEN]', $suiteAccessToken, EnterpriseWechatCommonService::$getCorpTokenUrl);
        $request = curl_https($url, json_encode(['auth_corpid'=>$corpId, 'permanent_code'=>$permanentCode]), ['Content-Type:application/json;charset=UTF-8'], 15, true, 'POST');
        $responseData = json_decode($request, true);

        if (!is_array($responseData) || !isset($responseData['access_token']) || false == $responseData['access_token']){
            return false;
        }else{
            RedisService::connectredis()->set('enterprisewechat:corp:accesstoken:' . $corpId, $responseData['access_token'],['ex'=>$responseData['expires_in'] - 200]);//企业token
            return $responseData['access_token'];
        }
    }


    /**
     * 从redis或redis中没有时，调用接口获取
     * @param $corpId
     * @param $suiteId
     */
    public static function getCorpAccessToken($corpId, $suiteId)
    {
        $token = RedisService::connectredis()->get('enterprisewechat:corp:accesstoken:' . $corpId);
        if (false == $token){
            $token = self::setCorpAccessToken($corpId, $suiteId);
        }
        return $token;
    }


    /**
     * 获取应用的管理员列表
     * @param $suiteId
     * @param $corpId
     * @param $agentId
     * @return bool|mixed
     */
    public static function getAdminList($suiteId, $corpId, $agentId)
    {
        if (false == $suiteId || false == $corpId || false == $suiteId){
            return false;
        }

        $suiteAccessToken = self::getSuiteAccessToken($suiteId);
        if ($suiteAccessToken == false){
            return false;
        }
        $url = str_replace('[SUITE_ACCESS_TOKEN]', $suiteAccessToken, EnterpriseWechatCommonService::$getAdminList);
        $request = curl_https($url, json_encode(['auth_corpid'=>$corpId, 'agentid'=>$agentId]), ['Content-Type:application/json;charset=UTF-8'], 15, true, 'POST');
        $responseData = json_decode($request, true);
        if (is_array($responseData)){
            return $responseData;
        }else{
            return false;
        }






    }
































}