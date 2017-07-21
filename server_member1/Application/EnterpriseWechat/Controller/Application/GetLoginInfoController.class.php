<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/4/17
 * Time: 14:55
 *
 * http://qydev.weixin.qq.com/wiki/index.php?title=成员登录授权
 */

namespace EnterpriseWechat\Controller\Application;


use EnterpriseWechat\Controller\EnterprisewConfigController;

class GetLoginInfoController extends EnterprisewConfigController
{


    /**
     * @param $auth_code 授权跳转时URL携带的auth_code
     * @param $token  两种token，一种是企业自己的，一种是第三方服务商的，请求的URL
     * @param $url
     * @return bool|mixed
     */
    public static function getLoginInfo($auth_code, $token)
    {
        if ($auth_code == null || $token == null){
            return false;
        }
        return self::getLoginInfoFWechat($auth_code, $token);
    }


    /**
     * @param $auth_code
     * @param $token
     * @param $url
     * @return mixed
     */
    private function getLoginInfoFWechat($auth_code, $token)
    {
        $url = str_replace('[ACCESS_TOKEN]', $token, self::$getlogininfo);
        $info = curl_https($url, json_encode( array('auth_code'=>$auth_code) ), array(), 600, true, 'POST');
        return $info;
    }


}