<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 27/10/2017
 * Time: 15:18
 */

namespace MerAdmin\Service;


use Common\Service\PublicService;
use Common\Service\RedisService;

class ShoppingReceiptService
{

    /**
     *
     * @param $scoreSetting 积分设置
     * @param $couponSettings  优惠券设置
     * @param $prizeSettings 抽奖设置
     * @param $adminInfo
     * @return array
     *
     * 2017-11-03因需求更改，删除积分相关设置、生日相关设置、时间段相关设置，代码内注释
     *
     */
    public static function shopReceipt($scoreSetting, $couponSettings, $prizeSettings, $birthday, $timetotime, $adminInfo)
    {
//        //判断积分设置
//        $score = self::checkScoreSetting($scoreSetting);
//        if ($score !== true) {
//            return $score;
//        }
        //判断优惠券设置
        $coupon = self::checkCouponSettings($couponSettings);
        if ($coupon !== true) {
            return $coupon;
        }

        //判断奖品设置
//        $coupon = self::checkPrizeSettings($prizeSettings);
//        if ($coupon !== true) {
//            return $coupon;
//        }

//        //判断生日是否送积分
//        $birthdayis = self::checkBirthdayGiveScore($birthday);
//        if ($birthdayis !== true) {
//            return $birthdayis;
//        }
//        //时间段内是否设置
//        $timetotimeis = self::checkTimeToTime($timetotime);
//        if ($timetotimeis !== true) {
//            return $timetotimeis;
//        }

        /**
         * 积分设置状态
         */
        //积分是否开启
//        $scoreisopendata = [
//            'customer_name'=>'shopreceiptscoreisopen',
//            'function_name'=>$scoreSetting['isopen'],
//            'description'=>'小票积分送积分是否开启',
//        ];
//        //积分比率
//        $scorenumberdata = [
//            'customer_name'=>'shopreceiptscorenum',
//            'function_name'=>$scoreSetting['scorenum'],
//            'description'=>'小票积分送多少积分',
//        ];

        /**
         * 赠送优惠券设置
         */
        //是否开启优惠券
        $couponisopendata = [
            'customer_name'=>'shopreceiptcouponisopen',
            'function_name'=>$couponSettings['isopen'],
            'description'=>'小票是否开启送优惠券的配置',
        ];
        //优惠券配置
        $datacoupons = [];
        foreach ($couponSettings['coupon'] as $key => $value) {
            $datacoupons[] = [
                'activityid'=>$value['activityid'],
                'satisfied'=>$value['satisfied']
            ];
        }
        $couponactivitydata = [
            'customer_name'=>'shopreceiptcouponactivityid',
            'function_name'=>json_encode($datacoupons),
            'description'=>'小票是否开启抽奖的配置',
        ];
        /**
         * 抽奖配置
         */
        /**
         * 是否开启抽奖
         */
//        $prizeisopendata = [
//            'customer_name'=>'shopreceiptprizeisopen',
//            'function_name'=>$prizeSettings['isopen'],
//            'description'=>'小票是否开启抽奖的配置',
//        ];
//        //抽奖配置
//        $dataprizes = [];
//        foreach ($prizeSettings['prize'] as $key => $value) {
//            $dataprizes[] = [
//                'prizeurl'=>$value['url'],
//                'satisfied'=>$value['satisfied']
//            ];
//        }
//        //奖品礼品设置
//        $prizedata = [
//            'customer_name'=>'shopreceiptprize',
//            'function_name'=>json_encode($dataprizes),
//            'description'=>'小票是抽奖配置',
//        ];

        //生日设置
//        $birthdaydata = [
//            'customer_name'=>'shopreceiptbirthdayisopen',
//            'function_name'=>$birthday['isopen'],
//            'description'=>'是否开启生日送多倍积分',
//        ];
//        $birthdaytimesdata = [
//            'customer_name'=>'shopreceiptbirthdaytimes',
//            'function_name'=>$birthday['times'],
//            'description'=>'生日送积分倍数',
//        ];

        /**
         * 时间段积分翻倍
         */
        /**
         * 是否开启时间段多倍积分
         */
//        $timetotimedata = [
//            'customer_name'=>'shopreceipttimetotimeisopen',
//            'function_name'=>$timetotime['isopen'],
//            'description'=>'小票是否开启多时间段多倍积分',
//        ];
//        //送积分倍数
//        $timestotimesdata = [
//            'customer_name'=>'shopreceipttimetotime',
//            'function_name'=>$timetotime['times'],
//            'description'=>'小票是送积分倍数',
//        ];
//        //时间段内多倍积分配置配置
//        $datatimetotime = [];
//        foreach ($timetotime['timetotime'] as $key => $value) {
//            $datatimetotime[] = [
//                'starttime'=>(int)$value['starttime'],
//                'stoptime'=>(int)$value['stoptime']
//            ];
//        }
//        //时间段
//        $timetotimetimesdata = [
//            'customer_name'=>'shopreceipttimetotimetimes',
//            'function_name'=>json_encode($datatimetotime),
//            'description'=>'小票赠送多倍积分',
//        ];




        $m = M('default', $adminInfo['pre_table']);
//        $scoreisopen = $m->where(array('customer_name'=>'shopreceiptscoreisopen'))->find();
//        $scorenumber = $m->where(array('customer_name'=>'shopreceiptscorenum'))->find();
        $couponisopen = $m->where(array('customer_name'=>'shopreceiptcouponisopen'))->find();
        $couponactivity = $m->where(array('customer_name'=>'shopreceiptcouponactivityid'))->find();
//        $prizeisopen = $m->where(array('customer_name'=>'shopreceiptprizeisopen'))->find();
//        $prize = $m->where(array('customer_name'=>'shopreceiptprize'))->find();
//        $birthdayisopen = $m->where(array('customer_name'=>'shopreceiptbirthdayisopen'))->find();
//        $birthdaytimes = $m->where(array('customer_name'=>'shopreceiptbirthdaytimes'))->find();
//        $timetotimeisopen = $m->where(array('customer_name'=>'shopreceipttimetotimeisopen'))->find();
//        $timetotimetimes = $m->where(array('customer_name'=>'shopreceipttimetotimetimes'))->find();
//        $timetotimetimescheck = $m->where(array('customer_name'=>'shopreceipttimetotime'))->find();



        $m->startTrans();
        //积分两项配置
//        if ($scoreisopen){
//            $scoreisopensave=$m->where(['customer_name'=>'shopreceiptscoreisopen'])->save($scoreisopendata);
//        }else{
//            $scoreisopensave=$m->add($scoreisopendata);
//        }
//
//        if ($scorenumber){
//            $scorenumbersave = $m->where(['customer_name'=>'shopreceiptscorenum'])->save($scorenumberdata);
//        }else{
//            $scorenumbersave = $m->add($scorenumberdata);
//        }

        //优惠券两项配置
        if ($couponisopen){
            $couponisopensave = $m->where(['customer_name'=>'shopreceiptcouponisopen'])->save($couponisopendata);
        }else{
            $couponisopensave = $m->add($couponisopendata);
        }
        if ($couponactivity){
            $couponactivitysave = $m->where(['customer_name'=>'shopreceiptcouponactivityid'])->save($couponactivitydata);
        }else{
            $couponactivitysave = $m->add($couponactivitydata);
        }

        //奖品两项配置
//        if ($prizeisopen){
//            $prizeopensave = $m->where(['customer_name'=>'shopreceiptprizeisopen'])->save($prizeisopendata);
//        }else{
//            $prizeopensave = $m->add($prizeisopendata);
//        }
//        if ($prize){
//            $prizesave = $m->where(['customer_name'=>'shopreceiptprize'])->save($prizedata);
//        }else{
//            $prizesave = $m->add($prizedata);
//        }

        //生日两项配置
//        if ($birthdayisopen){
//            $birthdayisopensave = $m->where(['customer_name'=>'shopreceiptbirthdayisopen'])->save($birthdaydata);
//        }else{
//            $birthdayisopensave = $m->add($birthdaydata);
//        }
//        if ($birthdaytimes){
//            $birthdaytimessave = $m->where(['customer_name'=>'shopreceiptbirthdaytimes'])->save($birthdaytimesdata);
//        }else{
//            $birthdaytimessave = $m->add($birthdaytimesdata);
//        }

        //时间段内多倍积分三项配置
//        if ($timetotimeisopen){
//            $timetotimeisopensave = $m->where(['customer_name'=>'shopreceipttimetotimeisopen'])->save($timetotimedata);
//        }else{
//            $timetotimeisopensave = $m->add($timetotimedata);
//        }
//        if ($timetotimetimes){
//            $timetotimetimessave = $m->where(['customer_name'=>'shopreceipttimetotimetimes'])->save($timetotimetimesdata);
//        }else{
//            $timetotimetimessave = $m->add($timetotimetimesdata);
//        }
//        if ($timetotimetimescheck){
//            $timetotimetimeschecksave = $m->where(['customer_name'=>'shopreceipttimetotime'])->save($timestotimesdata);
//        }else{
//            $timetotimetimeschecksave = $m->add($timestotimesdata);
//        }






        if (
//            $scoreisopensave !== false
//            && $scorenumbersave !== false &&
            $couponisopensave !== false
            && $couponactivitysave !== false
//            && $prizeopensave !== false
//            && $prizesave !== false
//            && $birthdayisopensave !== false
//            && $birthdaytimessave !== false
//            && $timetotimeisopensave !== false
//            && $timetotimetimessave !== false
//            && $timetotimetimeschecksave !== false
        ) {
            $m->commit();

            RedisService::connectredis()->delete(
                'admin:default:one:shopreceiptscoreisopen:'.$adminInfo['ukey'],
                'admin:default:one:shopreceiptscorenum:'.$adminInfo['ukey'],
                'admin:default:one:shopreceiptcouponisopen:'.$adminInfo['ukey']
            );
            RedisService::connectredis()->delete(
                'admin:default:one:shopreceiptcouponactivityid:'.$adminInfo['ukey'],
                'admin:default:one:shopreceiptprizeisopen:'.$adminInfo['ukey'],
                'admin:default:one:shopreceiptprize:'.$adminInfo['ukey']
            );
            RedisService::connectredis()->delete(
                'admin:default:one:shopreceiptbirthdayisopen:'.$adminInfo['ukey'],
                'admin:default:one:shopreceiptbirthdaytimes:'.$adminInfo['ukey'],
                'admin:default:one:shopreceipttimetotimeisopen:'.$adminInfo['ukey']
            );
            RedisService::connectredis()->delete(
                'admin:default:one:shopreceipttimetotimetimes:'.$adminInfo['ukey'],
                'admin:default:one:shopreceipttimetotime:'.$adminInfo['ukey']
            );
            return ['code'=>200];
        }else{
            $m->rollback();
            return ['code'=>104];
        }
    }


    /**
     * 积分奖励配置
     * @param $scoreSetting
     * @return array|bool
     */
    private static function checkScoreSetting($scoreSetting)
    {
        //格式应是数组
        if (!is_array($scoreSetting)){
            return ['code'=>1051, 'data'=>'score1'];
        }

        //积分设置字段是否完整
        if (!isset($scoreSetting['isopen']) ) {
            return ['code'=>1030, 'data'=>'score1'];
        }

        //0关闭，1开启
        if (!is_numeric($scoreSetting['isopen']) || !in_array($scoreSetting['isopen'], [0,1])){
            return ['code'=>1051, 'data'=>'score2'];
        }

        //如果开状态，没有传入积分数或积分数传入的不对
        if (isset($scoreSetting['scorenum'])) {
            if (!is_numeric($scoreSetting['scorenum']) || empty($scoreSetting['scorenum']) || $scoreSetting['scorenum'] <= 0){
                return ['code'=>1051, 'data'=>'score3'];
            }
        }
        return true;
    }


    /**
     *送券
     * @param $couponSettings
     * @return array|bool
     */
    private static function checkCouponSettings($couponSettings)
    {
        //格式应该是数组
        if (!is_array($couponSettings)){
            return ['code'=>1051, 'data'=>'coupon1'];
        }

        //是否有开启字段
        if (!isset($couponSettings['isopen'])) {
            return ['code'=>1030, 'data'=>'coupon1'];
        }

        //0关闭，1开启
        if (!is_numeric($couponSettings['isopen']) || !in_array($couponSettings['isopen'], [0,1])){
            return ['code'=>1051, 'data'=>'coupon2'];
        }

        //如果开启状态，则判断优惠券设置是否正确
        if (isset($couponSettings['coupon']) ) {
            if (!is_array($couponSettings['coupon'])){
                return ['code'=>1051, 'data'=>'coupon3'];
            }
            //判断数组是否正确
            foreach ($couponSettings['coupon'] as $key => $val) {
                if (!is_numeric($val['activityid']) || !is_numeric($val['satisfied']) || !isset($val['activityid']) || !isset($val['satisfied']) || empty($val['activityid']) || empty($val['satisfied']) || $val['satisfied'] <= 0){
                    return ['code'=>1030, 'data'=>'coupon2'];
                }
            }
        }
        return true;
    }


    /**
     *抽奖
     * @param $couponSettings
     * @return array|bool
     */
    private static function checkPrizeSettings($prizeSettings)
    {
        //格式应该是数组
        if (!is_array($prizeSettings)){
            return ['code'=>1051, 'data'=>'prize1'];
        }

        //是否有开启字段
        if (!isset($prizeSettings['isopen'])) {
            return ['code'=>1030, 'data'=>'prize1'];
        }

        //0关闭，1开启
        if (!is_numeric($prizeSettings['isopen']) || !in_array($prizeSettings['isopen'], [0,1])){
            return ['code'=>1051, 'data'=>'prize2'];
        }

        //如果开启状态，则判断优惠券设置是否正确
        if (isset($prizeSettings['prize'])) {
            if (!is_array($prizeSettings['prize'])){
                return ['code'=>1051, 'data'=>'prize3'];
            }
            //判断数组是否正确
            foreach ($prizeSettings['prize'] as $key => $val) {
                if (!is_numeric($val['satisfied']) || !isset($val['url']) || !isset($val['satisfied']) || empty($val['url']) || empty($val['satisfied']) || $val['satisfied'] <= 0){
                    return ['code'=>1030, 'data'=>'prize2'];
                }
            }
        }
        return true;
    }


    /**
     * 是否开启生日多倍积分
     * @param $birthdayTimes 是否开启、倍数
     * @return array|bool
     */
    private static function checkBirthdayGiveScore($birthdayTimes)
    {
        if (!is_array($birthdayTimes)) {
            return ['code'=>1051, 'data'=>'birthday1'];
        }
        //是否有开启字段
        if (!isset($birthdayTimes['isopen'])) {
            return ['code'=>1030, 'data'=>'birthday1'];
        }

        //0关闭，1开启
        if (!is_numeric($birthdayTimes['isopen']) || !is_numeric($birthdayTimes['times']) || $birthdayTimes['times'] <=0 || !in_array($birthdayTimes['isopen'], [0,1])){
            return ['code'=>1051, 'data'=>'birthday2'];
        }
        return true;
    }


    /**
     * 验证时间段内的配置是否符合条件
     * @param $times
     * @return array|bool
     */
    private static function checkTimeToTime($times)
    {
        if (!is_array($times) || !is_array($times['timetotime'])) {
            return ['code'=>1051, 'data'=>'time1'];
        }
        //是否有开启字段
        if (!isset($times['isopen']) || !isset($times['times']) || !isset($times['timetotime'])) {
            return ['code'=>1030, 'data'=>'time1'];
        }

        if (!is_numeric($times['isopen']) || !is_numeric($times['times']) || !in_array($times['isopen'], [0,1])) {
            return ['code'=>1051, 'data'=>'time2'];
        }

        $stoptime = 0;
        //下面判断时间，时间比较麻烦
        foreach ($times['timetotime'] as $key => $val) {
            if (!isset($val['stoptime']) || !isset($val['starttime'])){
                return ['code'=>1030, 'data'=>['data'=>$val, 'k'=>$key, 'code'=>'time2']];
            }
            //如果不是数值，要求传入时间戳
            if (!is_numeric($val['stoptime']) || !is_numeric($val['starttime'])) {
                return ['code'=>1051, 'data'=>['data'=>$val,'code'=>'time3']];
                break;
            }
            //如果结束时间比开始时间小
            if ($val['stoptime'] < $val['starttime']) {
                return ['code'=>1051, 'data'=>['data'=>$val,'code'=>'time4']];
                break;
            }

            //如果开始时间比上一个的结束时间小，说明有重合点，不符合要求
            if ($val['starttime'] <= $stoptime) {
                return ['code'=>1051, 'data'=>['data'=>$val,'code'=>'time5']];
                break;
            }
            //如果比现在的时间小，不符合要求
            if ($val['starttime'] < time()) {
                return ['code'=>1051, 'data'=>['data'=>$val,'code'=>'time6']];
                break;
            }

            $stoptime = $val['stoptime'];
        }
        return true;
    }


    /**
     * 获取配置
     * @param $adminInfo
     * @return array
     */
    public static function getShoppingReceiptSettings($adminInfo)
    {
        $m = M('default', $adminInfo['pre_table']);
        $scoreisopen = $m->where(array('customer_name'=>'shopreceiptscoreisopen'))->find();
        $scorenumber = $m->where(array('customer_name'=>'shopreceiptscorenum'))->find();
        $birthdayisopen = $m->where(array('customer_name'=>'shopreceiptbirthdayisopen'))->find();
        $birthdaytimes = $m->where(array('customer_name'=>'shopreceiptbirthdaytimes'))->find();
        $timetotimeisopen = $m->where(array('customer_name'=>'shopreceipttimetotimeisopen'))->find();
        $timetotimetimes = $m->where(array('customer_name'=>'shopreceipttimetotimetimes'))->find();
        $timetotimetimescheck = $m->where(array('customer_name'=>'shopreceipttimetotime'))->find();
        $couponisopen = $m->where(array('customer_name'=>'shopreceiptcouponisopen'))->find();
        $couponactivity = $m->where(array('customer_name'=>'shopreceiptcouponactivityid'))->find();
        $prizeisopen = $m->where(array('customer_name'=>'shopreceiptprizeisopen'))->find();
        $prize = $m->where(array('customer_name'=>'shopreceiptprize'))->find();


        $data = [
            'scoreisopen'=>$scoreisopen['function_name'],
            'scorenumber'=>$scorenumber['function_name'],

            'birthdayisopen'=>$birthdayisopen['function_name'],
            'birthdaytimes'=>$birthdaytimes['function_name'],

            'timetotimeisopen'=>$timetotimeisopen['function_name'],
            'timetotime'=>json_decode($timetotimetimes['function_name'], true),//时间段
            'timetotimetimesdata'=>$timetotimetimescheck['function_name'],//倍数

            'couponisopen'=>$couponisopen['function_name'],
            'couponactivityid'=>json_decode($couponactivity['function_name'], true),

            'prizeisopen'=>$prizeisopen['function_name'],
            'prize'=>json_decode($prize['function_name'], true)
        ];

        return ['code'=>200, 'data'=>$data];
    }

















    /**
     * 第二部分，实际扫码返利数据
     *
     *
     */

    /**
     * 扫码返利列表
     * @param $adminInfo
     * @param array $search
     * @param int $pageNum
     * @param int $line
     * @return array
     */
    public static function scanShoppingReceiptList($adminInfo, $search= [], $pageNum = 1, $line = 10)
    {
        $pageNum = false != $pageNum ? $pageNum : 1;
        $line = false != $line ? $line : 10;
        if (!empty($search) && !is_array($search)) {
            return ['code'=>1051, 'data'=>'search1'];//搜索条件错误
        }
        $where = null;
        //判断搜索条件
        if (isset($search['receiptid']) && !empty($search['receiptid'])) {//流水号
            $where['receiptid'] = ['like', '%'.$search['receiptid'].'%'];
        }
        if (isset($search['terminalnumber']) && !empty($search['terminalnumber'])) {//收银机号
            $where['terminalnumber'] = ['like', '%'.$search['terminalnumber'].'%'];
        }


        if (isset($search['starttime']) && !empty($search['starttime']) && is_numeric($search['starttime'])) {
            $where['createtime'] = ['EGT', $search['starttime']];
        }

        if (isset($search['endtime']) && !empty($search['endtime']) && is_numeric($search['endtime'])) {
            $where['createtime'] = ['ELT', $search['endtime']];
        }



        if ($where == null) {
            $where='1=1';
        }

        $db = M('scanshoppingreceipt', $adminInfo['pre_table']);
        $count = $db->where($where)->count('id');//总条数
        if ($count > 0){
            $totalPages = ceil($count/$line);//总页数
            $start = ($pageNum - 1) * $line;
            $sel = $db->where($where)->limit($start, $line)->order('id desc')->select();
            return ['code'=>200, 'data'=>['totalpages'=>$totalPages,'dataCount'=>$count, 'pagenum'=>$pageNum, 'line'=>$line, 'data'=>$sel]];
        }else{
            return ['code'=>102];
        }







    }



    /**
     * 第二部分，扫码返利，上面的代码实际没有在任何地方用到
     */


    /**
     * 扫码返利配置
     * @param $key_admin|商户唯一码，这个字段习惯下划线了所以就不大小写了
     * @param $ruleName|规则名称
     * @param $starTtime|此规则开始时间
     * @param $endTime|此规则结束时间
     * @param $isOpenScore|是否开启赠送积分
     * @param $isOpenCoupon|是否开启赠送券
     * @param $isRepeatedlyCoupon|是否可以根据价格循环多次赠送券
     * @param $isClass|是否区分品类
     * @param $setTings|具体赠送券设置
     */
    public static function scanShoppingReceiptSettings($adminInfo, $ruleName, $starTtime, $endTime, $isOpenScore, $isOpenCoupon, $isRepeatedlyCoupon, $isClass, $setTings, $id=null)
    {
        $db = M('scanshoppingreceiptsettings', $adminInfo['pre_table']);
        $checkparams = self::checkParams($ruleName, $starTtime, $endTime, $isOpenScore, $isOpenCoupon, $isRepeatedlyCoupon, $isClass, $setTings, $db, $id);
        if ($checkparams !== true) {
            return $checkparams;
        }
        //经过上面的判断，参数都不为空，然后强制转换类型
        $starTtime = (int)$starTtime;
        $endTime = (int)$endTime;
        $isOpenScore = (int)$isOpenScore;
        $isOpenCoupon = (int)$isOpenCoupon;
        $isRepeatedlyCoupon = (int)$isRepeatedlyCoupon;
        $isClass = (int)$isClass;
        //如果配置可循环，可区分品类
        $data = [];
        if ($isRepeatedlyCoupon == 0 && $isClass == 0) {//不开启循环兑换，不区分品类
            $data = self::offRepeatedlyOffClass($setTings);
        }else{
            $data = self::onRepeatedlyonClass($setTings);
        }

        if (isset($data['code'])){
            return $data;
        }

        $datasetting = [
            'rulename'=>$ruleName,
            'starttime'=>$starTtime,
            'endtime'=>$endTime,
            'isopenscore'=>$isOpenScore,
            'isopencoupon'=>$isOpenCoupon,
            'isrepeatedlycoupon'=>$isRepeatedlyCoupon,
            'isclass'=>$isClass
        ];
        $dbsettins = M('scanshoppingreceiptcouponsettings', $adminInfo['pre_table']);
        $db->startTrans();
        $dbsettins->startTrans();

        if ($id != null) {//修改
            foreach ($data as $key => $value) {
                $data[$key]['settingsid']=$id;
            }
            $del = $dbsettins->where(['settingsid'=>$id])->delete();
            $add1 = $db->where(['id'=>$id])->save($datasetting);

            if ($data){
                $add2 = $dbsettins->addAll($data);
                if ($add1 !== false && $add2 && $del !== false){
                    $db->commit();
                    $dbsettins->commit();
                    RedisService::connectredis()->del('shopping:receipt:rebate:rules:times:' . $adminInfo['id']);
                    return ['code'=>200];
                }else{
                    $dbsettins->rollback();
                    return ['code'=>104];
                }
            }else{
                if ($add1 !== false && $del !== false){
                    $db->commit();
                    RedisService::connectredis()->del('shopping:receipt:rebate:rules:times:' . $adminInfo['id']);
                    return ['code'=>200];
                }else{
                    $dbsettins->rollback();
                    return ['code'=>104];
                }
            }
        }else{
            $add1 = $db->add($datasetting);
            if ($add1){
                if ($data){//如果有数据
                    foreach ($data as $key => $value) {
                        $data[$key]['settingsid']=$add1;
                    }
                    $add2 = $dbsettins->addAll($data);
                    if ($add2){
                        $db->commit();
                        $dbsettins->commit();
                        RedisService::connectredis()->del('shopping:receipt:rebate:rules:times:' . $adminInfo['id']);
                        return ['code'=>200];
                    }else{
                        $db->rollback();
                        $dbsettins->rollback();
                        return ['code'=>104, 'data'=>['y'=>1]];
                    }
                }else{//如果没有数据
                    $db->commit();
                    RedisService::connectredis()->del('shopping:receipt:rebate:rules:times:' . $adminInfo['id']);
                    return ['code'=>200];
                }
            }else{
                $db->rollback();
                return ['code'=>104, 'data'=>['y'=>2]];
            }
        }
    }


    /**
     * 判断除了券信息以外的其他参数正确性，不正确返回数组，并携带code，直接返回前端
     * @param $ruleName
     * @param $starTtime
     * @param $endTime
     * @param $isOpenScore
     * @param $isOpenCoupon
     * @param $isRepeatedlyCoupon
     * @param $isClass
     * @param $setTings
     * @param $db
     * @param int $isUPdate| 如果传入的话，必须是一个id，且是要修改的id
     * @return array|bool
     */
    private static function checkParams($ruleName, $starTtime, $endTime, $isOpenScore, $isOpenCoupon, $isRepeatedlyCoupon, $isClass, $setTings,$db, $isUPdate = 1)
    {
        //判断参数的完整性，is_numeric变相的判断了参数是否完整
        if (empty($ruleName) ||
            empty($starTtime) ||
            !is_numeric($starTtime) ||
            empty($endTime) ||
            !is_numeric($endTime) ||
            !is_numeric($isOpenScore) ||
            !is_numeric($isOpenCoupon) ||
            !is_numeric($isRepeatedlyCoupon) ||
            !is_numeric($isClass) ||
            !is_array($setTings)
        ){
            return ['code'=>1051, 'data'=>['y'=>'paramisempey', 'servertime'=>time()]];
        }

        //经过上面的判断，参数都不为空，然后强制转换类型
        $starTtime = (int)$starTtime;
        $endTime = (int)$endTime;
        $isOpenScore = (int)$isOpenScore;
        $isOpenCoupon = (int)$isOpenCoupon;
        $isRepeatedlyCoupon = (int)$isRepeatedlyCoupon;
        $isClass = (int)$isClass;
        //判断时间是否符合"人性"
        if ($starTtime < time() || $endTime < $starTtime) {
            return ['code'=>1051, 'data'=>['y'=>'timeerror', 'servertime'=>time()]];
        }
        //开启关闭自动只允许传0或1
        $arr = [0,1];
        if (!in_array($isOpenScore, $arr) || !in_array($isOpenCoupon, $arr) || !in_array($isRepeatedlyCoupon, $arr) || !in_array($isClass, $arr)) {
            return ['code'=>1051, 'data'=>['y'=>'on~offerror']];
        }

        /**
         * 验证传入的时间，需要和数据库中的比对，不能有时间重复
         */
        $where1 = [//开始时间在其他规则的开始和结束之间
            'starttime'=>['elt', $starTtime],
            'endtime'=>['egt', $starTtime]
        ];
        $where2 = [//结束时间在其他贵重的开始和结束之间
            'starttime'=>['elt', $endTime],
            'endtime'=>['egt', $endTime]
        ];
        $where3 = [//开始时间和结束时间都在历史规则中单条的前后（传入的时间段包含了历史某单条记录的时间段内的整段时间）
            'starttime'=>['egt', $starTtime],
            'endtime'=>['elt', $endTime]
        ];

        //如果传入了$isupdate，必须传入数字id
        if ($isUPdate != null && is_numeric($isUPdate)) {
            $mapc1['_complex'] = $where1;
            $mapc2['_complex'] = $where2;
            $mapc3['_complex'] = $where3;

            $mapc1['id'] = ['neq', (int)$isUPdate];
            $mapc2['id'] = ['neq', (int)$isUPdate];
            $mapc3['id'] = ['neq', (int)$isUPdate];

        }else{
            $mapc1 = $where1;
            $mapc2 = $where2;
            $mapc3 = $where3;
        }

        $sel1 = $db->where($mapc1)->find();
        $sel2 = $db->where($mapc2)->find();
        $sel3 = $db->where($mapc3)->find();
        if ($sel1 || $sel2 || $sel3) {
            return ['code'=>1051, 'data'=>['y'=>'timealreadyexits', 'servertime'=>time()]];
        }
        return true;

    }


    /**
     * 当设置除了关闭循环兑换且关闭区分品类时，调用此方法
     * @param $setTings
     * @return array
     */
    private static function onRepeatedlyonClass($setTings)
    {
        $coupon = array_column($setTings, 'class');//取出品类编码
        $totalNum = 0;//每一个二维数组中的个数
        $couponArray = null;
        //验证品类编码是否有重复
        foreach ($coupon as $key => $value) {
            $arr = explode(';', $value);
            $totalNum = $totalNum + count($arr);
            $couponArray[] = $arr;//品类转成的数组
        }
        $array = call_user_func_array('array_merge',$couponArray);//把所有的品类数组合并
        $array = array_unique($array);
        $a = in_array('0', $array, true);
        $array = array_filter($array);
        if ($a == true){
            array_push($array, '0');
        }
        //经过品类转数组，组合，去重，得到的不重合的个数和每一个的个数相比，如果个数不一样，说明品类有重复的，返回错误
        if ($totalNum != count($array)) {
            return ['code'=>1004, 'data'=>['y'=>'classerror']];//调试时，暂时注释
        }
        $satisfied = array_column($setTings, 'satisfied');
        if (count($setTings) != count(array_unique($satisfied))) {
            return ['code'=>1005, 'data'=>['y'=>'satisfiednumerror']];//调试时，暂时注释
        }

        $dbData = [];
        $i = 0;
        foreach ($setTings as $key => $value) {
            $dbData[$i]['classes'] = json_encode(explode(';', $value['class']));
            //验证金额
            if (isset($value['satisfied']) && is_numeric($value['satisfied']) && $value['satisfied'] > 0){
                $dbData[$i]['satisfied'] = $value['satisfied'];
            }else{
                return ['code'=>1004, 'data'=>['y'=>'satisfiedparamerror']];
                break;
            }
            //验证金额
            if (isset($value['activityid']) && is_numeric($value['activityid']) && $value['activityid'] > 0){
                $dbData[$i]['activityid'] = $value['activityid'];
            }else{
                return ['code'=>1004, 'data'=>['y'=>'activityidparamerror']];
                break;
            }
            ++$i;
        }
        return $dbData;
    }


    /**
     * 不开启循环兑换，不开启区分品类
     * @param $setTings
     */
    private static function offRepeatedlyOffClass($setTings){
        if (empty($setTings['class'])){
            $coupon = [];
        }else{
            $coupon = array_unique(explode(';', $setTings['class']));//获取券信息
        }
        $a = in_array('0', $coupon, true);
        $coupon = array_filter($coupon);
        if ($a == true){
            array_push($coupon, '0');
        }
        $dbData = [];
        foreach ($setTings['coupon'] as $key => $value) {
            if (isset($value['satisfied']) && is_numeric($value['satisfied']) && !empty($value['activityid'])) {
                $dbData[] = [
                    'satisfied'=>$value['satisfied'],
                    'activityid'=>$value['activityid'],
                    'classes'=>json_encode($coupon)
                ];
            }else{
                return ['code'=>1004, 'data'=>['y'=>'couponparamerror']];
                break;
            }
        }
        return $dbData;
    }


    /**
     * 获取单个扫码配置信息
     * @param $adminInfo
     * @param $id
     * @return array
     */
    public static function getShoppingReceiptSettingsInfo($adminInfo, $id)
    {
        if (empty($id) || !is_numeric($id)) {
            return ['code'=>1051, 'data'=>['y'=>'iderror']];
        }

        $db = M('scanshoppingreceiptsettings', $adminInfo['pre_table']);
        $dbsettins = M('scanshoppingreceiptcouponsettings', $adminInfo['pre_table']);
        $find = $db->where(['id'=>$id])->find();
        if ($find){
            $couponSettings = $dbsettins->where(['settingsid'=>$find['id']])->select();
            $find['couponsettings'] = $couponSettings;
            return ['code'=>200, 'data'=>$find];
        }else{
            return ['code'=>102];
        }
    }


    /**
     * 获取扫码配置信息列表
     * @param $adminInfo
     * @param null $rulename
     * @param null $starttime
     * @param null $endtime
     * @return array
     */
    public static function getShoppingReceiptSettingsList($adminInfo, $rulename=null, $starttime=null, $endtime=null)
    {
        $db = M('scanshoppingreceiptsettings', $adminInfo['pre_table']);

        if ($rulename != null){
            $where['rulename'] = ['like', '%'.$rulename.'%'];
        }
        if ($starttime != null){
            $where['starttime'] = ['egt', (int)$starttime];
        }
        if ($endtime != null){
            $where['endtime'] = ['elt', (int)$endtime];
        }
        if (empty($where)) {
            $where = '1=1';
        }

        $sel = $db->where($where)->order('id desc')->select();
        if ($sel){
            return ['code'=>200, 'data'=>$sel];
        }else{
            return ['code'=>102];
        }
    }


    /**
     * 删除单条配置
     * @param $adminInfo
     * @param $id
     * @return array
     */
    public static function destroyShoppingReceiptSetting($adminInfo, $id)
    {
        if (empty($id) || !is_numeric($id)) {
            return ['code'=>1051, 'data'=>['y'=>'iderror']];
        }

        $db = M('scanshoppingreceiptsettings', $adminInfo['pre_table']);
        $dbsettins = M('scanshoppingreceiptcouponsettings', $adminInfo['pre_table']);
        $db->startTrans();
        $dbsettins->startTrans();
        $del1 = $db->where(['id'=>$id])->delete();
        $del2 = $dbsettins->where(['settingsid'=>$id])->delete();
        if ($del1 !== false && $del2 !== false){
            $db->commit();
            $dbsettins->commit();
            RedisService::connectredis()->del('shopping:receipt:rebate:rules:times:' . $adminInfo['id']);
            return ['code'=>200];
        }else{
            $db->rollback();
            $dbsettins->rollback();
            return ['code'=>104];
        }
    }


    /**
     * 小票返利公共配置：规则，抽奖图标，或许以后还会有
     * @param $rule
     * @param $icon
     * @param $adminInfo
     * @return array
     */
    public static function receiptPublicSetting($rule, $icon, $adminInfo)
    {
        if (false == $rule || false == $icon) {
            return ['code'=>1030];
        }

        $db = M('receipt', $adminInfo['pre_table']);
        $find = $db->find();
        $data = [
            'rule'=>$rule,
            'icon'=>$icon
        ];
        if ($find){
            $save = $db->where(['id'=>$find['id']])->save($data);
        }else{
            $save = $db->add($data);
        }

        if ($save !== false) {
            return ['code'=>200];
        }else{
            return ['code'=>104];
        }


    }







}