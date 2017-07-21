<?php
/**
 * 微信模板消息
 */
namespace Thirdwechat\Controller\Wechat\Message\Tmplate;

use Thirdwechat\Controller\Thirdwechat\EventsController;
use Thirdwechat\Controller\Wechat\WechatcommonController;

class WechattemplateController extends WechatcommonController
{
    
    /**
     * 设置所属行业
     * @param unknown $param
     */
    public function set_industry()
    {
        $url=$this->set_industry;
    }
    
    /**
     * 获取设置的行业信息
     */
    public function get_industry()
    {
        
    }
    
    /**
     * 获取模板id
     * @param unknown $param
     */
    public function get_template($param)
    {
        
    }
    
    
    /**
     * 获取模板列表
     */
    public function get_all_private_template($appid)
    {
        $events=new EventsController();
        $authorizer_access_token=$events->authorizer_access_token($appid);
        if (false==$authorizer_access_token){
            return false;
        }else{
            //执行业务逻辑
            $url=$this->get_all_private_template.$authorizer_access_token;
            $return=curl_https($url,array(),array(),30,true);
            return $return;
        }
    }
    
    /**
     * 发送模板消息
     * 为了兼容批量发送，全部数据都用二维数组方式传递
     * @param array $data
     */
    public function send_message(array $data=array(),$appid)
    {
        $events=new EventsController();
        $authorizer_access_token=$events->authorizer_access_token($appid);
        if (false==$authorizer_access_token){
            return false;
        }else{
            //暂时用foreach循环
            $return=array();
            foreach ($data as $key => $val) {
                $url=$this->send_message_template.$authorizer_access_token;
                $curl=curl_https($url,json_encode($val),array(),30,true);
                $arr=json_decode($curl, true);
                if (!isset($arr['errcode']) || 0 != $arr['errcode']){
                    $return[]=array('openid'=>$val['touser'],'errcode'=>$arr['errcode']);
                }
            }
            return $return;
        }
    }
}

?>