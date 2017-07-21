<?php
namespace Thirdwechat\Controller\Wechat\Menu;

use Thirdwechat\Controller\Wechat\WechatcommonController;
use Thirdwechat\Controller\Thirdwechat\EventsController;
class MenuonwechatController extends WechatcommonController{
    // TODO - Insert your code here
    public function _initialize(){
        //header("content-Type: text/html; charset=UTF-8");
        parent::_initialize();
    }
    
    /**
     *@desc  创建菜单
     *
     */
    protected function createMenu($appid,$menu) {
        $events=new EventsController();
        $authorizer_access_token=$events->authorizer_access_token($appid);
        if (false==$authorizer_access_token){
            return false;
        }else{
            //执行业务逻辑
            $url=$this->create_menu_url.$authorizer_access_token;
            $return=curl_https($url,$menu,array(),30,true);//dump($return);
            return $return;
        }
    }
    
    
    /**
     * @desc    获取菜单
     */
    protected function getMenu($appid) {
        $events=new EventsController();
        $authorizer_access_token=$events->authorizer_access_token($appid);//获取第三方的authorizer_access_token
        if (false==$authorizer_access_token){
            return false;
        }else{
            //执行业务逻辑
            $url=$this->get_menu_url.$authorizer_access_token;
            $return=curl_https($url);
            return $return;
        }
    }
    
    
}

?>