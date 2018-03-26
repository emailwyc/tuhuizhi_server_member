<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 05/12/2017
 * Time: 13:56
 */

namespace Thirdwechat\Service\OpenWechat\Message;


use Common\Service\RedisService;
use Thirdwechat\Controller\Thirdwechat\EndecryptionController;

class AutoReplayInputMessageService
{

    public static function replayMessage($data, $get_data, $type, $appidInfo)
    {
        if ('openid' == trim(strtolower($data['Content']))){
            $data = self::textMessage($data, $get_data, $type, $appidInfo, null);
        }else{
            //如果有值，则之前读过库，没有任何东西，直接返回false
            $isReplayList = RedisService::connectredis()->get('wechat:'.$get_data['appid'] .':' . $type . ':auto_reply:isreplay');
            if ($isReplayList){
                return false;
            }
            //回复列表
            $replayList = RedisService::connectredis()->get('wechat:'.$get_data['appid'] .':'. $type .':auto_reply:list');
            if (!$replayList){
                $m = M();
                //由于历史原因，有些没有表，且微信不允许返回错误，必须检查表
                $c=$m->execute('SHOW TABLES like "'.$appidInfo['pre_table'].'wechat_auto_reply"');
                if ($c == false){
                    RedisService::connectredis()->set('wechat:'.$get_data['appid'] .':' . $type . ':auto_reply:isreplay', 'yes', ['ex'=>86400]);//如果连表都没有，则直接加redis返回
                    return false;
                }
                $db = M($appidInfo['pre_table'].'wechat_auto_reply');
                $replayList = $db->where(['type'=>$type])->select();
                if (!$replayList){
                    RedisService::connectredis()->set('wechat:'.$get_data['appid'] .':' . $type . ':auto_reply:isreplay', 'yes', ['ex'=>86400]);//如果没有数据，则直接加redis返回
                    return false;
                }else{
                    RedisService::connectredis()->set('wechat:'.$get_data['appid'] .':'. $type .':auto_reply:list', json_encode($replayList), ['ex'=>86400]);
                }
            }else{
                $replayList = json_decode($replayList, true);
            }

            if ($type == 'text'){
                $data = self::textMessage($data, $get_data, $type, $appidInfo, $replayList);
            }else{
                $data =false;
            }
        }


        return $data;
    }


    protected static function textMessage($data, $getData, $type, $appidInfo, $autoReply)
    {
        $msg=false;
        if ('openid' == trim(strtolower($data['Content'])) ){
            $msg=$data['FromUserName'];
        }else{
            //从自动回复列表中查看对应的回复
            if (false != $autoReply){
                foreach ($autoReply as $key => $val){
                    if ($val['name'] == $data['Content']){
                        $msg=false != $val['message'] ? $val['message'] : '您好，欢迎光临。';
                        break;
                    }
                }
            }
            //如果自动回复里面没有设置这一条，或着商户没有设置自动回复
            if (false == $msg || false == $autoReply){
                $tuling_api_url=C('TULING_API_URL');
                $tuling_api_key=C('TULING_API_KEY');
                $array=array('key'=>$tuling_api_key,'info'=>$data['Content']);
                $header=array('Content-Type:application/json;charset=UTF-8');
                $return=http($tuling_api_url, json_encode($array), 'POST', $header, true);
                if (is_json($return)){
                    $returnarray=json_decode($return, true);
                    if ($returnarray['code'] == 100000){
                        $msg=$returnarray['text'];
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }
        }
        if (false == $msg){
            return false;
        }

        $timestamp=time();
        $xml='<xml><ToUserName><![CDATA['.$getData['openid'].']]></ToUserName><FromUserName><![CDATA['.$data['ToUserName'].']]></FromUserName><CreateTime>'.$timestamp.'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.$msg.']]></Content></xml>';
        writeOperationLog(array('str'=>$xml),'sendmessage');
        $endecryption= new EndecryptionController();
        $encryptionmsg=$endecryption->encryption($xml, $timestamp, $getData['nonce']);

        return $encryptionmsg;
    }
}