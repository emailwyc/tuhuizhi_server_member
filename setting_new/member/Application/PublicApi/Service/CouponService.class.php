<?php
/**
 * Created by PhpStorm.
 * User: soone
 * Date: 17-12-20
 * Time: 下午12:07
 */
namespace PublicApi\Service;
use Common\Service\RedisService;
use Common\Service\PublicService;
class CouponService
{
    protected static $rurl = "http://47.94.115.24/rtmap-coupon-web/api/common/coupon";
    protected static $rurl_host = "http://47.94.115.24";
    protected static $rheader  = array(
        'Content-Type:application/json; charset=utf-8'
    );

    /**
     * 营销平台券相关调配器
     */
    public static function Index($params)
    {
        if (empty($params['key_admin'])){
            return array('code'=>1001,'msg'=>"key_admin未定义！");
        }
        $adminInfo = PublicService::getMerchant($params['key_admin']);
        if (empty($adminInfo['pre_table'])) {
            return array('code'=>1082,'msg'=>"没有该商户记录！");
        }
        $couponVersion = PublicService::GetOneAmindefaul($adminInfo, 'coupon_default');
        $version = empty($couponVersion['function_name'])?1:$couponVersion['function_name'];
        $calssname = 'PublicApi\Service\Coupon'.$version.'Service';
        $action = $params['method'];
        if (!empty($action)){
            $a=$calssname::$action($params['api']);
            return $a;
        }else{
            return array('code'=>1082,'msg'=>"方法名错误，请检查！");
        }
    }

    /**
     * 通用活动下券批列表接口
     * param:$activityId活动ID,$v版本
     */
    public static function getListByAct($activityId, $v="1.0.0")
    {
        if (!$activityId) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = array(
                "activityId"=>$activityId,
                "v"=>$v
            );
            $result = http(self::$rurl."/activity/list",$rparam);
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
     * $activityId:活动ID,$id:券id
     */
    public static function getDetailById($couponActivityId, $id)
    {
        if (!$couponActivityId || !$id) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = array(
                "couponActivityId"=>$couponActivityId,
                "id"=>$id
            );
            $result = http(self::$rurl."/detail",$rparam);
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
     * 领券接口(不验证)
     * $couponId,$openId,$couponActivityId,$type,$wxOpenId,$channelId
     * 　参数名　　　　类型　　说明　　　必选
     *   couponId	Long	券ID	　　是
     *   openId	　　String	用户微信标识	是
     *   type	　　Integer	领取方式:1:微信openId 2:手机号	　是
     *   wxOpenId　　String	手机号领取时需携带openId	否
     *   channelId	Long	渠道ID	否
     *   couponActivityId	String	 券批ID	是
     */
    public static function giveCoupon($couponId, $openId,$couponActivityId,$type=1,$wxOpenId="",$channelId="")
    {
        if (!$couponId || !$openId || !$couponActivityId) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = json_encode(array(
                "couponId"=>$couponId,
                "openId"=>$openId,
                "couponActivityId"=>$couponActivityId,
                "type"=>$type,
                "wxOpenId"=>$wxOpenId,
                "channelId" =>$channelId
            ));
            $url = self::$rurl_host."/rtmap-luck-web/api/coupon/get";
            $result = http($url,$rparam,"POST",self::$rheader,true);
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
     * 领券接口(验证活动)(没有活动优惠券-未测试)
     * $couponId,$openId,$couponActivityId,$type,$wxOpenId,$channelId
     * 　参数名　　　　类型　　说明　　　必选
     *   couponId	Long	券ID	　　是
     *   activityId  String  活动id  是
     *   openId	　　String	用户微信标识	是
     *   type	　　Integer	领取方式:1:微信openId 2:手机号	　是
     *   wxOpenId　　String	手机号领取时需携带openId	否
     *   channelId	Long	渠道ID	否
     *   couponActivityId	String	 券批ID	是
     */
    public static function giveCouponCheck($couponId,$activityId, $openId,$couponActivityId,$type=1,$wxOpenId="",$channelId="")
    {
        if (!$couponId || !$openId || !$couponActivityId || !$activityId) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = json_encode(array(
                "couponId"=>$couponId,
                "activityId"=>$activityId,
                "openId"=>$openId,
                "couponActivityId"=>$couponActivityId,
                "type"=>$type,
                "wxOpenId"=>$wxOpenId,
                "channelId" =>$channelId
            ));
            $url = self::$rurl_host."/rts-mgr-web/api/rts/coupon/get?v=1.0.0";
            $result = http($url,$rparam,"POST",self::$rheader,true);
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
     * C端通用核销接口(没有规则)   还有一个B端核销接口加在这里也不能用（需要token，所以没有添加）
     * 参数　　类型　　说明　　是否为空
     * openId	String	微信用户标识	否
     * qrCode	String	券码	否
     * shopId	Long	核销商户	是
     * writeOffChannel　　String	核销渠道：应用名称	否
     */
    public static function couponWriteoff($openId,$qrCode,$writeOffChannel,$shopId="")
    {
        if (!$openId || !$qrCode || !$writeOffChannel) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = json_encode(array(
                "openId"=>$openId,
                "qrCode"=>$qrCode,
                "writeOffChannel"=>$writeOffChannel,
                "shopId"=>$shopId
            ));
            $url = self::$rurl_host."/rtmap-coupon-web/api/writeoff/coupon/common/writeoff";
            $result = http($url,$rparam,"POST",self::$rheader,true);
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
     * 退券接口(没有活动优惠券-未测试)
     * 参数　　类型　　说明　　是否为空
     * couponId	Long	券ID	  是
     * openId	String	用户微信标识	是
     * couponActivityId	String	 券批ID	是
     * qrCode	String	券码	是
     * shopId	Long	商户ID	是
     */
    public static function returnTicket($couponId,$openId,$couponActivityId,$qrCode,$shopId)
    {
        if (!$couponId || !$openId || !$couponActivityId || !$qrCode) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = json_encode(array(
                "couponId"=>$couponId,
                "openId"=>$openId,
                "couponActivityId"=>$couponActivityId,
                "qrCode"=>$qrCode,
                "shopId"=>$shopId
            ));
            $url = self::$rurl_host."/rtmap-luck-web/api/coupon/return";
            $result = http($url,$rparam,"POST",self::$rheader,true);
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
     * 参数　　类型　　说明　　是否为空
     * shopId	Long	核销商户(非token请求，此参数不能为空)	是
     * openId	String	微信用户标识	是
     * userId	Long	操作人ID(token请求此参数不能为空)	是
     * qrCode	String	券码	否
     * writeOffChannel String	核销渠道：应用名称	否
     * flowType	Integer	核销类型：0:商场核销 1:商户核销 2：C端核销 3:POS核销	否
     */
    public static function returnWriteOff($shopId,$openId,$userId,$qrCode,$writeOffChannel,$flowType)
    {
        if (!$couponId || !$openId || !$couponActivityId || !$qrCode) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = json_encode(array(
                "shopId"=>$shopId,
                "openId"=>$openId,
                "userId"=>$userId,
                "qrCode"=>$qrCode,
                "writeOffChannel"=>$writeOffChannel,
                "flowType"=>$flowType
            ));
            $url = self::$rurl_host."/rtmap-coupon-web/api/writeoff/coupon/common/return/writeoff";
            $result = http($url,$rparam,"POST",self::$rheader,true);
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
     * 领券接口(验证活动)(没有活动优惠券-未测试)
     * $couponId,$openId,$couponActivityId,$type,$wxOpenId,$channelId
     * 　参数名　　　　类型　　说明　　　必选
     *   couponId	Long	券ID	　　是
     *   activityId  String  活动id  是
     *   openId	　　String	用户微信标识	是
     *   type	　　Integer	领取方式:1:微信openId 2:手机号	　是
     *   wxOpenId　　String	手机号领取时需携带openId	否
     *   channelId	Long	渠道ID	否
     *   couponActivityId	String	 券批ID	是
     */
    public static function giveCouponBatch($activityId, $openId,$type,$wxOpenId="",$channelId=1,$v="1.0.0")
    {
        if (!$activityId) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = array(
                "activityId"=>$activityId,
                "v"=>$v
            );
            $result = http(self::$rurl."/activity/list",$rparam);
            if(!is_json($result)){
                return array('code'=>101,'msg'=>"接口错误！");
            }
            $result = json_decode($result,true);
            if($result['status']==200){
                if($result['data']){
                    $url = self::$rurl_host . "/rts-mgr-web/api/rts/coupon/get?v=1.0.0";
                    foreach ($result['data'] as $k=>$v) {
                        $rparam1 = json_encode(array(
                            "couponId" => $v['id'],
                            "activityId"=>$activityId,
                            "openId" => $openId,
                            "couponActivityId" => $v['couponActivityId'],
                            "type" => $type,
                            "wxOpenId" => $wxOpenId,
                            "channelId" => $channelId
                        ));
                        $result1 = http($url, $rparam1, "POST", self::$rheader, true);
                    }
                    return array('code' => 200);

                }else{
                    return array('code'=>102,'data'=>$result['message']);
                }
            }else{
                return array('code'=>1082,'msg'=>$result['message']);
            }
        }catch (\Exception $exception){
            return array('code'=>101, 'data'=>$exception);
        }
    }

    /**
     * 领券接口(验证活动)(没有活动优惠券-未测试)
     * $couponId,$openId,$couponActivityId,$type,$wxOpenId,$channelId
     * 　参数名　　　　类型　　说明　　　必选
     *   couponId	Long	券ID	　　是
     *   activityId  String  活动id  是
     *   openId	　　String	用户微信标识	是
     *   type	　　Integer	领取方式:1:微信openId 2:手机号	　是
     *   wxOpenId　　String	手机号领取时需携带openId	否
     *   channelId	Long	渠道ID	否
     *   couponActivityId	String	 券批ID	是
     */
    public static function giveCouponBatch1($activityId, $openId,$type,$wxOpenId="",$channelId=1,$v="1.0.0")
    {
        if (!$activityId) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = array(
                "activityId"=>$activityId,
                "v"=>$v
            );
            $result = http(self::$rurl."/activity/list",$rparam);
            if(!is_json($result)){
                return array('code'=>101,'msg'=>"接口错误！");
            }
            $result = json_decode($result,true);
            if($result['status']==200){
                if($result['data']){
                    $url = self::$rurl_host . "/rtmap-luck-web/api/coupon/get";
                    foreach ($result['data'] as $k=>$v) {
                        $rparam1 = json_encode(array(
                            "couponId" => $v['id'],
                            "openId" => $openId,
                            "couponActivityId" => $v['couponActivityId'],
                            "type" => $type,
                            "wxOpenId" => $wxOpenId,
                            "channelId" => $channelId
                        ));
                        $result1 = http($url, $rparam1, "POST", self::$rheader, true);
                    }
                    return array('code' => 200);

                }else{
                    return array('code'=>102,'data'=>$result['message']);
                }
            }else{
                return array('code'=>1082,'msg'=>$result['message']);
            }
        }catch (\Exception $exception){
            return array('code'=>101, 'data'=>$exception);
        }
    }


}
