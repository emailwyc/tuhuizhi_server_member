<?php
/**
 * Created by PhpStorm.
 * 获取大B端配置送券设置，放入缓存
 * User: zhang
 * Date: 17/11/2017
 * Time: 19:29
 */

namespace ShoppingReceipt\Service;


use Common\Service\RedisService;

class RebateSettingsService
{


    /**
     * 获取送券配置
     * 此部分内容，可以放到大B端设置完成后生成缓存，以避免c端第一个用到此功能的用户等待时间加长，大B调一下此方法即可，但数据仅设为存储一天，所以在实际使用时，效率究竟有多少提升没有测试
     * @param $adminInfo
     * @return bool|mixed
     */
    public static function getRebeatSettings($adminInfo)
    {
        //redis单个key大小最大512M
        $times = RedisService::connectredis()->get('shopping:receipt:rebate:rules:times:' . $adminInfo['id']);
        if (!$times) {
            $dbsettings = M('scanshoppingreceiptsettings', $adminInfo['pre_table']);
            $dbcoupons = M('scanshoppingreceiptcouponsettings', $adminInfo['pre_table']);
            $selSettings = $dbsettings->select();
            if ($selSettings && count($selSettings) > 0){
                foreach ($selSettings as $key => $value) {//所有规则数据
                    $selcoupons = null;
                    $selcoupons = $dbcoupons->where(['settingsid'=>$value['id']])->select();
                    $classAllKeys = [];
                    foreach ($selcoupons as $key2 => $value2) {//单条规则数据
                        $selcoupons[$key2]['classes'] = json_decode($value2['classes'], true);//数据为原数据，此刻没有用到
                        foreach ($selcoupons[$key2]['classes'] as $k => $v) {
                            $selcoupons[$key2]['classeskeys'][$v] = $key2;//值和二维数组的key互换
                        }
                        $classAllKeys = array_merge($classAllKeys, $selcoupons[$key2]['classeskeys']);
                    }
                    $selSettings[$key]['classesAllKeys'] = $classAllKeys;
                    $selSettings[$key]['couponSettings'] = $selcoupons;
                }
                RedisService::connectredis()->set('shopping:receipt:rebate:rules:times:' . $adminInfo['id'], json_encode($selSettings), array('ex'=>86400));
                return $selSettings;
            }else{
                return false;
            }
        }else{
            return json_decode($times, true);
        }
    }
}