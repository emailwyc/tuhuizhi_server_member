<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 21/08/2017
 * Time: 17:54
 */

namespace Thirdwechat\Controller\MiniProgram;


use Thirdwechat\Controller\Thirdwechat\EventsController;
use Thirdwechat\Controller\Wechat\WechatcommonController;

class WechatLoginController extends WechatcommonController
{
    public function getuserinfo()
    {
        $params['key_admin'] = I('post.key_admin');
        $params['js_code'] = I('post.code');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }

        $admininfo = $this->getMerchant($params['key_admin']);
        $events=new EventsController();
        $component_access_token=$events->component_access_token();
        $url = $this->miniprogram_getopenid;
        $url = str_replace('[APPID]', $admininfo['applet_appid'], $url);
        $url = str_replace('[JSCODE]', $params['js_code'], $url);
        $url = str_replace('[COMPONENT_APPID]', $this->appId, $url);
        $url = str_replace('[ACCESS_TOKEN]', $component_access_token, $url);
        $data = curl_https($url, array(), array(), 30, false, 'GET');
        if (is_json($data)) {
            $array = json_decode($data, true);
            if (!isset($array['errcode'])) {
                $this->redis->set($array['openid'], $array['session_key'], array('ex'=>864000));//session_key存十天，目前暂时没用
                unset($array['session_key']);
                returnjson(array('code'=>200, 'data'=>$array),$this->returnstyle,$this->callback);
            }else{
                returnjson(array('code'=>101, 'data'=>$array),$this->returnstyle,$this->callback);
            }
        }else{
            returnjson(array('code'=>101),$this->returnstyle,$this->callback);
        }





    }
}