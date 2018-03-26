<?php
/**
 * 事件自动回复
 * Created by PhpStorm.
 * User: zhang
 * Date: 17/10/2017
 * Time: 15:36
 */

namespace Thirdwechat\Service\OpenWechat\Message;


use Common\Service\RedisService;
use Thirdwechat\Controller\Thirdwechat\EndecryptionController;

class AutoReplayEventMessageService
{

    /**
     * @param $FromUserName 文档上面写的是"开发者微信号"，这里填的是公众号原始id
     * @param $replayMsgType 要回复的消息类型
     * @param $toUser 要发送给谁
     * @param $adminInfo 商户基本信息
     * @param $appid 接收到的appid字符串
     * @param $eventType 接收到的事件类型
     * @param $getData 从URL链接获取到的$_GET信息
     * @return bool|string
     */
    public static function eventMessage($FromUserName, $replayMsgType, $toUser, $adminInfo, $appid, $eventType, $getData)
    {
        $messageValue = RedisService::connectredis(1)->get('wechat:'.$eventType.':message:type:content:'.$appid);
        if ($messageValue){
            $msgContent=json_decode($messageValue, true);
            if (!is_array($msgContent)){
                return false;
            }
        }else{
            $db = M('event_message', $adminInfo['pre_table']);
            $msgContent = $db->where(array('message_event_type'=>$eventType))->select();
            if (!$msgContent) {
                return false;
            }
            RedisService::connectredis(1)->set('wechat:'.$eventType.':message:type:content:'.$appid,json_encode($msgContent), array('ex'=>86400));
        }
//wechat:subscribe:message:type:content:wxf3a057928b881466
        //暂时只用到图文和文本，其他的用到时再加
        if ($replayMsgType == 'news'){
            $xml = self::newsMessage($msgContent, $toUser, $FromUserName);
        }elseif($replayMsgType == 'text'){
            $xml = self::textMessage($msgContent[0]['description'], $toUser, $FromUserName);
        }else{
            return false;
        }

        $endecryption= new EndecryptionController();
        $encryptionmsg=$endecryption->encryption($xml, time(), $getData['nonce']);
        return $encryptionmsg;
    }


    /**
     * 如果设置自动回复为图文消息
     * @param $msgContent array ,消息内容
     * @param $toUser string openid，发给谁
     * @param $FromUserName string 文档上面写的是"开发者微信号"，这里填的是公众号原始id
     * @return bool|string
     */
    protected static function newsMessage($msgContent, $toUser, $FromUserName)
    {
        $count = count($msgContent);
        if ($count > 8){
            return false;
        }
        $article = '';
        foreach ($msgContent as $key => $value) {
            $article .= '<item><Title><![CDATA[' .$value['title']. ']]></Title><Description><![CDATA[' .$value['description']. ']]></Description><PicUrl><![CDATA[' .$value['picurl']. ']]></PicUrl><Url><![CDATA[' .$value['url']. ']]></Url></item>';
        }
        $xml = '<xml><ToUserName><![CDATA[' .$toUser. ']]></ToUserName><FromUserName><![CDATA[' .$FromUserName. ']]></FromUserName><CreateTime>' .time(). '</CreateTime><MsgType><![CDATA[news]]></MsgType><ArticleCount>'.$count.'</ArticleCount><Articles>'.$article.'</Articles></xml>';
        return $xml;
    }

    /**
     * 如果设置自动回复为文本消息
     * @param $msgContent array ,消息内容
     * @param $toUser string openid，发给谁
     * @param $FromUserName string 文档上面写的是"开发者微信号"，这里填的是公众号原始id
     * @return string
     */
    protected static function textMessage($msgContent, $toUser, $FromUserName)
    {
        $xml = '<xml><ToUserName><![CDATA[' .$toUser. ']]></ToUserName><FromUserName><![CDATA[' .$FromUserName. ']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.$msgContent.']]></Content></xml>';
        return $xml;
    }
}