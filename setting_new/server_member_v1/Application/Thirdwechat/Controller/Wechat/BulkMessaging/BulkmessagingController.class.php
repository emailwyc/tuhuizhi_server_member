<?php
/**
 * 群发消息接口，接口可能需要调用
 */
namespace Thirdwechat\Controller\Wechat\BulkMessaging;

use Thirdwechat\Controller\Wechat\WechatcommonController;
use Thirdwechat\Controller\Thirdwechat\EventsController;

class BulkmessagingController extends WechatcommonController
{
    /**
     * 所有人群发消息
     * @param unknown $appid
     * @param int $who，发给谁，全发还是按传入的openid列表发
     * @return boolean|mixed
     */
    protected function sendMessage($appid, $data, $who)
    {
        $events=new EventsController();
        $authorizer_access_token=$events->authorizer_access_token($appid);
        if (false==$authorizer_access_token){
            return false;
        }else{
            if (2== $who){//如果是2，则按openid列表发
                $url=$this->send_openid.$authorizer_access_token;
            }else{
                $url=$this->send_all.$authorizer_access_token;
            }
            $curl=curl_https($url,$data,array(),30,true);
            return $curl;
        }
    }
}

?>