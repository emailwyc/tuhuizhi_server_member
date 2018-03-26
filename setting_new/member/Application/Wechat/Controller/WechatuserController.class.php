<?php
namespace Wechat\Controller;
/**
 * 微信用户管理
 */
class WechatuserController extends CommonController{
    
    public function __construct(){
        $this->first();
    }
    
    
    //判断当前用户是否关注当前公众号
    public function iswatchme(){
        $url='http://'.$_SERVER['HTTP_HOST'].U('/Wechat/Weixin/getWxUserInfo','','');
        $accesstoken=new WechataccesstokenController();
        
        
    }
    
}

?>