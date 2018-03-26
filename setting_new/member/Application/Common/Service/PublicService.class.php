<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 13/09/2017
 * Time: 14:27
 */

namespace Common\Service;


use Common\Controller\CommonController;

class PublicService
{
    public static function publicinit()
    {
        $common = new CommonController();
        $common->__initialize();
//        dump($commons);
        return $common;
    }

    public static function returnstyle()
    {
        return self::publicinit()->returnstyle;
    }

    public static function callback()
    {
        return self::publicinit()->callback;
    }

    /**
     * 获取单条default配置
     * @param $table_pre
     * @param $key_admin
     * @param $function_name
     * @return mixed|null
     */
    public static function GetOneAmindefaul($adminInfo,$function_name)
    {
        $default= RedisService::connectredis()->get('admin:default:one:'.$function_name.':'. $adminInfo['ukey']);
        if ($default){
            return json_decode($default,true);
        }else{
            $dbm=M();
            $c=$dbm->execute('SHOW TABLES like "'.$adminInfo['pre_table'].'default"');
            if (1 !== $c){
                $sql="CREATE TABLE `".$adminInfo['pre_table']."default`  (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '索引id',
  `customer_name` varchar(50) NOT NULL COMMENT '用途',
  `function_name` text NOT NULL COMMENT '用途属性',
  `description` varchar(150) DEFAULT '' COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='商户常量配置表'";
                $dbm->execute($sql);
                return false;
            }else{
                $db=M('default',$adminInfo['pre_table']);
                $select=$db->where(array('customer_name'=>$function_name))->find();
                if ($select) {
                    RedisService::connectredis()->set('admin:default:one:'.$function_name.':'. $adminInfo['ukey'], json_encode($select),array('ex'=>86400));
                    return $select;
                }else{
                    return false;
                }

            }
        }
    }


    /**
     * 按商户密钥查询商户配置信息
     * @param $key_admin 商户密钥
     * @return array|mixed
     */
    public static function getMerchant($key_admin) {
        if (!$key_admin) {
            return ['code'=>1001];
        }
        $m_info = RedisService::connectredis()->get('member:' . $key_admin);
        if ($m_info) {
            return json_decode($m_info, true);
        } else {
            $merchant = M('total_admin');
            $re = $merchant->where(array('ukey' => $key_admin))->find();
            if ($re) {
                RedisService::connectredis()->set('member:' . $key_admin, json_encode($re),array('ex'=>86400));
            }else {
                return ['code'=>1001];
            }
            return $re;
        }
    }

    /**
     * 获取admin配置
     * @param $key_admin
     * @return bool
     */
//    public static function getMerchant($key_admin)
//    {
//        return self::publicinit()->getMerchant($key_admin);
//    }


    /**
     * 用openid获取用户信息（查库）
     * @param $params
     * @param $adminInfo
     * @param $adminDefault
     */
    public static function getUserInfoByOPenid($params, $adminInfo, $adminDefault)
    {
        $db = M('mem', $adminInfo['pre_table']);
        $userInfo = $db->where(['openid'=>$params['openid']])->find();
        return $userInfo;
    }

}