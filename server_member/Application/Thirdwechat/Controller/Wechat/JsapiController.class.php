<?php
namespace Thirdwechat\Controller\Wechat;


use Thirdwechat\Controller\Thirdwechat\EventsController;
use Org\Util\String;
/**
 * @desc    微信第三方平台jsapi接口
 * @author zhangkaifeng
 *
 */
class JsapiController extends WechatcommonController{
    // TODO - Insert your code here
    public function get_js_sdk() {
        $key_admin=I('key_admin');
        $url=I('url');
        $msg=$this->commonerrorcode;
        if (null == $key_admin || null == $url){
            $msg['code']=100;
        }else {
            $admininfo=$this->getMerchant($key_admin);
            $jsapi_ticket=$this->getJsapiTicket($admininfo['wechat_appid']);
            $string=new String();
            $noncestr=$string->randString(20,3);
            $timestamp=time();
            $url=htmlspecialchars_decode(urldecode($url));
            if (false != $jsapi_ticket ){//如果
                    $jsapi_sdk=$this->setJsapiticketSign($jsapi_ticket, $noncestr, $timestamp, $url, $admininfo['wechat_appid']);
                    $msg['code']=200;
                    $re=array(
                        'appId'=>$admininfo['wechat_appid'],
                        'timestamp'=>$timestamp,
                        'nonceStr'=>$noncestr,
                        'signature'=>$jsapi_sdk,
                        'url'=>$url,
                        'ticket'=>$jsapi_ticket
                    );
                    $msg['data']=$re;//array('appId'=>$admininfo['wechat_appid'],'timestamp'=>$timestamp,'nonceStr'=>$noncestr,'signature'=>$jsapi_sdk,'url'=>$url);
            }else{
                $msg['code']=1000;
            }
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit;
    }
    
    
    /**
     * 获取jsapi_ticket
     * @param unknown $appid
     */
    private function getJsapiTicket($appid){
        $jsapi_ticket=$this->redis->get('wechat:jsapi_ticket:'.$appid);
        if ($jsapi_ticket){
            return $jsapi_ticket;
        }else{
            $events=new EventsController();
            $authorizer_access_token=$events->authorizer_access_token($appid);
            if (false==$authorizer_access_token){
                return false;
            }else{
                $url=$this->get_jsapi_ticket;
                $url=str_replace('[ACCESS_TOKEN]',$authorizer_access_token,$url);
                $resutl=curl_https($url);
                if (is_json($resutl)){
                    $resutl=json_decode($resutl,true);
                    if (0==$resutl['errcode']){
                        $this->redis->set('wechat:jsapi_ticket:'.$appid,$resutl['ticket'],$resutl['expires_in']);
                        return $resutl['ticket'];
                    }else {
                        return false;
                    }
                }else{
                    return false;
                }
            }
        }
    }
    
    /**
     * 生成证书
     * @param unknown $jsapi_ticket
     * @param unknown $noncestr
     * @param unknown $timestamp
     * @param unknown $url
     */
    private function setJsapiticketSign($jsapi_ticket,$noncestr,$timestamp,$url, $appid) {//
        $str='jsapi_ticket='.$jsapi_ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;
        $data=array(
            'jsapi_ticket'=>$jsapi_ticket,
            'noncestr'=>$noncestr,
            'timestamp'=>$timestamp,
            'url'=>$url
        );
        $data['str']=$str;
        $data['jsapi_ticket_signs']=sha1($str);
        
        writeOperationLog($data,'jsapidata');
        return  $data['jsapi_ticket_signs'];
    }
}

?>