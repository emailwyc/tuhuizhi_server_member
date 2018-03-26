<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 25/01/2018
 * Time: 15:29
 */

namespace Pay\Service\Wechat;


use Pay\Service\PayApiService;

class WechatPayService
{
    private static $payUrl = 'http://pay.rtmap.com/pay-api/v3/{platform}/{mchId}/{tradeType}/prepay';
    private static $refundUrl ='http://pay.rtmap.com/pay-api/v3/{platform}/{mchId}/refund';
    private static $orderInfo = 'http://pay.rtmap.com/pay-api/v3/order/{platform}/{mchId}/query';
    private static $wechatPayJsapiPublicParam = ['total_fee','body','notify_url','attach','attach_transmit_tag','device_info','detail','fee_type','goods_tag','outtradeno','receipt','openid','appid','wxa_tag'];
    private static $wechatPayAppPublicParam = ['total_fee','body','notify_url','attach','attach_transmit_tag','device_info','detail','fee_type','goods_tag','outtradeno','receipt','appid'];
    private static $wechatPayMicroPublicParam = ['total_fee','body','notify_url','attach','attach_transmit_tag','device_info','detail','fee_type','goods_tag','outtradeno','receipt','auth_code'];
    private static $wechatRefund = ['outtradeno', 'refund_fee', 'refund_account', 'device_info', 'refundfeetype'];
    private static $wechatOrderInfo = ['out_trade_no'];

    /**
     * 微信支付
     * @param $tradeType | 交易类型，大小写均可：JSAPI、APP、MICROPAY
     * @param $param | 根据前面三个参数，传递相对应的参数值
     */
    public static function wechatPay($tradeType, $param, $payAccount, $platform)
    {
        $tradeType = strtolower($tradeType);
        if ($tradeType === 'jsapi'){
            $check = self::wechatJsapiPay($param, $payAccount, $tradeType, $platform);
        }elseif ($tradeType === 'app'){
            $check = self::wechatAppPay($param, $payAccount, $tradeType, $platform);
        }elseif ($tradeType === 'micropay'){
            $check = self::wechatMicroPay($param, $payAccount, $tradeType, $platform);
        }else{
            return ['code'=>1051, 'data'=>'wechattradetypeerror'];
        }

        if ($check === true){
            //签名
            $sign = PayApiService::sign($param, $payAccount['signkey']);
            $param['sign'] = $sign;
            $url = str_replace('{platform}', $platform, self::$payUrl);
            $url = str_replace('{mchId}', $payAccount['mchid'], $url);
            $url = str_replace('{tradeType}', $tradeType, $url);
            $responseData = http($url, json_encode($param), 'POST', ['Content-type: application/json; charset=utf-8'], true, 10);
            $responseData = json_decode($responseData, true);
            if (is_array($responseData)){
                return $responseData;
            }else{
                return ['code'=>101];
            }
        }else{
            return $check;
        }
    }


    /**
     * 微信h5支付
     * @param $mchId
     * @param $param
     */
    private static function wechatJsapiPay($param, $payAccount, $tradeType, $platform)
    {
        $paramKeys = array_keys($param);
        $checkParam = array_merge(self::$wechatPayJsapiPublicParam, $paramKeys);//规定的参数和传入的参数合并数组，如果数量不对，则传入的不对
        $total = array_unique($checkParam);

        if (count($total) > count(self::$wechatPayJsapiPublicParam)){//判断传入参数个数是否不对
            return ['code'=>1051, 'data'=>'inputparamerror'];
        }

        return true;
    }


    /**
     * 微信APP支付
     * @param $mchId
     * @param $param
     */
    private static function wechatAppPay($param, $payAccount, $tradeType, $platform)
    {
        $paramKeys = array_keys($param);
        $checkParam = array_merge(self::$wechatPayAppPublicParam, $paramKeys);//规定的参数和传入的参数合并数组，如果数量不对，则传入的不对
        $total = array_unique($checkParam);

        if (count($total) > count(self::$wechatPayAppPublicParam)){//判断传入参数个数是否不对
            return ['code'=>1051, 'data'=>'inputparamerror'];
        }

        return true;


    }


    /**
     * 被扫码支付
     * @param $mchId
     * @param $param
     */
    private static function wechatMicroPay($param, $payAccount, $tradeType, $platform)
    {
        $paramKeys = array_keys($param);
        $checkParam = array_merge(self::$wechatPayMicroPublicParam, $paramKeys);//规定的参数和传入的参数合并数组，如果数量不对，则传入的不对
        $total = array_unique($checkParam);

        if (count($total) > count(self::$wechatPayMicroPublicParam)){//判断传入参数个数是否不对
            return ['code'=>1051, 'data'=>'inputparamerror'];
        }

        return true;
    }


    /**
     * 微信退款方法
     * @param $platform
     * @param $param
     * @param $payAccount
     * @return array|mixed|string
     */
    public static function wechatRefund($platform, $param, $payAccount)
    {
        $paramKeys = array_keys($param);
        $checkParam = array_merge(self::$wechatRefund, $paramKeys);//规定的参数和传入的参数合并数组，如果数量不对，则传入的不对
        $total = array_unique($checkParam);

        if (count($total) > count(self::$wechatRefund)){//判断传入参数个数是否不对
            return ['code'=>1051, 'data'=>'inputparamerror'];
        }
        //签名
        $sign = PayApiService::sign($param, $payAccount['signkey']);
        $param['sign'] = $sign;
        $url = str_replace('{platform}', $platform, self::$refundUrl);
        $url = str_replace('{mchId}', $payAccount['mchid'], $url);
        $responseData = http($url, json_encode($param), 'POST', ['Content-type: application/json; charset=utf-8'], true, 10);
        $responseData = json_decode($responseData, true);
        if (is_array($responseData)){
            return $responseData;
        }else{
            return ['code'=>101];
        }
    }


    /**
     * 微信查询订单详情
     * @param $platform
     * @param $param
     * @param $payAccount
     * @return array|mixed|string
     * @throws \Exception
     */
    public static function wechatOrderInfo($platform, $param, $payAccount)
    {
        $paramKeys = array_keys($param);
        $checkParam = array_merge(self::$wechatOrderInfo, $paramKeys);//规定的参数和传入的参数合并数组，如果数量不对，则传入的不对
        $total = array_unique($checkParam);

        if (count($total) > count(self::$wechatOrderInfo)){//判断传入参数个数是否不对
            return ['code'=>1051, 'data'=>'inputparamerror'];
        }
        //签名
        $sign = PayApiService::sign($param, $payAccount['signkey']);
        $param['sign'] = $sign;
        $url = str_replace('{platform}', $platform, self::$orderInfo);
        $url = str_replace('{mchId}', $payAccount['mchid'], $url);
        $responseData = http($url, json_encode($param), 'POST', ['Content-type: application/json; charset=utf-8'], true, 10);
        $responseData = json_decode($responseData, true);
        if (is_array($responseData)){
            return $responseData;
        }else{
            return ['code'=>101];
        }
    }



}