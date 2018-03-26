<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 25/08/2017
 * Time: 17:48
 */

namespace Thirdwechat\Service\MiniProgram\Open;


use Thirdwechat\Controller\Wechat\WechatcommonController;

class MiniProgramTesterService
{
    /**
     * 配置小程序体验者
     * https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489140588_nVUgx&token=&lang=zh_CN
     */
    public static function bindTester($appid, $type, $wechatid)
    {
        if ($type == 'bind'){
            $url = WechatcommonController::$wechatMiniProgramBind;
        }elseif ($type == 'unbind') {
            $url = WechatcommonController::$wechatMiniProgramUnbind;
        }else{
            return array('code'=>1051);
        }

        $params = array('wechatid'=>$wechatid);//微信接口参数
        $url = str_replace('[TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);
        $re = curl_https($url, json_encode($params), array('Content-Type:application/json;charset=UTF-8'), 30, true);
        if (is_json($re)) {
            $array = json_decode($re, true);
            if ($array['errcode'] == 0){
                return array('code'=>200, 'data'=>$array);
            }else{
                return array('code'=>104, 'data'=>$re);
            }
        }else{
            return array('code'=>101, 'data'=>$re);
        }
    }
}