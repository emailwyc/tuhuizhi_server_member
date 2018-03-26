<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 26/10/2017
 * Time: 11:38
 */

namespace PublicApi\Service;


class OutSourceService
{


    /**
     * 验证第三方调用接口时的签名规则
     * @param $params 所有参数
     * @param $adminInfo
     * @return array 返回200表示验证通过，否则失败
     */
    public static function verifySign($params, $adminInfo)
    {
        //是否包含敏感的签名key
        if (array_key_exists('sign_key', $params)) {
            return array('code'=>1051, 'data'=>2);
        }
        //是否传递了签名值
        if (!isset($params['sign'])) {
            return array('code'=>1051, 'data'=>3);
        }
        //验证签名
        $paramssign = $params['sign'];
        unset($params['sign']);
        $params['sign_key'] = $adminInfo['signkey'];
        $sign = sign($params);
        if ($sign != $paramssign) {
            return array('code'=>1002);
        }
        return array('code'=>200);
    }
}