<?php
/**
 * Created by PhpStorm.
 * 扫码返利C端
 * User: zhang
 * Date: 02/11/2017
 * Time: 16:29
 */

namespace ShoppingReceipt\Service;


use Common\Service\PublicService;
use ErpService\Service\ErpCommonService;

class ScanService
{

    /**
     * 扫码，判断是否满足后台赠送设置
     * @param $adminInfo
     * @param $adminDefault
     * @param $params
     * @return array
     */
    public static function showReceiptInfo($adminInfo, $adminDefault, $params)
    {
        $receiptInfo = ErpCommonService::receiveCodeInfo($params, $adminInfo, $adminDefault);//获取小票信息
        dump($receiptInfo);die;

        //获取会员信息
        $userInfo = self::getUserInfo($adminInfo, $params);


        //赠送积分，扫码送积分，需求变更：需求删除
//        $giveScore = self::checkGiveScore($adminInfo, $receiptInfo, $params, $userInfo);

        $giveCoupon = self::checkCoupon($adminInfo, $receiptInfo, $params, $userInfo);

//        $givePrize = self::checkPrize($adminInfo, $receiptInfo, $params, $userInfo);

        $data['coupon'] = $giveCoupon != false ? $giveCoupon : [];
//        $data['prize'] = $givePrize != false ? $givePrize : [];
        return ['code'=>200, 'data'=>$data];
    }


    /**
     * 是否赠送优惠券
     * @param $adminInfo
     * @param $receiptInfo
     * @param $params
     * @param $userInfo
     * @return array
     */
    private static function checkCoupon($adminInfo, $receiptInfo, $params, $userInfo)
    {
        if ($userInfo == false) {
            return ['status'=>false, 'data'=>$userInfo,'msg'=>'no user'];
        }

        //是否开启送优惠券
        $isopen = PublicService::GetOneAmindefaul($adminInfo, 'shopreceiptcouponisopen');
        if ($isopen == false || $isopen['function_name'] == 1) {
            return ['status'=>false, 'data'=>'', 'msg'=>'disabled'];
        }

        //赠送优惠券设置
        $couponSettings = PublicService::GetOneAmindefaul($adminInfo, 'shopreceiptcouponactivityid');
        //解析字段
        $couponSettings = json_decode($couponSettings['function_name'], true);
        if ($couponSettings == false) {
            return ['status'=>false, 'msg'=>'no setting'];
        }
        dump($couponSettings);
        $activityid = null;
        $paidamount = 0;//假设最小的送优惠券金额是0，判断的时候要大于这个数
        foreach ($couponSettings as $key => $value) {
            //如果金额大于满足的条件，为避免用户配置的时候金额不是按从小到大设置的，加一个&&条件，如：满1送活动id1，满10送活动id10，满5送活动id5，支付20rmb，如果按顺序则活动id是5，所以加&&判断
            if ($receiptInfo['data']['paidAmount'] >= $value['satisfied'] && $value['satisfied'] >= $paidamount){
                $activityid = $value['activityid'];
                $paidamount = $value['satisfied'];
            }
        }
        if (!$activityid) {
            return ['status'=>false, 'msg'=>'noactivity'];//没有满足的活动id
        }
        $url = 'http://101.201.176.54/rest/act/' . $activityid . '/' . $params['openid'] . '?export=scanreceipt';
        $curl = http($url, array());
        $arr = json_decode($curl, true);
        if (is_array($arr) && $arr['code'] == 0) {
            //入库
            $db = M('scanshoppingreceipt', $adminInfo['pre_table']);

            $data['cardno']=$userInfo['cardno'];
            $data['paidamount']=$receiptInfo['data']['paidAmount'];//
            $data['orderid']=$receiptInfo['data']['orderid'];//
            $data['shopentityname']=$receiptInfo['data']['shopEntityName'];//
            $data['scorenum']=0;//赠送积分数，因为产品被砍掉了，所以写死0，以后再改
            $data['couponactivityid']=$activityid;
            $data['couponactivityname']=$arr['main'];
            $data['isprize']=0;
            $data['prizeurl']='';
            $data['memname']=$userInfo['name'];
            $data['createtime'] = time();
            $add = $db->add($data);
            if ($add){
                return ['status' => true, 'data' => $arr];
            }else{
                $url = 'http://101.201.175.219/promo/api/ka/coupon/return?activityId='.$activityid.'&prizeId='.$arr['pid'].'&openId=' . $params['openid'] . '&qrCode='.$arr['qr'].'';
                http($url);
                return ['status'=>false, 'msg'=>'saveerror'];
            }
        } else {
            return ['status' =>false, 'data'=>$arr, 'msg'=>'give'];
        }
    }


    /**
     * 是否赠送
     * @param $adminInfo
     * @param $receiptInfo
     * @param $params
     * @param $userInfo
     */
    private static function checkPrize($adminInfo, $receiptInfo, $params, $userInfo)
    {
        //是否开启抽奖
        $isopen = PublicService::GetOneAmindefaul($adminInfo, 'shopreceiptprizeisopen');
        if ($isopen == false) {
            return false;
        }

        //抽奖设置
        $prizeSettings = PublicService::GetOneAmindefaul($adminInfo, 'shopreceiptprize');
        //解析字段
        $prizeSettings = json_decode($prizeSettings['function_name'], true);
        if ($prizeSettings == false) {
            return false;
        }

        $prizeurl = null;
        $paidamount = 0;//假设最小的抽奖金额是0，判断的时候要大于这个数
        foreach ($prizeSettings as $key => $value) {
            //如果金额大于满足的条件，为避免用户配置的时候金额不是按从小到大设置的，加一个&&条件
            if ($receiptInfo['data']['paidAmount'] >= $value['satisfied'] && $value['satisfied'] > $paidamount){
                $prizeurl = $value['prizeurl'];
                $paidamount = $value['satisfied'];
            }
        }
        //如果获取到了抽奖URL
        if ($prizeurl != false) {
            //入库
//            $data['createtime']=date('Y-m-d H:i:s');
//            $data['status']=1;
//            $data['user_mobile']=$userInfo['mobile'];//用户手机号
//            $data['username']=$userInfo['name'];//用户名称
//            $data['cardno']=$userInfo['cardno'];//用户卡号
//            $data['money']=$receiptInfo['data']['paidAmount'];//金额
//            $data['store']=$receiptInfo['data']['shopEntityName'];
//            $data['ordernumber']=$receiptInfo['data']['id'];
//            $data['score_number']=0;

            return ['status' => true, 'data' => ['prizeurl'=>$prizeurl]];
        } else {
            return ['status' =>false];
        }
    }


    /**
     * 判断是否要赠送积分，赠送多少积分
     * @param $adminInfo
     * @param $receiptInfo
     * @param $params
     * @return bool|array
     */
    private static function checkGiveScore($adminInfo, $receiptInfo, $params, $userInfo)
    {
        //判断实际收款多少钱，如果小于等于0，则直接返回false
        if ($score = $receiptInfo['data']['paidAmount'] <= 0){
            return false;
        }
        //是否开启送积分
        $isopen = PublicService::GetOneAmindefaul($adminInfo, 'shopreceiptscoreisopen');
        if ($isopen == false) {
            return false;
        }
        //赠送积分数:一元等于多少积分
        $scorenum = PublicService::GetOneAmindefaul($adminInfo, 'shopreceiptscorenum');
        if ($scorenum == false) {
            return false;
        }
        if (!is_numeric($scorenum['function_name'])){
            return false;
        }

        //获取会员信息
//        $userInfo = self::getUserInfo($adminInfo, $params);
        $birthday = false;//默认不是生日，不加积分
        if ($userInfo != false){
            //生日获得的积分倍数
            $birthday = self::checkBirthdayTimes($adminInfo,$params, $userInfo);
        }


        //时间段内获得的积分倍数
        $timetotime = self::checkTimeToTime($adminInfo);//返回倍数或false


        $times = $birthday === false ? 1 : 1 + $birthday;//计算生日条件符合的倍数
        $times = $timetotime === false ? $times : $times + $timetotime;//计算时间段符合的倍数
        $score = $receiptInfo['data']['paidAmount'] * $scorenum['function_name'];//计算钱数乘积分数得出没有加倍的积分数
        $timesscore = $score * $times;//未加倍的积分数乘倍数得出加倍的积分数
        $addscore = self::giveScore($adminInfo, $userInfo, $timesscore);//加积分接口
        if ($addscore == true){
            $content = [
                'birthday'=>$birthday === false ? false : true,
                'timetotime'=>$timetotime === false ? false : true
            ];
            return ['score'=>['givescore'=>$timesscore,'content'=>$content]];
        }else{
            return false;
        }
    }


    /**
     * 调用加积分
     * @param $adminInfo
     * @param $userInfo
     */
    private static function giveScore($adminInfo, $userInfo, $score)
    {
        //调用积分增加接口，增加积分
        $addscore['key_admin']=$adminInfo['ukey'];
        $addscore['cardno']=$userInfo['cardno'];
        $addscore['scoreno']=$score;
        $addscore['why']='小票扫码送积分';
        $addscore['scorecode']=date('Y-m-d');
        $addscore['membername']=$userInfo['usermember'];
        $addscore['sign_key']=$adminInfo['signkey'];
        $addscore['sign']=sign($addscore);
        $url=C('DOMAIN').'/CrmService/OutputApi/Index/addintegral';//'https://mem.rtmap.com/CrmService/OutputApi/Index/addintegral';
        unset($addscore['sign_key']);
        $return=curl_https($url,$addscore,array(),10);//调用自己的接口
        if (is_json($return)){
            $return=json_decode($return,true);
            if (200 == $return['code']){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    /**
     * 判断是否有生日赠送积分
     * 获取会员信息放在最后，毕竟要curl获取，浪费资源
     * @param $adminInfo
     * @return bool|float
     */
    private static function checkBirthdayTimes($adminInfo, $params, $userInfo)
    {
        //查不到是否开启字段
        $isopen = PublicService::GetOneAmindefaul($adminInfo, 'shopreceiptbirthdayisopen');
        if ($isopen == false) {
            return false;
        }
        //关闭状态
        if ((int)$isopen['function_name'] === 0){
            return false;
        }
        //查询积分值
        $scoretimes = PublicService::GetOneAmindefaul($adminInfo, 'shopreceiptbirthdaytimes');
        if ($scoretimes == false) {
            return false;
        }elseif ($scoretimes['function_name'] > 0) {
            //判断生日字段是否有值
            $birthdaytime = strtotime($userInfo['birthday']);
            if ($birthdaytime == false) {
                return false;
            }
            //获取今天的开始和结束时间
            $todaystart = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $todaystop = mktime(23, 59,59, date('m'), date('d'), date('Y'));
            //如果生日在今天，则，返回积分数
            if ($birthdaytime >= $todaystart && $birthdaytime <= $todaystop) {
                return (float)$scoretimes['function_name'];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    /**
     * 获取会员信息
     * @param $adminInfo
     * @param $params
     * @return bool|array
     */
    private static function getUserInfo($adminInfo, $params)
    {
        $db = M('mem', $adminInfo['pre_table']);
        $find = $db->where(['openid'=>$params['openid']])->select();
        if (!$find || count($find) > 1){
            return false;
        }
        $data['card'] = $find[0]['cardno'];
        $data['key_admin'] = $adminInfo['ukey'];
        $data['sign_key'] = $adminInfo['signkey'];
        $data['sign'] = sign($data);
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/getuserinfobycard';
        $curl_re = http($url, $data, 'post');
        $info = json_decode($curl_re, true);
        if ($info['code'] == 200){
            $info['data']['score']=number_format($info['data']['score'],2,'.','');
            return $info['data'];
        }else{
            return false;
        }
    }


    /**
     * 判断时间段内是否送积分
     * @param $adminInfo
     * @return bool|float
     */
    private static function checkTimeToTime($adminInfo)
    {
        //查不到是否开启字段
        $isopen = PublicService::GetOneAmindefaul($adminInfo, 'shopreceipttimetotimeisopen');
        if ($isopen == false) {
            return false;
        }
        //关闭状态
        if ((int)$isopen['function_name'] === 0){
            return false;
        }

        //当前时间是否在时间段内
        $time = PublicService::GetOneAmindefaul($adminInfo, 'shopreceipttimetotimetimes');
        if ($time == false){
            return false;
        }
        //解析字段
        $timetotime = json_decode($time['function_name'], true);
        if ($timetotime == false) {
            return false;
        }
        $nowtime = time();
        $nowistime = false;
        foreach ($timetotime as $key => $value) {
            if ($nowtime > $value['starttime'] && $nowtime < $value['stoptime']){//如果有时间段条件符合
                $nowistime = true;
                break;
            }
        }

        //判断是否有条件符合，如果没有，则返回false
        if ($nowistime === false) {
            return false;
        }
        //查询时间段内送积分倍数
        $timeto = PublicService::GetOneAmindefaul($adminInfo, 'shopreceipttimetotime');
        if ($timeto == false) {
            return false;
        }
        if ($timeto['function_name'] == false){
            return false;
        }
        return (float)$timeto['function_name'];
    }



    public static function scanShoppingReceiptList($adminInfo, $params, $pageNum = 1, $line = 10)
    {
        $pageNum = false != $pageNum ? $pageNum : 1;
        $line = false != $line ? $line : 10;
        if (!isset($params['openid']) || empty($params['openid'])) {
            return ['code'=>1030];
        }
        $userinfo = self::getUserInfo($adminInfo, $params);
        if (false == $userinfo) {
            return ['code'=>2000];
        }

        $where['cardno'] = $userinfo['cardno'];

        $db = M('scanshoppingreceipt', $adminInfo['pre_table']);
        $count = $db->where($where)->count('id');//总条数
        if ($count > 0){
            $totalPages = ceil($count/$line);//总页数
            $start = ($pageNum - 1) * $line;
            $sel = $db->where($where)->limit($start, $line)->select();
            return ['code'=>200, 'data'=>['totalpages'=>$totalPages,'dataCount'=>$count, 'pagenum'=>$pageNum, 'line'=>$line, 'data'=>$sel]];
        }else{
            return ['code'=>102];
        }


    }










}