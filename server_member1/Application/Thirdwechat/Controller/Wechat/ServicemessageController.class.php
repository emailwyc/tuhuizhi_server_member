<?php
/**
 * 客服消息对外接口类
 */
namespace Thirdwechat\Controller\Wechat;

use Thirdwechat\Controller\Wechat\Message\Service\WechatserviceController;

class ServicemessageController extends WechatserviceController
{
    
    /**
     * 发送客服消息，因为客服消息有条数限制，所以不提供批量发送
     */
    public function send_service_message()
    {
        $send=file_get_contents('php://input');
        $key_admin=$_GET['key_admin'];
        $sign=$_GET['sign'];
        $msg=$this->commonerrorcode;
        if (false==$send || empty($key_admin) || !is_json($send)){
            $msg['code']=100;
        }else{
            $admininfo=$this->getMerchant($key_admin);
            //验证签名
            $params=array('key_admin'=>$key_admin,'sign_key'=>$admininfo['signkey']);
            if (sign($params) != $sign){
                $msg['code']=1002;
            }else{
                if (!isset($admininfo['wechat_appid']) || empty($admininfo['wechat_appid'])){
                    $msg['code']=102;
                }else{
                    $array=json_decode($send,true);
                    if (isset($array['touser']) || 10000 > count($array)){
                        $return=$this->sendServiceMessage($send,$admininfo['wechat_appid']);
                        if (is_json($return)){
                            $returnarray=json_decode($return,true);
                            if (isset($returnarray['errcode']) && 0 != $returnarray['errcode']){
                                $msg['code']=104;
                                $msg['data']=$returnarray;
                            }else{
                                $msg['code']=200;
                            }
                        }else{
                            $msg['code']=101;
                        }
                    }else{
                        $msg['code']=1042;
                    }
                }
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }


    /**
     * 发送客服消息，因为客服消息有条数限制，所以不提供批量发送，仅供内部php调用
     * $send array
     * $appid string
     */
    public function inside_send_service_message($send, $appid)
    {
//        $send=file_get_contents('php://input');
//        $key_admin=$_GET['key_admin'];
//        $sign=$_GET['sign'];
        $msg=$this->commonerrorcode;
        if (false==$send || empty($appid)){
            $msg['code']=100;
        }else{
//            $admininfo=$this->getMerchant($key_admin);
            //验证签名
//            $params=array('key_admin'=>$key_admin,'sign_key'=>$admininfo['signkey']);
//            if (sign($params) != $sign){
//                $msg['code']=1002;
//            }else{
//                if (!isset($admininfo['wechat_appid']) || empty($admininfo['wechat_appid'])){
//                    $msg['code']=102;
//                }else{
//                    $array=json_decode($send,true);
                    $send=json_encode_chinese($send);
                    if (isset($send['touser'])){
                        $return=$this->sendServiceMessage($send,$appid);
                        if (is_json($return)){
                            $returnarray=json_decode($return,true);
                            if (isset($returnarray['errcode']) && 0 != $returnarray['errcode']){
                                $msg['code']=104;
                                $msg['data']=$returnarray;
                            }else{
                                $msg['code']=200;
                            }
                        }else{
                            $msg['code']=101;
                        }
                    }else{
                        $msg['code']=1042;
                    }
//                }
//            }
        }
//        echo returnjson($msg,$this->returnstyle,$this->callback);
        $msg['send']=$send;
        writeOperationLog($msg, 'testtest');
    }
}

?>