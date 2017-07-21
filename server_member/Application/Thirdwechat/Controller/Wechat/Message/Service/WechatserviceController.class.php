<?php
/**
 * 客服消息
 */

namespace Thirdwechat\Controller\Wechat\Message\Service;

use Thirdwechat\Controller\Wechat\WechatcommonController;
use Thirdwechat\Controller\Thirdwechat\EventsController;

class WechatserviceController extends WechatcommonController
{
    protected $newstype=array('text','');
    /**
     * 发送客服消息
     * @param string $data
     * @param string $appid
     */
    public function sendServiceMessage(string $data, string $appid)
    {
        $events=new EventsController();
        $authorizer_access_token=$events->authorizer_access_token($appid);
        if (false==$authorizer_access_token){
            return false;
        }else{
            $url=$this->send_service_message.$authorizer_access_token;
            $curl=curl_https($url,$data,array(),30,true);
            return $curl;
        }
    }
    
    
}

?>