<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 16/01/2018
 * Time: 16:31
 */

namespace Pay\Service;



use Common\Service\PublicService;
use Pay\Service\Ali\AliPayService;
use Pay\Service\Wechat\WechatPayService;

class PayApiService
{


    /**
     * 公共支付接口
     * @param $platform | 支付平台 wechat、alipay
     * @param $tradeType | 交易类型，大小写均可：微信填JSAPI、APP、MICROPAY，支付宝填wappay
     * @param $param | 根据前面三个参数，传递相对应的参数值
     * @param $modular | 使用模块，如停车:park，券商城：coupon，公共：public,其他:other
     */
    public static function requestOrder($platform, $tradeType, $param,$keyAdmin, $modular)
    {
        //判断参数是否完整
        if ($platform == false || $tradeType == false || !is_array($param)){
            return ['code'=>1004];
        }

        $adminInfo = PublicService::getMerchant($keyAdmin);
        if (isset($adminInfo['code'])){
            return $adminInfo;
        }

        //为方便判断，交易类型转小写
        $tradeType = strtolower($tradeType);
        if ($platform === 'wechat'){
            $payAccount = self::getPayAccountByWechat($modular, $adminInfo, $platform);
            if (isset($payAccount['code'])){//查询账号出错
                return $payAccount;
            }elseif (is_array($payAccount)){//返回的账号id和秘钥
                $platform = 'wx';
                $data = WechatPayService::wechatPay($tradeType, $param, $payAccount, $platform);
            }else{//如果是第三方支付
                $data = $payAccount::requestOrder();
            }

        }elseif($platform === 'alipay'){
            $payAccount = self::getPayAccountByAlipay($modular, $adminInfo);
            if (isset($payAccount['code'])){
                return $payAccount;
            }
            $data = AliPayService::Pay($tradeType, $param, $payAccount, $platform);
        }else{
            return ['code'=>1051, 'data'=>'platformerror'];
        }
        return $data;
    }


    /**
     * 退款接口——2018-01-25开发，无订单供测试，故可能会出错
     * @param $platform | 支付平台 wx、alipay
     * @param $keyAdmin 商户key
     * @param $modular | 使用模块，如停车:park，券商城：coupon
     * @param $param | 退款参数
     * @return array|bool|mixed|null|string
     */
    public static function refund($platform, $keyAdmin,$modular, $param)
    {
        if ($platform == false || $keyAdmin == false){
            return ['code'=>1004];
        }
        $adminInfo = PublicService::getMerchant($keyAdmin);
        if (isset($adminInfo['code'])){
            return $adminInfo;
        }
        if ($platform === 'wechat'){
            $payAccount = self::getPayAccountByWechat($modular, $adminInfo, $platform);
            if (isset($payAccount['code'])){
                return $payAccount;
            }elseif (is_array($payAccount)){
                $platform = 'wx';
                return WechatPayService::wechatRefund($platform, $param, $payAccount);
            }else{
                return $payAccount::refund();
            }

        }elseif($platform === 'alipay'){
            $payAccount = self::getPayAccountByAlipay($modular, $adminInfo);
            if (isset($payAccount['code'])){
                return $payAccount;
            }
            return AliPayService::alipayRefund($platform, $param, $payAccount);
        }else{
            return ['code'=>1051, 'data'=>'platformerror'];
        }
    }


    /**
     * 订单查询
     * @param $platform
     * @param $keyAdmin
     * @param $modular
     * @param $param
     * @return array|bool|mixed|null|string
     */
    public static function queryOrder($platform, $param, $keyAdmin, $modular)
    {
        if ($platform == false || $keyAdmin == false){
            return ['code'=>1004];
        }
        $adminInfo = PublicService::getMerchant($keyAdmin);
        if (isset($adminInfo['code'])){
            return $adminInfo;
        }
        if ($platform === 'wechat'){
            $payAccount = self::getPayAccountByWechat($modular, $adminInfo, $platform);
            if (isset($payAccount['code'])){
                return $payAccount;
            }elseif (is_array($payAccount)) {
                $platform = 'wx';
                return WechatPayService::wechatOrderInfo($platform, $param, $payAccount);
            }else{
                return $payAccount::queryOorder();
            }
        }elseif($platform === 'alipay'){
            $payAccount = self::getPayAccountByAlipay($modular, $adminInfo);
            if (isset($payAccount['code'])){
                return $payAccount;
            }
            return AliPayService::aliOrderInfo($platform, $param, $payAccount);
        }else{
            return ['code'=>1051, 'data'=>'platformerror'];
        }
    }












    /**
     * 获取微信支付平台账号密码
     * @param $modular
     * @param $keyAdmin
     * @return bool|mixed|null
     */
    private static function getPayAccountByWechat($modular, $adminInfo, $platform = false)
    {
        //公共支付账号
        $publiAccount =  PublicService::GetOneAmindefaul($adminInfo, 'public_pay_config');
        $publiAccount = json_decode($publiAccount['function_name'], true);
        if (!is_array($publiAccount) || $publiAccount['publicmchid'] == false || $publiAccount['publicsignkey'] == false){
            $publicAccountStatus = false;
        }else{
            $publiAccount = [
                'mchid'=>$publiAccount['publicmchid'],
                'signkey'=>$publiAccount['publicsignkey']
            ];
            $publicAccountStatus = true;
        }

        $isPlatformPay = self::checkPayFunction($adminInfo, $modular, $platform);
        //智慧图支付账号
        if ($isPlatformPay === true){
            //获取某个模块下的支付账号
            if ($modular == 'public') {
                if ($publicAccountStatus == true){
                    return $publiAccount;
                }else{
                    return ['code'=>102, 'data'=>'payaccounterror'];
                }
            }else if ($modular == 'park') {
                $payAccount = PublicService::GetOneAmindefaul($adminInfo, 'park_pay_config');
                $payAccount = json_decode($payAccount['function_name'], true);
                //如果json解析失败，或者配置错误，则用公共配置
                if (!is_array($payAccount) || $payAccount['mchid'] == false || $payAccount['signkey'] == false){
                    if ($publicAccountStatus == true){
                        return $publiAccount;
                    }else{
                        return ['code'=>102, 'data'=>'payaccounterror'];
                    }
                }else{
                    return $payAccount;
                }
            }elseif ($modular == 'other'){
                $payAccount = PublicService::GetOneAmindefaul($adminInfo, 'other_pay_config');
                $payAccount = json_decode($payAccount['function_name'], true);
                //如果json解析失败，或者配置错误，则用公共配置
                if (!is_array($payAccount) || $payAccount['othermchid'] == false || $payAccount['othersignkey'] == false){
                    if ($publicAccountStatus == true){
                        return $publiAccount;
                    }else{
                        return ['code'=>102, 'data'=>'payaccounterror'];
                    }
                }else{

                    $payAccount = [
                        'mchid'=>$payAccount['othermchid'],
                        'signkey'=>$payAccount['othersignkey']
                    ];
                    return $payAccount;
                }
            }elseif ($modular == 'coupon'){
                if ($publicAccountStatus == true){
                    return $publiAccount;
                }else{
                    return ['code'=>102, 'data'=>'payaccounterror'];
                }
            }else{
                return ['code'=>1051, 'data'=>'modularerror'];
            }
        }else{//返回支付方式结果，两种：错误和第三方支付
            return $isPlatformPay;
        }

    }


    /**
     * 获取支付是智慧图平台支付还是第三方支付
     * @param $adminInfo
     * @param $modular
     * @return array|bool|mixed|null
     */
    private static function checkPayFunction ($adminInfo, $modular, $platform)
    {
        if($modular=="other") {
            $payconfig = PublicService::GetOneAmindefaul($adminInfo, "other_pay_config");
            $payconfig = json_decode($payconfig,true);
            if (empty($payconfig) || $payconfig['function_name']["otherplatform"] == '0') {
                return true;
            }else{
                if (empty($payconfig['function_name']["otherpfclass"])) {
                    return ['code' => 113, 'nopayfunction'];
                } else {
                    return $payconfig['function_name']["otherpfclass"];
                }
            }
        }else {
            $modular = $platform . '-pay-' . $modular;
            //是否是平台或第三方支付
            $payPlatform = PublicService::GetOneAmindefaul($adminInfo, $modular);
            //如果没有配置或是配置值为0，则调用智慧图平台支付
            if ($payPlatform == false || $payPlatform['function_name'] === '0') {
                return true;
            } else {
                //查询支付类型第三方支付的类名，对$modular格式有要求：wechat-pay-public、wechat-pay-park-classname，后台存库的时候，要存这样的key
                $className = PublicService::GetOneAmindefaul($adminInfo, $modular . '-classname');
                if ($className == false) {
                    return ['code' => 113, 'nopayfunction'];
                } else {
                    return $className['function_name'];
                }
            }
        }
    }


    /**
     * 获取支付宝支付账号，没有其他功能，直接读alipay_payconf
     * @param $modular
     * @param $keyAdmin
     */
    private static function getPayAccountByAlipay($modular, $adminInfo)
    {
        //公共支付账号
        $publiAccount =  PublicService::GetOneAmindefaul($adminInfo, 'alipay_payconf');
        $publiAccount = json_decode($publiAccount['function_name'], true);
        if (!is_array($publiAccount) || $publiAccount['merchant'] == false || $publiAccount['signkey'] == false){
            return ['code'=>102, 'data'=>'payaccounterror'];
        }else{
            $publiAccount = [
                'mchid'=>$publiAccount['merchant'],
                'signkey'=>$publiAccount['signkey']
            ];
            return $publiAccount;
        }
    }


    /**
     * 签名
     */
    public static function sign($data, $string){
        $data = array_filter($data);
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            if ($v == '') { // 值为空不参与签名
                continue;
            }

            if ('' == $str) {
                $str .= $k . '=' . trim($v);
            } else {
                $str .= '&' . $k . '=' . trim($v);
            }
        }


        $str .= '&key=' . $string;
        $sign = strtoupper(md5($str));
        return $sign;
    }






}