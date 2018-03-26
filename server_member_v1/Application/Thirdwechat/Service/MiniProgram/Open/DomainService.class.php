<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 23/08/2017
 * Time: 17:00
 */

namespace Thirdwechat\Service\MiniProgram\Open;


use Thirdwechat\Controller\Wechat\WechatcommonController;

class DomainService
{
    /**
     *设置域名
     * https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489138143_WPbOO&token=&lang=zh_CN
     */
    public static function modify_domain($appid, $action, $requestdomain, $wsrequestdomain, $uploaddomain, $downloaddomain)
    {
//        $d = D('total_wechat_miniprogram');
//        $sel = $d->where(array('miniprogram_key'=>array('in', array('wsrequestdomain', 'uploaddomain', 'requestdomain', 'downloaddomain'))))->select();//这几个需要是一位数组序列化的字符串
//        if ($sel){
//            $data = array_column($sel, 'miniprogram_value', 'miniprogram_key');
        $params['action'] = $action;
        if ('get' != $action){
            $params = array(
                'action'=>$action,
                'requestdomain' => $requestdomain,
                'wsrequestdomain' => $wsrequestdomain,
                'uploaddomain' => $uploaddomain,
                'downloaddomain' => $downloaddomain
            );
        }

        $url = WechatcommonController::$wechatMiniProgramDomain;
        $url = str_replace('[TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);
        $re = curl_https($url, json_encode($params), array('Content-Type:application/json;charset=UTF-8'), 30, true);
        if (is_json($re)) {
            $array = json_decode($re, true);
            if ($array['errcode'] == 0){
                unset($array['errcode']);
                unset($array['errmsg']);
                return array('code'=>200, 'data'=>$array);
            }else{
                return array('code'=>104, 'data'=>$re);
            }
        }else{
            return array('code'=>101, 'data'=>$re);
        }
//        }else{
//            return array('code'=>102, 'data'=>false);
//        }
    }
}