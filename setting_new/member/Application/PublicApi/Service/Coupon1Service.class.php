<?php
/**
 * 营销平台券接口 3.0
 * Date: 18-1-17
 */
namespace PublicApi\Service;
use Common\Service\RedisService;
class Coupon1Service
{
    protected static $rurl = "http://47.94.115.24/rtmap-coupon-web/api/common/coupon";
    protected static $rheader  = array(
        'Content-Type:application/json; charset=utf-8'
    );

    /**
     * 通用活动下券批列表接口
     * param:$activityId活动ID
     */
    public static function getListByAct($params)
    {
        if (!$params['activityId']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = array();
            $url = "http://101.201.175.219/promo/prize/ka/prize/list/".$params['activityId'];
            $result = http($url,$rparam);
            if(!is_json($result)){
                return array('code'=>101,'msg'=>"接口错误！");
            }
            $result = json_decode($result,true);
            if($result['status']==200){
                return array('code'=>200,'data'=>$result['data']);
            }else{
                return array('code'=>1082,'msg'=>$result['message']);
            }
        }catch (\Exception $exception){
            return array('code'=>101, 'data'=>$exception);
        }
    }

    /**
     * 获取优惠券详情接口
     * $id:券id
     */
    public static function getDetailById($params)
    {
        if (!$params['id']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = array();
            $url = "http://182.92.31.114/rest/act/level/".$params['id'];
            $result = http($url,$rparam);
            if(!is_json($result)){
                return array('code'=>101,'msg'=>"接口错误！");
            }
            $result = json_decode($result,true);
            return array('code'=>200,'data'=>$result);
        }catch (\Exception $exception){
            return array('code'=>101, 'data'=>$exception);
        }
    }

    /**
     * 领券接口(不验证)
     * $couponId,$openId,$activityId
     */
    public static function giveCoupon($params)
    {
        if (!$params['couponId'] || !$params['openId'] || !$params['activityId']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = array(
            );
            $url = "http://101.201.176.54/rest/act/prize/".$params['activityId']."/".$params['couponId']."/".$params['openId'];
            $result = http($url,$rparam);
            if(!is_json($result)){
                return array('code'=>101,'msg'=>"接口错误！");
            }
            $result = json_decode($result,true);
            return array('code'=>200,'data'=>$result,'msg'=>$result['message']);
        }catch (\Exception $exception){
            return array('code'=>101, 'data'=>$exception);
        }
    }

    /**
     * 领券接口(没有活动优惠券-未测试)（couponId，openId，activityId）
     */
    public static function giveCouponCheck($params)
    {
        if (!$params['couponId'] || !$params['openId'] || !$params['activityId']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = array(
            );
            $url = "http://101.201.176.54/rest/act/prize/".$params['activityId']."/".$params['couponId']."/".$params['openId'];
            $result = http($url,$rparam);
            if(!is_json($result)){
                return array('code'=>101,'msg'=>"接口错误！");
            }
            $result = json_decode($result,true);
            return array('code'=>200,'data'=>$result,'msg'=>$result['message']);
        }catch (\Exception $exception){
            return array('code'=>101, 'data'=>$exception);
        }
    }

    /**
     * C端通用核销接口qrCode
     */
    public static function couponWriteoff($params)
    {
        if (!$params['qrCode']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        $url = 'http://101.200.216.60:8080/proxy/verify/pos';
        $data = array('code'=>$params['qrCode']);
        $res = http($url, json_encode($data), 'POST', array('Content-Type:application/json'), true);
        if (is_json($res)){
            $array = json_decode($res, true);
            if ($array['code'] == 0) {
                return array('code'=>200);
            }else{
                return array('code'=>1500,"msg"=>"核销失败！");
            }
        }else{
            return array('code'=>101, 'data'=>$res);
        }
    }

    /**
     * 退券接口(没有活动优惠券-未测试)
     * couponId,couponId,couponId,qrCode
     */
    public static function returnTicket($params)
    {
        if (!$params['couponId'] || !$params['openId'] || !$params['activityId'] || !$params['qrCode']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $url = "http://101.201.175.219/promo/api/ka/coupon/return?activityId=".$params['activityId']."&prizeId=".$params['couponId']."&openId=".$params['openId']."&qrCode=".$params['qrCode'];
            $result = http($url);
            if(!is_json($result)){
                return array('code'=>101,'msg'=>"接口错误！");
            }
            $result = json_decode($result,true);
            if($result['status']==200){
                return array('code'=>200,'data'=>$result['data']);
            }else{
                return array('code'=>1082,'msg'=>$result['message']);
            }
        }catch (\Exception $exception){
            return array('code'=>101, 'data'=>$exception);
        }
    }

    /**
     * 通用反核销接口(未测试)
     */
    public static function returnWriteOff($params)
    {
        return array('code'=>1082, 'msg'=>"3.0没有反核销接口");
    }

    /**
     * 领券接口(验证活动)(没有活动优惠券-未测试)
     * activityId,$openId,couponId
     */
    public static function giveCouponBatch($params)
    {
        if (!$params['couponId'] || !$params['openId'] || !$params['activityId']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = array(
            );
            $url = "http://101.201.176.54/rest/act/prize/".$params['activityId']."/".$params['couponId']."/".$params['openId'];
            $result = http($url,$rparam);
            if(!is_json($result)){
                return array('code'=>101,'msg'=>"接口错误！");
            }
            $result = json_decode($result,true);
            return array('code'=>200,'data'=>$result,'msg'=>$result['message']);
        }catch (\Exception $exception){
            return array('code'=>101, 'data'=>$exception);
        }
    }

    /**
     * 获取活动状态接口
     * activityId
     */
    public static function getActivityStatus($params)
    {
        if (!$params['activityId']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = array(
            );
            $url = "http://182.92.31.114/rest/act/status/".$params['activityId'];
            $result = http($url,$rparam);
            if(!is_json($result)){
                return array('code'=>101,'msg'=>"接口错误！");
            }
            $result = json_decode($result,true);
            return array('code'=>200,'data'=>$result,'msg'=>$result['message']);
        }catch (\Exception $exception){
            return array('code'=>101, 'data'=>$exception);
        }
    }

    /**
     * 抽奖接口3.0
     * $openId，activityId
     */
    public static function luckDraw($params)
    {
        if (!$params['activityId'] || $params['openId']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = array(
            );
            $url = "http://182.92.31.114/rest/act/".$params['activityId']."/".$params['openId'];
            $result = http($url,$rparam);
            if(!is_json($result)){
                return array('code'=>101,'msg'=>"接口错误！");
            }
            $result = json_decode($result,true);
            return array('code'=>200,'data'=>$result,'msg'=>$result['message']);
        }catch (\Exception $exception){
            return array('code'=>101, 'data'=>$exception);
        }
    }


}