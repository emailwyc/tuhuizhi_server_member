<?php
/**
 * 群发消息对外接口（后期可能会增加对内方法）
 */
namespace Thirdwechat\Controller\Wechat;

use Thirdwechat\Controller\Wechat\BulkMessaging\BulkmessagingController;

class BulkmessageController extends BulkmessagingController
{
    
//     /**
//      * 图文消息上传图片
//      */
//     public function uploadimg()
//     {
//         $url='';
//         $appid='wxf3a057928b881466';
//         $data=array('media'=>'@/var/www/html/php-word/project/member/Public/img/a1.jpg');
//         $return=$this->uploadImage($appid, $data, 'image');
//         dump($return);
//     }
    
    
    /**
     * 给公众号所有人发送消息，订阅号服务号认证后都可用
     */
    public function send_message()
        {
        $params['key_admin']=$_GET['key_admin'];
        $sign=$_GET['sign'];
        $jsondata=file_get_contents('php://input');//接收json串
        $checkparams=$this->checkParams($params, $params['key_admin'], $sign);//验证是否正确
        $msg=$this->commonerrorcode;
        if (true == $checkparams){
            $admininfo=$this->getMerchant($params['key_admin']);
            $send_array=json_decode($jsondata,true);
            if (isset($send_array['touser'])){
                $who=2;
            }else if (isset($send_array['filter'])){
                $who=1;
            }
            $return=$this->sendMessage($admininfo['wechat_appid'], $jsondata, $who);//1是给所有人发
            if (is_json($return)){
                $array=json_decode($return, true);
                if (isset($array['errcode']) && 0 == $array['errcode']){
                    $msg['code']=200;
                    $msg['data']=$array;
                }else{
                    $msg['code']=104;
                    $msg['data']=$array;
                }
            }else{
                $msg['code']=101;
            }
        }else{
            $msg['code']=$checkparams;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
}

?>