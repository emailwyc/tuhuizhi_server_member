<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 29/01/2018
 * Time: 17:53
 */

namespace DPlatform\Service;


class DPlatformService
{
//    protected static $api_url = 'http://d-platform.zqs1023.wang/d-platform-web';//外包测试接口地址

    //获取人员信息
    public static function getDPlatformUserInfo($keyAdmin, $openid){
        $url = C('D_PLATFORM') .'/dkpt/get_auth_info';

        $return = http($url,array('keyAdmin'=>$keyAdmin,'openId'=>$openid));

        $perData = json_decode($return,true);

        if($perData['errcode'] != 0 || empty($perData['data']) || !isset($perData['data']['id'])){//判断，最后加一个数组key是否存在，因为返回结果有问题

             return ['code'=>2000];
        }

        return $perData['data'];
    }
}