<?php
namespace Thirdwechat\Controller\Thirdwechat;
use Thirdwechat\Controller\Thirdwechat\ThirdwechatcommonController;
use Common\Controller\RedisController;
use Thirdwechat\Controller\Thirdwechat\EventsController;

class GetauthorizerController extends ThirdwechatcommonController{
    // TODO - Insert your code here
    
    private function index() {
        
        //先获取pre_auth_code
        $rediss = new RedisController();
        $redis=$rediss->connectredis();
        $pre_auth_code=$redis->get('wechat:pre_auth_code');
        if (empty($pre_auth_code)){
            $event=new EventsController();
            $pre_auth_code=$event->get_pre_auth_code();
        }
        
        $url=$this->componentloginpage_url;
        $hosttype=!is_https()?'http':'https';//判断当前域名是否是https链接
        $backurl=$hosttype.'://'.$_SERVER['HTTP_HOST'].U('/Thirdwechat/Thirdwechat/Events/getauthorizer','','');
        $url=str_replace('[component_appid]',$this->appId,$url);
        $url=str_replace('[pre_auth_code]',$pre_auth_code,$url);
        $url=str_replace('[redirect_uri]',$backurl,$url);
        $redis->close();
        return $url;
        //echo '<a href='.$url.' target="__blank">点我授权公众号信息给开发平台';
    }
    
    public function authurl(){
        echo  $this->index();
    }
    private function returnauthurl(){
        return $this->index();
    }
    public function clickurl(){
        echo '<a href='.$this->returnauthurl().' target="__blank">点我授权公众号信息给开发平台';
    }
    public function getauthorizer() {
        
        
    }
}

?>