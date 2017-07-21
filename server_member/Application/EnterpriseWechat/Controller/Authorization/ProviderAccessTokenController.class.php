<?php
/**
 * 获取应用提供商凭证：ProviderAccessToken
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/4/17
 * Time: 13:51
 * http://qydev.weixin.qq.com/wiki/index.php?title=管理后台单点登录
 */

namespace EnterpriseWechat\Controller\Authorization;


use EnterpriseWechat\Controller\EnterprisewConfigController;

class ProviderAccessTokenController extends EnterprisewConfigController
{
    protected static $token;
    /**
     * 从redis读取token，若有直接返回，没有则调用接口
     */
    private function checkProviderAccessToken()
    {
        $token = self::$redise->get('providersecret:provideraccesstoken');
        if ($token) {
            return $token;
        }else {
            $token = self::setProviderAccessToken();
            return $token;
        }
    }


    public static function getToken()
    {
        return self::checkProviderAccessToken();
    }


    /**
     * 请求微信接口，获取token
     * @return mixed
     */
    private function setProviderAccessToken()
    {
        $data = array('corpid'=>self::$corpId, 'provider_secret'=>self::$providersecret);
        $res = curl_https(self::$providertoken, json_encode( $data ), array(), 600, true, 'POST');
        if (is_json($res)){
            $arr = json_decode($res, true);
            if (is_array($arr) && isset($arr['provider_access_token']) && $arr['expires_in']){
                self::$redise->set('providersecret:provideraccesstoken', $arr['provider_access_token'], array('ex'=>$arr['expires_in']));
                return $arr['provider_access_token'];
            }else {
                returnjson(array('code'=>4010, 'data'=>$arr), self::$returnstylee, self::$callbacke);//返回报错，将信息返回调用者
            }
        }else{
            returnjson(array('code'=>4010), self::$returnstylee, self::$callbacke);//返回的不是json
        }

    }


}