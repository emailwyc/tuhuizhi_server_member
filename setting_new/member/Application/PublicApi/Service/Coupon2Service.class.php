<?php
/**
 * 营销平台券接口 3.0
 * Date: 18-1-17
 */
namespace PublicApi\Service;
use Common\Service\RedisService;
class Coupon2Service
{
    protected static $rurl = "http://47.94.115.24/rtmap-coupon-web/api/common/coupon";
    protected static $rurl_host = "http://47.94.115.24";
    protected static $rheader  = array(
        'Content-Type:application/json; charset=utf-8'
    );

    /**
     * 通用活动下券批列表接口
     * param:$activityId活动ID,$v版本
     */
    public static function getListByAct($params)
    {
        if (!$params['activityId']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        $v = empty($params['v'])?"1.0.0":$params['v'];
        try{
            $rparam = array(
                "activityId"=>$params['activityId'],
                "v"=>$v
            );
            $result = http(self::$rurl."/activity/list",$rparam);
            if(!is_json($result)){
                return array('code'=>101,'msg'=>"接口错误！");
            }
            $result = json_decode($result,true);
            if($result['status']==200){
                $coupon_data1 = array();
                if($result['data']){
                    foreach ($result['data'] as $v4){
                        //$coupon_data = $v4;
                        $coupon_data['pid'] = $v4['id'];
                        $coupon_data['id'] = $v4['id'];
                        $coupon_data['shopId'] = $v4['shopId'];
                        $coupon_data['main_info'] = $v4['mainInfo'];
                        $coupon_data['extend_info'] = $v4['mainInfo'];
                        $coupon_data['image_url'] = $v4['imgLogoUrl'];
                        $coupon_data['start_time'] = $v4['effectiveStartTime'];
                        $coupon_data['end_time'] = $v4['effectiveEndTime'];
                        $coupon_data['status'] = $v4['validateStatus'] == 1?0:$v4['validateStatus'];
                        $coupon_data['activity'] = $v4['activityId'];
                        $coupon_data['activityname'] = $v4['activityName'];
                        $coupon_data['activity_type'] = 'ZHT_YX';
                        $coupon_data['writeoff_count'] = $v4['writeoffNum'];
                        $coupon_data['issue'] = $v4['getNum'];
                        //！！！！！！！
                        //总数需要列表返回    --  目前没有这个参数。需要跟营销平台沟通  详情和列表都需要
                        $coupon_data['num'] = $v4['quantity']?$v4['quantity']:'';
                        $coupon_data['coupon_pid'] = $v4['couponActivityId'];
                        $coupon_data1[] = $coupon_data;
                    }
                }
                return array('code'=>200,'data'=>$coupon_data1);
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
    public static function getDetailById($params)
    {
        if (!$params['couponActivityId'] || !$params['id']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $rparam = array(
                "couponActivityId"=>$params['couponActivityId'],
                "id"=>$params['id']
            );
            $result = http(self::$rurl."/detail",$rparam);
            if(!is_json($result)){
                return array('code'=>101,'msg'=>"接口错误！");
            }
            $result = json_decode($result,true);
            if($result['status']==200){
                if(empty($result['data'])) {
                    return array('code' => 102, 'msg' => $result['message']);
                }
                $coupon_data = array();
                $coupon_data['pid'] = $result['data']['id'];
                $coupon_data['id'] = $result['data']['id'];
                $coupon_data['shopId'] = $result['data']['shopId'];
                $coupon_data['main_info'] = $result['data']['mainInfo'];
                $coupon_data['extend_info'] = $result['data']['mainInfo'];
                $coupon_data['image_url'] = $result['data']['imgLogoUrl'];
                $coupon_data['start_time'] = $result['data']['effectiveStartTime'];
                $coupon_data['end_time'] = $result['data']['effectiveEndTime'];
                $coupon_data['status'] = $result['data']['validateStatus'] == 1?0:$result['data']['validateStatus'];
                $coupon_data['activity'] = $result['data']['activityId'];
                $coupon_data['activityname'] = $result['data']['activityName'];
                $coupon_data['activity_type'] = 'ZHT_YX';
                $coupon_data['writeoff_count'] = $result['data']['writeoffNum'];
                $coupon_data['issue'] = $result['data']['getNum'];
                //！！！！！！！
                //总数需要列表返回    --  目前没有这个参数。需要跟营销平台沟通  详情和列表都需要
                $coupon_data['num'] = $result['data']['quantity']?$result['data']['quantity']:'';
                $coupon_data['coupon_pid'] = $result['data']['couponActivityId'];
                return array('code'=>200,'data'=>$coupon_data);
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
    public static function giveCoupon($params)
    {
        if (!$params['couponId'] || !$params['openId'] || !$params['couponActivityId']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $type = isset($params['type'])?$params['type']:1;
            $wxOpenId = isset($params['wxOpenId'])?$params['wxOpenId']:"";
            $channelId = isset($params['channelId'])?$params['channelId']:"";
            $rparam = json_encode(array(
                "couponId"=>$params['couponId'],
                "openId"=>$params['openId'],
                "couponActivityId"=>$params['couponActivityId'],
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
                if(empty($result['data'])) {
                    return array('code' => 102, 'msg' => $result['message']);
                }
                $result['data']['qr']=$result['data']['qrCode'];
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
    public static function giveCouponCheck($params)
    {
        if (!$params['couponId'] || !$params['openId'] || !$params['couponActivityId'] || !$params['activityId']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $type = isset($params['type'])?$params['type']:1;
            $wxOpenId = isset($params['wxOpenId'])?$params['wxOpenId']:"";
            $channelId = isset($params['channelId'])?$params['channelId']:"";
            $rparam = json_encode(array(
                "couponId"=>$params['couponId'],
                "activityId"=>$params['activityId'],
                "openId"=>$params['openId'],
                "couponActivityId"=>$params['couponActivityId'],
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
                if(empty($result['data'])) {
                    return array('code' => 102, 'msg' => $result['message']);
                }
                $result['data']['qr']=$result['data']['qrCode'];
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
    public static function couponWriteoff($params)
    {
        if (!$params['openId'] || !$params['qrCode'] || !$params['writeOffChannel']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $shopId = isset($params['shopId'])?$params['shopId']:"";
            $rparam = json_encode(array(
                "openId"=>$params['openId'],
                "qrCode"=>$params['qrCode'],
                "writeOffChannel"=>$params['writeOffChannel'],
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
    public static function returnTicket($params)
    {
        if (!$params['couponId'] || !$params['openId'] || !$params['couponActivityId'] || !$params['qrCode']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $shopId = isset($params['shopId'])?$params['shopId']:"";
            $rparam = json_encode(array(
                "couponId"=>$params['couponId'],
                "openId"=>$params['openId'],
                "couponActivityId"=>$params['couponActivityId'],
                "qrCode"=>$params['qrCode'],
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
    public static function returnWriteOff($params)
    {
        if (!$params['openId'] || !$params['userId']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $shopId = isset($params['shopId'])?$params['shopId']:"";
            $rparam = json_encode(array(
                "shopId"=>$shopId,
                "openId"=>$params['openId'],
                "userId"=>$params['userId'],
                "qrCode"=>$params['qrCode'],
                "writeOffChannel"=>$params['writeOffChannel'],
                "flowType"=>$params['flowType']
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
    public static function giveCouponBatch($params)
    {
        if (!$params['activityId']) {
            return array('code'=>1030,'msg'=>"参数错误！");
        }
        try{
            $v = isset($params['v'])?$params['v']:"1.0.0";
            $rparam = array(
                "activityId"=>$params['activityId'],
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
                        $type = isset($params['type'])?$params['type']:1;
                        $wxOpenId = isset($params['wxOpenId'])?$params['wxOpenId']:"";
                        $channelId = isset($params['channelId'])?$params['channelId']:1;
                        $rparam1 = json_encode(array(
                            "couponId" => $v['couponId'],
                            "activityId" => $params['activityId'],
                            "openId" => $params['openId'],
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
     * 获取活动状态接口(4.0暂时没有这个接口)
     * activityId
     */
    public static function getActivityStatus($params)
    {
        return array('code'=>200, 'data'=>1);
    }
    /**
     * 抽奖接口4.0
     * $openId，activityId
     */
    public static function luckDraw($params)
    {
        return array('code'=>1082, 'msg'=>"4.0没有该接口");
    }

}