<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/7/4
 * Time: 19:34
 */

namespace Thirdwechat\Controller\Wechat\QrCode;


use Thirdwechat\Controller\Thirdwechat\EventsController;
use Thirdwechat\Controller\Wechat\WechatcommonController;

class QrCode extends WechatcommonController
{

    public function __construct()
    {
        parent::_initialize();
    }


    public function getQrcode($appid, $json)
    {
        $events=new EventsController();
        $authorizer_access_token=$events->authorizer_access_token($appid);
        if (false==$authorizer_access_token){
            return false;
        }else{
            //执行业务逻辑
            $url=$this->qrcode.$authorizer_access_token;
            $return=curl_https($url,$json,array(),30,true);//dump($return);
            return $return;
        }
    }
}