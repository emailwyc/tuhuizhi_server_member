<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 07/12/2017
 * Time: 12:04
 */

namespace EnterpriseWechat\Service;

use Couchbase\Document;

require_cache('Class/Enterprise/lib/msgcrypt.php');


class MsgcryptService
{

    /**
     * 验证URL有效性，解密接收到的echostr字符串，并返回给微信
     * @param $sVerifyMsgSig
     * @param $sVerifyTimeStamp
     * @param $sVerifyNonce
     * @param $sVerifyEchoStr
     * @param $appName
     * @return bool|string
     */
    public static function CallbackValid($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $appName)
    {
//        $sVerifyEchoStr = '2vii34mwZbOkXGv6pk70CGIRSp4/reCV34xcIUv7tRe6Wh+vtpuxN+ZgC1GSyefwd47Bv50RsQzc7s0DorSetw==';
        $appInfo = EnterpriseWechatCommonService::getAppInfoByName($appName);
        if (!$appInfo){
            return false;
        }
        // 需要返回的明文
        $sEchoStr = '';
        $wxcpt = new \MsgCrypt($appInfo['token'], $appInfo['encodingaeskey'], EnterpriseWechatCommonService::$corpId);
        $errCode = $wxcpt->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);

        $log['encryptStr'] = $sVerifyEchoStr;
        $log['appName'] = $appName;
        $log['msgSignature'] = $sVerifyMsgSig;
        $log['timestamp'] = $sVerifyTimeStamp;
        $log['nonce'] = $sVerifyNonce;

        if ($errCode == 0) {
            $log['echostr'] = $sEchoStr;
            writeOperationLog($log, 'enterprisecallbackurl');
            return $sEchoStr;
        } else {
            $log['errorcode'] = $errCode;
            writeOperationLog($log, 'enterprisecallbackurl');
            return false;
        }
    }


    /**
     * 企业接收用户在应用的聊天窗口输入后传递过来的数据
     * @param $encryptStr|接收到的加密密文，file_get_content接收
     * @param $appName|APP名
     * @param $msgSignature|收到的加密签名字符串
     * @param $timestamp|时间戳
     * @param $nonce随机数
     * @return bool
     */
    public static function receiveMessageDecrypt($encryptStr, $appName, $msgSignature, $timestamp, $nonce)
    {
        $appInfo = EnterpriseWechatCommonService::getAppInfoByName($appName);
        if (!$appInfo){
            return false;
        }

        $decryptMsg = '';//定义解密后的密文
        $wxcpt = new \MsgCrypt($appInfo['token'], $appInfo['encodingaeskey'], EnterpriseWechatCommonService::$corpId);
        $errCode = $wxcpt->DecryptMsg($msgSignature, $timestamp, $nonce, $encryptStr, $decryptMsg);

        $log['encryptStr'] = $encryptStr;
        $log['appName'] = $appName;
        $log['msgSignature'] = $msgSignature;
        $log['timestamp'] = $timestamp;
        $log['nonce'] = $nonce;


        if ($errCode == 0) {
            $log['decryptMsg'] = $decryptMsg;
            writeOperationLog($log, 'enterprisereceivemessage');
            // 解密成功，sMsg即为xml格式的明文

            return false;//暂时没用到

            //TODO ... 业务逻辑
            //...
            //...

        }else {
            $log['errcode'] = $errCode;
            writeOperationLog($log, 'enterprisereceivemessage');
            return false;
        }
    }


    /**
     * 企业接收的事件消息
     * @param $encryptStr|接收到的加密密文，file_get_content接收
     * @param $appName|APP名
     * @param $msgSignature|收到的加密签名字符串
     * @param $timestamp|时间戳
     * @param $nonce随机数
     * @return bool
     */
    public static function receiveEventMessageDecrypt($encryptStr, $appName, $msgSignature, $timestamp, $nonce,$appId, $from, $getAll)
    {
        $appInfo = EnterpriseWechatCommonService::getAppInfoByName($appName);
        if (!$appInfo){
            return false;
        }

        $appId = false == $appId ? $appInfo['suiteid'] : $appId;

        $decryptMsg = "";//定义解密后的密文
        $wxcpt = new \MsgCrypt($appInfo['token'], $appInfo['encodingaeskey'], $appId);//最后一个参数是，如果是指令URL调用，则是应用的id，不是企业的id，文档又坑了，数据回调URL是企业URL
        $errCode = $wxcpt->DecryptMsg($msgSignature, $timestamp, $nonce, $encryptStr, $decryptMsg);
        dump($msgSignature);dump($timestamp);dump($nonce);dump($encryptStr);dump($decryptMsg);
dump($errCode);
        $log['from'] = $from;
        $log['encryptStr'] = $encryptStr;
        $log['appName'] = $appName;
        $log['msgSignature'] = $msgSignature;
        $log['timestamp'] = $timestamp;
        $log['nonce'] = $nonce;
        $log['getall'] = $getAll;

//dump($errCode);
        if ($errCode == 0) {
            $log['decryptMsg'] = $decryptMsg;
            writeOperationLog($log, 'enterprisereceivevent');
            // 解密成功，sMsg即为xml格式的明文
            return $decryptMsg;//暂时没用到


            //TODO ... 业务逻辑
            //...
            //...

        }else {
            $log['errcode'] = $errCode;
            writeOperationLog($log, 'enterprisereceivevent');
            return false;
        }
    }
}