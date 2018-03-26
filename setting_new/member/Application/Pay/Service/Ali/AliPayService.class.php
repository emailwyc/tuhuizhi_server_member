<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 25/01/2018
 * Time: 18:08
 */

namespace Pay\Service\Ali;


use Pay\Service\PayApiService;

class AliPayService
{
    private static $payUrl = 'http://pay.rtmap.com/pay-api/v3/{platform}/{mchId}/{tradeType}/prepay';
    private static $refundUrl ='http://pay.rtmap.com/pay-api/v3/{platform}/{mchId}/refund';
    private static $orderInfo = 'http://pay.rtmap.com/pay-api/v3/order/{platform}/{mchId}/query';
    private static $AliPayPublicParam = ['total_amount','subject','return_url','notify_url','attach','outtradeno','receipt'];
    private static $aliRefund = ['outtradeno', 'refund_fee', 'refund_account', 'device_info', 'refundfeetype'];
    private static $aliOrderInfo = ['out_trade_no'];

    /**
     * 支付宝支付
     * @param $tradeType | 交易类型，大小写均可：JSAPI、APP、MICROPAY
     * @param $param | 根据前面三个参数，传递相对应的参数值
     */
    public static function Pay($tradeType, $param, $payAccount, $platform)
    {
        if ($tradeType === 'wappay'){
            $check = self::wapPay($param, $payAccount, $tradeType, $platform);
        }else{
            return ['code'=>1051, 'data'=>'alipaytradetypeerror'];
        }

        if ($check === true){
            //签名
            $sign = PayApiService::sign($param, $payAccount['signkey']);
            $param['sign'] = $sign;
            $url = str_replace('{platform}', $platform, self::$payUrl);
            $url = str_replace('{mchId}', $payAccount['mchid'], $url);
            $url = str_replace('{tradeType}', $tradeType, $url);
            $responseData = http($url, json_encode($param), 'POST', ['Content-type: application/json; charset=utf-8'], true, 10);
            //请求返回值为form表单
            return $responseData;
        }else{
            return $check;
        }
    }


    private static function wapPay($param, $payAccount, $tradeType, $platform)
    {
        $paramKeys = array_keys($param);
        $checkParam = array_merge(self::$AliPayPublicParam, $paramKeys);//规定的参数和传入的参数合并数组，如果数量不对，则传入的不对
        $total = array_unique($checkParam);

        if (count($total) > count(self::$AliPayPublicParam)){//判断传入参数个数是否不对
            return ['code'=>1051, 'data'=>'inputparamerror'];
        }
        return true;
    }


    /**
     * 支付宝退款接口
     * @param $platform
     * @param $param
     * @param $payAccount
     * @return array|mixed|string
     */
    public static function alipayRefund($platform, $param, $payAccount)
    {
        $paramKeys = array_keys($param);
        $checkParam = array_merge(self::$aliRefund, $paramKeys);//规定的参数和传入的参数合并数组，如果数量不对，则传入的不对
        $total = array_unique($checkParam);

        if (count($total) > count(self::$aliRefund)){//判断传入参数个数是否不对
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
    public static function aliOrderInfo($platform, $param, $payAccount)
    {
        $paramKeys = array_keys($param);
        $checkParam = array_merge(self::$aliOrderInfo, $paramKeys);//规定的参数和传入的参数合并数组，如果数量不对，则传入的不对
        $total = array_unique($checkParam);

        if (count($total) > count(self::$aliOrderInfo)){//判断传入参数个数是否不对
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