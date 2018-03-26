<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 17/11/2017
 * Time: 17:51
 */

namespace ShoppingReceipt\Service;


use Common\Service\PublicService;
use Common\Service\RedisService;
use ErpService\Service\ErpCommonService;

class RebateService
{

    /**
     * 第一部分
     * 处理扫码或手动填写信息，计算返利信息
     */



    /**
     * 扫码返利
     * @param $admininfo
     * @param $admindefault
     * @param $params
     * @param $from
     * @return array
     */
    public static function rebate($adminInfo, $adminDefault, $params, $from)
    {
        if (!in_array($from, ['scan', 'write'])) {
            return ['code'=>111, 'data'=>['y'=>'fromerror']];
        }



        if ($from == 'scan') {
            $receiptInfo = ErpCommonService::receiveInfoByScan($params, $adminInfo, $adminDefault);
        }else {
            $receiptInfo = ErpCommonService::receiveInfoByWrite($params, $adminInfo, $adminDefault);
        }

        if ($receiptInfo['code'] != 200) {
            return $receiptInfo;
        }

        //判断是否已经扫过
        $checkReceipt = self::checkReceiptScaned($receiptInfo, $adminInfo);
        if ($checkReceipt !== true){
            return $checkReceipt;
        }

        $activity = self::prizePlan($adminInfo, $adminDefault, $params, $receiptInfo);
        return ['code'=>200, 'data'=>$activity];
    }

    /**
     * 判断小票是否扫过
     * @param $receiptInfo
     * @param $adminInfo
     * @return array|bool
     */
    private static function checkReceiptScaned($receiptInfo, $adminInfo)
    {
        //先读redis，避免读库
        if (RedisService::connectredis()->get('scanreceipt:' . $receiptInfo['data']['billSerialNumber'] . $adminInfo['ukey'])){
            return ['code'=>1032];
        }
        $db = M('scanshoppingreceipt', $adminInfo['pre_table']);
        if ($db->where(['receiptid'=>$receiptInfo['data']['billSerialNumber']])->find()){
            RedisService::connectredis()->set('scanreceipt:' . $receiptInfo['data']['billSerialNumber'] . $adminInfo['ukey'], 'yes', ['ex'=>600]);
            return ['code'=>1032];
        }else{
            return true;
        }
    }



    /**
     * 计算送券、积分结果
     * @param $adminInfo
     * @param $receiptInfo
     * @return array
     */
    private static function prizePlan ($adminInfo, $adminDefault,$params, $receiptInfo)
    {
        $couponSettings = RebateSettingsService::getRebeatSettings($adminInfo);
        //如果没有配置
        if (!$couponSettings){
            return ['code'=>113, 'data'=>['y'=>'nosettings']];
        }

        /**
         * 计算送券结果
         */
        $couponOneSetting= null;
        //查找符合条件的规则
        foreach ($couponSettings as $key => $value) {
            if ($value['starttime'] <= time() && $value['endtime'] >= time()) {
                $couponOneSetting = $value;//获取符合条件的配置
                break;
            }
        }
        //如果没有符合条件的结果，返回错误
        if ($couponOneSetting == null) {
            return ['code'=>113, 'data'=>['y'=>'timenoonesetting']];
        }
        //返利数组
        $prize = [];
        //是否赠送积分
        if ($couponOneSetting['isopenscore'] == 1) {
            try{
                //方法需要调用curl
                $giveScore = ErpCommonService::giveScoreByReceive($params, $adminInfo, $adminDefault, $receiptInfo);
                if ($giveScore['code'] == 200) {
                    $prize['score']= [
                        'scorenum' => $giveScore['data']['score'],
                        'code'=>200
                    ];
                }else{
                    $prize['score'] = [
                        'scorenum' => false,
                        'data'=>$giveScore,//erp返回的接口信息或是接口错误信息
                        'code'=>104
                    ];
                }
            }catch (\Exception $exception){

            }
        }else{
            $prize['score'] = [
                'scorenum' => false,
                'code'=>104
            ];
        }
        //如果不开启送券
        if ($couponOneSetting['isopencoupon'] != 1) {
            $prize['prize'] = [
                'prizenum' => false,
                'code'=>104
            ];
            $data = [];
        }else{
            $everyClassMoney = self::getEveryClassMoney($receiptInfo, $couponOneSetting);//按白名单和黑名单区分获取意义不同的"总价"
            //下面写开启送券时的送券规则，一共四种，分方法，看看能不能放在一起计算。
            if ($couponOneSetting['isclass'] == 1) {//区分品类时
                $data = self::enableClassIsRepeat($adminInfo, $receiptInfo, $couponOneSetting, $everyClassMoney);
            }else{
                $data = self::disableClassAndIsRepeat($adminInfo, $receiptInfo, $couponOneSetting, $everyClassMoney);
            }
        }




        $couponData['openid']=$params['openid'];
        $couponData['paidamount']=$receiptInfo['data']['paidAmount'];//
        $couponData['receiptid']=$receiptInfo['data']['billSerialNumber'];//
        $couponData['shopentityname']=$receiptInfo['data']['shopEntityName'];//
        $couponData['scorenum']=$prize['score']['scorenum'] ? $prize['score']['scorenum'] : 0;//赠送积分数，调用erp接口，erp计算后添加并返回。
        $couponData['terminalnumber'] = $receiptInfo['data']['terminalNumber'] ? $receiptInfo['data']['terminalNumber'] : '';
        $couponData['createtime'] = time();
        $db = M('scanshoppingreceipt', $adminInfo['pre_table']);
        $dbCoupon = M('scanreceiptprizenums', $adminInfo['pre_table']);

        //计算总次数
        $totalCouponNum = 0;
        if (count($data) > 0){
            foreach ($data as $key => $value) {
                $totalCouponNum = $totalCouponNum + $value['prizenums'];
            }
        }
        $couponData['prizenums'] = $totalCouponNum;
        $db->startTrans();
        $dbCoupon->startTrans();
        $addReceipt = $db->add($couponData);

        if ($addReceipt) {
            //循环判断送券详情
            if (count($data) > 0){//如果有送券
                foreach ($data as $key => $value) {
                    $data[$key]['scanid'] = $addReceipt;
                }
                $addCoupon = $dbCoupon->addAll($data);
                if ($addCoupon){
                    $db->commit();
                    $dbCoupon->commit();
                    $prize['prize'] = [
                        'prizenum'=>$totalCouponNum,
                        'code'=>200,
                        'data'=>[
                            'id'=>$addReceipt,
                            'repeat'=>$receiptInfo['data']['billSerialNumber']
                        ]
                    ];
                }else{
                    $db->rollback();
                    $dbCoupon->rollback();
                    $prize['prize'] = [
                        'prizenum'=>false,
                        'code'=>1011,
                        'msg'=>'saveerror'
                    ];
                }
            }else{//如果没有送券，只添加小票信息
                $db->commit();
                $prize['prize'] = [
                    'prizenum'=>false,
                    'code'=>102,
                    'msg'=>'ordernoclassonlyscore'
                ];
            }

        }else{
            $db->rollback();
            $prize['prize'] = [
                'prizenum'=>false,
                'code'=>1011,
                'msg'=>'receipterror'
            ];
        }
        //记录redis，避免客户没完没了的扫码，没完没了的读库、查库
        RedisService::connectredis()->set('scanreceipt:' . $receiptInfo['data']['billSerialNumber'] . $adminInfo['ukey'], 'yes', ['ex'=>600]);
        return $prize;
    }


    /**
     * 区分品类的抽奖次数计算，内含可循环和不可循环，此方法与disableClassAndIsRepeat方法互补
     * @param $adminInfo|admin详情
     * @param $receiptInfo|erp小票详情
     * @param $couponOneSetting|本次规则详情
     * @param $everyClassMoney|每种品类的总价，每个的总价，不是所有品类的总价
     */
    private static function enableClassIsRepeat($adminInfo, $receiptInfo, $couponOneSetting, $everyClassMoney)
    {
        //计算每种规则内总品类的总价
        $ruleTotalMoney = [];
        foreach ($everyClassMoney as $key => $value) {//每种品类价格
//            echo $couponOneSetting['classesAllKeys'][$key];//规则里面的每种规则的key，本质上是数组的数字key0、1、2……
            if (array_key_exists($couponOneSetting['classesAllKeys'][$key],$couponOneSetting['couponSettings'])) {//规则里面的不同品类的规则是否存在
                //计算总价
                $ruleTotalMoney[ $couponOneSetting['classesAllKeys'][$key] ] = array_key_exists($couponOneSetting['classesAllKeys'][$key], $ruleTotalMoney)
                    ? $ruleTotalMoney[$couponOneSetting['classesAllKeys'][$key]] + $value//如果之前的循环已经加key了，则加本次的钱数
                    : $value;
            }
        }
        //如果开启重复兑换
        $nums = 0;
        $dbData = [];
        if ($couponOneSetting['isrepeatedlycoupon'] == 1){
            foreach ($ruleTotalMoney as $key => $value) {
                $nums = floor($value / $couponOneSetting['couponSettings'][$key]['satisfied']) ;//舍零取整
                $dbData[] = [
                    'ruleclassmoney'=>$value,
                    'prizenums'=>$nums,
                    'prizednums'=>0,//$nums,
                    'satisfied'=>$couponOneSetting['couponSettings'][$key]['satisfied'],
                    'activityid' =>$couponOneSetting['couponSettings'][$key]['activityid']
                ];
            }
        }else{//不开启重复兑换
            foreach ($ruleTotalMoney as $key => $value) {
                if ($value > $couponOneSetting['couponSettings'][$key]['satisfied']){//只要大于基数，即可有一次
                    $dbData[] = [
                        'ruleclassmoney'=>$value,
                        'prizenums'=>1,
                        'prizednums'=>0,
                        'satisfied'=>$couponOneSetting['couponSettings'][$key]['satisfied'],
                        'activityid' =>$couponOneSetting['couponSettings'][$key]['activityid']
                    ];
                }
                $nums = floor($value / $couponOneSetting['couponSettings'][$key]['satisfied']) + $nums;//舍零取整
            }
        }

        return $dbData;
    }

    /**
     * 不区分品类的抽奖次数计算，内含可循环和不可循环，此方法与enableClassIsRepeat方法互补
     * @param $adminInfo
     * @param $receiptInfo
     * @param $couponOneSetting|正在进行的规则
     * @param $whiteListClassMoney|除黑名单以外的所有品类的总价，真的是总价，不是单个品类的总价
     */
    private static function disableClassAndIsRepeat($adminInfo, $receiptInfo, $couponOneSetting, $whiteListClassMoney)
    {
        if ($couponOneSetting['isrepeatedlycoupon'] == 1) {//可循环抽奖，用总价，除以基数
            $prizeNums = floor($whiteListClassMoney['blacklistMoney'] / $couponOneSetting['couponSettings'][0]['satisfied']);
            $dbData[] = [
                'ruleclassmoney' => $whiteListClassMoney['blacklistMoney'],
                'prizenums' => $prizeNums,
                'prizednums'=> 0,//$prizeNums,
                'satisfied'=>$couponOneSetting['couponSettings'][0]['satisfied'],
                'activityid'=>$couponOneSetting['couponSettings'][0]['activityid']//活动id
            ];
        }else{
            $satisfied = 0;
            $activityid = null;
            foreach ($couponOneSetting['couponSettings'] as $key => $value) {
                if ($whiteListClassMoney['blacklistMoney'] > $value['satisfied'] && $value['satisfied'] > $satisfied) {
                    $satisfied = $value['satisfied'];
                    $activityid = $value['activityid'];
                }
            }
            if ($satisfied >0 ){
                $dbData[] = [
                    'ruleclassmoney' => $whiteListClassMoney['blacklistMoney'],
                    'prizenums' => 1,
                    'prizednums'=> 0,//floor($whiteListClassMoney['blacklistMoney'] / $satisfied),
                    'satisfied'=>$satisfied,
                    'activityid'=>$activityid//活动id
                ];
            }
        }
        return $dbData;
    }


    /**
     * 按白名单获取"每种品类的总价"；按黑名单获取除黑名单以外的品类总价
     * @param $receiptInfo|小票详情
     * @param $couponOneSetting|本次应该调的规则
     * @param $isBlackList|是否是黑名单，默认不是黑名单
     * @return array
     */
    private static function getEveryClassMoney($receiptInfo, $couponOneSetting)
    {
        $classesToatalMoney = [];
        if ($couponOneSetting['isclass'] != 0){//区分品类是白名单
            foreach ($receiptInfo['data']['goodsDetails'] as $key => $value) {//商品列表
                if (array_key_exists($value['class'], $couponOneSetting['classesAllKeys'])) {//单个商品详情里面的品类id是否在本规则的所有品类里面
                    //计算总价
                    $classesToatalMoney[$value['class']] = array_key_exists($value['class'], $classesToatalMoney)
                        ? $classesToatalMoney[$value['class']] + $value['totalprice']
                        : $value['totalprice'];
                }
            }
        }else{//黑名单q
            $classesToatalMoney['blacklistMoney'] = 0;//不区分品类，黑名单
            foreach ($receiptInfo['data']['goodsDetails'] as $key => $value) {//商品列表
                if (!array_key_exists($value['class'], $couponOneSetting['classesAllKeys']) && !empty($value['class']) ) {//单个商品详情里面的品类id不在本规则的所有品类里面
                    //计算总价
                    $classesToatalMoney['blacklistMoney'] = $classesToatalMoney['blacklistMoney'] + $value['totalprice'];
                }
            }
        }
        return $classesToatalMoney;
    }



    /**
     * 第二部分返利历史记录
     */


    /**
     * 返利历史记录
     * @param $adminInfo
     * @param $openid
     * @param int $pageNum
     * @param int $line
     * @return array
     */
    public static function getRepeatHistory($adminInfo, $params, $pageNum = 1, $line = 10)
    {
        $pageNum = false != $pageNum ? $pageNum : 1;
        $line = false != $line ? $line : 10;
        if (!isset($params['openid']) || empty($params['openid'])) {
            return ['code'=>1030];
        }
//        $userinfo = self::getUserInfo($adminInfo, $params);
//        if (false == $userinfo) {
//            return ['code'=>2000];
//        }

//        $where['cardno'] = $userinfo['cardno'];

        $db = M('scanshoppingreceipt', $adminInfo['pre_table']);
        $count = $db->where(['openid'=>$params['openid']])->count('id');//总条数
        if ($count > 0){
            $totalPages = ceil($count/$line);//总页数
            $start = ($pageNum - 1) * $line;
            $sel = $db->where(['openid'=>$params['openid']])->limit($start, $line)->order('id desc')->select();
            return ['code'=>200, 'data'=>['totalpages'=>$totalPages,'dataCount'=>$count, 'pagenum'=>$pageNum, 'line'=>$line, 'data'=>$sel]];
        }else{
            return ['code'=>102];
        }
    }


    /**
     * 抽奖送券
     * @param $adminInfo
     * @param $id
     * @param $billSerialNumber
     * @param $openid
     * @return array
     */
    public static function receiptPrize($adminInfo, $id, $billSerialNumber, $openid)
    {
        if (empty($id) || empty($billSerialNumber) || empty($openid)) {
            return ['code'=>1030];
        }

        //判断是否已经没有抽奖次数
        if (true !== self::checkPrizeNums($adminInfo, $billSerialNumber, $openid, $id)){
            return ['code'=>1813];
        }
        $data = self::doReceiptPrize($id, $adminInfo, $billSerialNumber, $openid);
        return ['code'=>200, 'data'=>$data];
    }


    /**
     * 判断小票号，是否可以继续抽奖
     * @param $adminInfo
     * @param $billSerialNumber
     * @param $openid
     * @param $id
     * @return array|bool
     */
    private static function checkPrizeNums($adminInfo, $billSerialNumber, $openid, $id)
    {
        $isPrize = RedisService::connectredis()->get('receipt:prize:' . $id . ':' . $billSerialNumber . ':' . $openid . ':' . $adminInfo['ukey']);
        if ($isPrize) {
            RedisService::connectredis()->set('receipt:prize:' . $id . ':' . $billSerialNumber . ':' . $openid . ':' . $adminInfo['ukey'], 'yes', ['ex'=>600]);
            return ['code'=>1813];
        }
        $db = M('scanshoppingreceipt', $adminInfo['pre_table']);
        $find = $db->where(['id'=>$id, 'receiptid'=>$billSerialNumber, 'openid'=>$openid])->find();
        if ($find){
            //如果已抽奖的次数小于可抽奖的次数
            if ($find['prizednums'] < $find['prizenums']) {
                return true;
            }else{
                RedisService::connectredis()->set('receipt:prize:' . $id . ':' . $billSerialNumber . ':' . $openid . ':' . $adminInfo['ukey'], 'yes', ['ex'=>600]);
                return ['code'=>1813];
            }
        }else{
            return ['code'=>102];
        }
    }


    /**
     * 判断无误后可以抽奖、送券
     * @param $id
     * @param $adminInfo
     * @param $billSerialNumber
     * @param $openid
     * @return array
     */
    private static function doReceiptPrize($id, $adminInfo, $billSerialNumber, $openid)
    {
        $db = M('scanshoppingreceipt', $adminInfo['pre_table']);
        $dbcoupon = M('scanreceiptprizenums', $adminInfo['pre_table']);
        $sel = $dbcoupon->where(['scanid'=>$id])->select();
        //如果没有结果，那可能是列表内数据错误，更改列表内的数据为总抽奖次数
        if (count($sel) <= 0) {
            $find = $db->where(['id'=>$id, 'receiptid'=>$billSerialNumber, 'openid'=>$openid])->find();
            $db->where(['id'=>$id])->save(['prizednums'=>$find['prizenums']]);
            return ['code'=>1813];
        }
        //获取活动id，或者说该抽规则内的哪一个活动id的奖
        $activityid = null;
        foreach ($sel as $key => $value) {
            if ($value['prizednums'] < $value['prizenums']) {
                $activityid = $value['activityid'];
                break;
            }
        }
        $db->startTrans();
        $dbcoupon->startTrans();
        $db->where(['id'=>$id])->setInc('prizednums', 1);//列表内加1，记录已经抽奖的次数
        $dbcoupon->where(['scanid'=>$id])->setInc('prizednums', 1);
        $shopreceiptrebateprizeversion = PublicService::GetOneAmindefaul($adminInfo, 'shopreceiptrebateprizeversion');

        
        $data = [];
        //默认调用营销平台3.0接口
//        if ($shopreceiptrebateprizeversion['function_name'] == 'v3') {// || $shopreceiptrebateprizeversion == false避免不配置，总是读库，所以把第二个判断暂时注释
            $url = 'http://101.201.176.54/rest/act/' . $activityid . '/' . $openid . '?export=scanreceipt';
            $curl = http($url, array());
            $arr = json_decode($curl, true);
            if (is_array($arr)) {
                $db->commit();
                $db->commit();
                $selPrieNums = $db->field('prizednums,prizenums')->where(['id'=>$id])->find();//获取历史抽奖次数
                $data = [
                    'prize'=>$arr,
                    'prizednums'=>$selPrieNums,
                    'status'=>true,
                ];
            }else{
                $db->rollback();
                $db->rollback();
                $data = [
                    'prize'=>[],
                    'status'=>false,
                    'msg'=>'backerror'
                ];
            }
//        }elseif ($shopreceiptrebateprizeversion == 'v4'){
//            $db->rollback();
//            $db->rollback();
//            $data = [
//                'prize'=>[],
//                'status'=>false,
//                'msg'=>'nov4'
//            ];
//        }else{
//            $db->rollback();
//            $db->rollback();
//            $data = [
//                'prize'=>[],
//                'status'=>false,
//                'msg'=>'nosetttingprize'
//            ];
//        }
        return $data;
    }


    /**
     * 获取小票返利公共配置：规则，抽奖图标，或许以后还会有
     * @param $adminInfo
     * @return array
     */
    public static function getReceiptPublicSettings($adminInfo)
    {
        $db = M('receipt', $adminInfo['pre_table']);
        $find = $db->find();
        if ($find){
            $find['rule'] = htmlspecialchars_decode($find['rule']);//转码
            return ['code'=>200, 'data'=>$find];
        }else{
            return ['code'=>102];
        }
    }





}