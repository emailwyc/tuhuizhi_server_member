<?php
namespace Thirdwechat\Controller\Wechat;


use Thirdwechat\Controller\Wechat\Menu\MenuonwechatController;
class MenuController extends MenuonwechatController{
    // TODO - Insert your code here
    public function _initialize(){
        //header("content-Type: text/html; charset=UTF-8");
        parent::_initialize();
    }
    
    /**
     * @desc    创建自定义菜单
     */
    public function create_menu(){
//         dump(json_decode($a,true));
//         $json='{
//     "button": [
//         {
//             "name": "LBSa",
//             "sub_button": [
//                 {
//                     "type": "view",
//                     "name": "通用测试",
//                     "url": "http://dev.rtmap.com/xunlu-dev/redirect/oauth/wxf3a057928b881466/snsapi_base?redirectURL=http://115.28.196.16:8080/wechat_lbs_test/"
//                 },
//                 {
//                     "type": "view",
//                     "name": "定位测试",
//                     "url": "http://dev.rtmap.com/xunlu-dev/redirect/oauth/wxf3a057928b881466/snsapi_base?redirectURL=http://maps.rtmap.com:8080/webmap_beta/&key=nAP7nj5ro6&buildid=861300010040300019&floor=f1"
//                 },
//                 {
//                     "type": "view",
//                     "name": "卡包",
//                     "url": "http://dev.rtmap.com/xunlu-dev/beacon/shake/transfer/1.do?redirectURL=http://res.rtmap.com/image/vs2/proj/standard2demo/list.html"
//                 }
//             ]
//         },
//         {
//             "name": "我的",
//             "sub_button": [
//                 {
//                     "type": "view",
//                     "name": "扫描测试",
//                     "url": "http://weix.rtmap.com/redirect/transfer/snsapi_base.do?redirectURL=http://www.rtmap.com/wx_yy/test/index.html"
//                 },
//                 {
//                     "type": "view",
//                     "name": "扫描开发",
//                     "url": "http://weix.rtmap.com/redirect/transfer/snsapi_base.do?redirectURL=http://www.rtmap.com/wx_yy/dev/index.html"
//                 },
//                 {
//                     "type": "view",
//                     "name": "直接访问测试",
//                     "url": "http://www.rtmap.com/wx_yy/test/index.html"
//                 },
//                 {
//                     "type": "view",
//                     "name": "直接访问开发",
//                     "url": "http://www.rtmap.com/wx_yy/dev/index.html"
//                 },
//                 {
//                     "type": "view",
//                     "name": "卡包",
//                     "url": "http://dev.rtmap.com/xunlu-dev/beacon/shake/transfer/1.do?redirectURL=http://res.rtmap.com/image/vs2/proj/standard2demo/list.html"
//                 }
//             ]
//         },
//         {
//             "name": "我身边儿",
//             "sub_button": [
//                 {
//                     "type": "view",
//                     "name": "找券儿",
//                     "url": "http://weix.rtmap.com/beacon/shake/transfer/172.do?redirectURL=http://res.rtmap.com/image/vs2/proj/haidsq/a87b08a764853d34f02469672b0f438c.html"
//                 },
//                 {
//                     "type": "click",
//                     "name": "找聊儿",
//                     "key": "reply_chat"
//                 },
//                 {
//                     "type": "view",
//                     "name": "线上摇一摇",
//                     "url": "http://weix.rtmap.com/redirect/oauth/wxb5e69065eb3d67ce/snsapi_base?redirectURL=res.rtmap.com/image/vs4/cocos2/?promoId=9162"
//                 }
//             ]
//         }
//     ]
// }';
       
        $msg=$this->commonerrorcode;
        //$menu=I('menu');
        $menus=file_get_contents('php://input');//$json;
//         dump(json_decode($menus,true));
        $key_admin=I('get.key_admin');
        if (''==$key_admin){
            $msg['code']=4001;
        }else{
            //$admininfo=$this->redis->get('wechat:'.$key_admin.':admin');
            $admininfo=$this->getMerchant($key_admin);//redis->get('wechat:'.$key_admin.':admin');
            
//             if (empty($admininfo)){//从数据库里面查询内容
//                 $admininfo=$this->selectadmin($key_admin);
//             }else {//把reids里面的数据转为数组
//                 $admininfo=json_decode($admininfo,true);
//             }
            //获取商户信息成功后
            if (false != $admininfo){
                $menu=$this->createMenu($admininfo['wechat_appid'],$menus);
                if (is_json($menu)){
                    $arr=json_decode($menu,true);
                    if ($arr['errcode'] != 0){
                        $msg['code']=4003;
                    }else{
                        $msg['code']=200;
                        //$msg['data']=$arr;
                    }
                }else{
                    $msg['code']=4000;//有可能是false，也可能是其它错误
                }
            }else{
                $msg['code']=4002;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    /**
     * @desc  获取自定义菜单
     */
    public function get_menu(){
        $msg=$this->commonerrorcode;
        $key_admin=I('key_admin');
        if (''==$key_admin){
            $msg['code']=4001;
        }else{
            $admininfo=$this->getMerchant($key_admin);//$this->redis->get('wechat:'.$key_admin.':admin');
//             dump($admininfo);
            if (empty($admininfo)){//从数据库里面查询内容
                $admininfo=$this->selectadmin($key_admin);
            }
//             else {//把reids里面的数据转为数组
//                 $admininfo=json_decode($admininfo,true);
//             }
            //获取商户信息成功后
            if (false != $admininfo){
                $menu=$this->getMenu($admininfo['wechat_appid']);
                if (is_json($menu)){
                    $arr=json_decode($menu,true);
                    if (isset($arr['errcode'])){
                        $msg['code']=4003;
                        $msg['data']=$arr;
                    }else{
                        $msg['code']=200;
                        $msg['data']=$arr;
                    }
                }else{
                    $msg['code']=4000;//有可能是false，也可能是其它错误
                }
            }else{
                $msg['code']=4002;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
}

?>