<?php
namespace Oywechat\Controller\Thirdwechat;

use Think\Controller;
use Thirdwechat\Controller\Wechat\Message\WechatPush\TextController;
//use Oywechat\Controller\Wechat\Message\Passive\AutoreplyController;

/**
 * 微信加密解密类
 * @author kaifeng
 *
 */
class EndecryptionController extends Controller
{
    protected $token='oya_open_wechat';//第三方平台申请时填写的接收消息的校验token
    protected $encodingAesKey='2a2ba460c451ca4bc459ff6d9eae6a9dc451ca4bc45';//第三方平台申请时填写的接收消息的加解密symmetric_key
    protected $appId='wx5a529bc337fe1d6f';//公众号第三方平台的appid
    protected $textarray=array('ToUserName','FromUserName','CreateTime','MsgType','Content','MsgId');//text消息类型的xml结构体
    
    protected $imagearray=array('ToUserName','FromUserName','CreateTime','MsgType','PicUrl','MediaId','MsgId');//image消息类型的xml结构体
    
    protected $voicearray=array('ToUserName','FromUserName','CreateTime','MsgType','MediaId','Format','Recognition','MsgId');//voice消息类型的xml结构体，Recognition不一定有（语音识别结果，UTF8编码）
    
    protected $videoarray=array('ToUserName','FromUserName','CreateTime','MsgType','MediaId','ThumbMediaId','MsgId');//video消息类型的xml结构体
    
    protected $shortvideoarray=array('ToUserName','FromUserName','CreateTime','MsgType','MediaId','ThumbMediaId','MsgId');//短视频消息类型的xml结构体
    
    protected $locationarray=array('ToUserName','FromUserName','CreateTime','MsgType','MediaId','Location_X','Location_Y','Scale','Label','MsgId');//地理位置消息类型的xml结构体
    
    protected $linkarray=array('ToUserName','FromUserName','CreateTime','MsgType','Title','Description','Url','MsgId');//链接消息类型的xml结构体
    
    protected $event=array('ToUserName','FromUserName','CreateTime','MsgType','Event','EventKey','Ticket','Latitude','Longitude','Precision');//有些没有
    /**
     * 微信xml解密
     * @param unknown $xml
     * @param unknown $appid
     */
    public function decryption($msgSignature, $timestamp, $nonce, $postData, $appid, $get_data)
    {
        //引入微信提供的解密类
        require_once 'Class/Wechatthird/wxBizMsgCrypt.php';
        $wx=new \WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appId);
        
        //开始解密
        $msg='';
        $decryptmsg=$wx->decryptMsg($msgSignature, $timestamp, $nonce, $postData, $msg);
        if ($decryptmsg == 0) {
            
            $xml = new \DOMDocument();
            $xml->loadXML($msg);
            
            //获取MsgType，根据MsgType处理消息
            $array_e = $xml->getElementsByTagName('MsgType');
            $type = $array_e->item(0)->nodeValue;
            switch ($type){
                case 'text':
                    $array=$this->textarray;
                    break;
                case 'image':
                    $array=$this->imagearray;
                    break;
                case 'voice':
                    $array=$this->voicearray;
                    break;
                case 'video':
                    $array=$this->videoarray;
                    break;
                case 'shortvideo':
                    $array=$this->shortvideoarray;
                    break;
                case 'location':
                    $array=$this->locationarray;
                    break;
                case 'link':
                    $array=$this->linkarray;
                    break;
                case 'event':
                    $array=$this->event;
                    break;
                default:
                    echostr('success');
                    break;
            }
            
            $return=array();
            if ('event' != $type){
                foreach ($array as $v){
                    //获取MsgType，根据MsgType处理消息
                    $array_e = $xml->getElementsByTagName($v);
                    $return[$v] = $array_e->item(0)->nodeValue;
                }
            }else {
                $xpath=new \DOMXPath($xml);
                foreach ($array as $v){
                    $tmp=$xpath->query($v)->item(0);
                    if (null == $tmp){//如果没有有这个节点，跳出此次循环
                        continue;
                    }else{//如果有这个节点，获取这个节点的内容
                        //获取MsgType，根据MsgType处理消息
                        $array_e = $xml->getElementsByTagName($v);
                        $return[$v] = $array_e->item(0)->nodeValue;
                    }
                }
            }
            
            writeOperationLog(array('msgSignature'=>$msgSignature,'postData'=>$postData,'appid'=>$appid,'xmlcontent'=>$msg,'xmlkeys'=>$return),'xmldecryption');
            $autoreplay=new AutoreplyController();
            $autoreplay->messageType($type, $return, $get_data, $msg);
            //暂时输出success
            //echo 'success';
        } else {
            echostr('success');
        }
    }
    
    
    
    public function encryption($xml, $timestamp, $nonce) {
        //引入微信提供的解密类
        require_once 'Class/Wechatthird/wxBizMsgCrypt.php';
        $wx=new \WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appId);
        $msg='';
        $decryptmsg=$wx->encryptMsg($xml, $timestamp, $nonce, $msg);
        return $msg;
    }
    
    
    
//     /**
//      * 根据消息类型，解析xml
//      * @param unknown $xml
//      */
//     public function decodexml($xml, $type)
//     {
//         //获取对应类型的数组
//         if ('text'==$type){
//             $array=$this->textarray;
//         }elseif ('image'==$type){
//             $array=$this->textarray;
//         }elseif ('voice'==$type){
//             $array=$this->voicearray;
//         }elseif ('video'==$type){
//             $array=$this->videoarray;
//         }elseif ('shortvideo'==$type){
//             $array=$this->shortvideoarray;
//         }elseif ('location'==$type){
//             $array=$this->locationarray;
//         }elseif ('link'==$type){
//             $array=$this->linkarray;
//         }else{
//             echo 'success';exit;
//         }
//         $return=null;
//         foreach ($array as $v){
//             //获取MsgType，根据MsgType处理消息
//             $array_e = $xml->getElementsByTagName($v);
//             $return[$v] = $array_e->item(0)->nodeValue;
//         }
//         return $return;
//     }
}

?>