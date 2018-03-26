<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 02/11/2017
 * Time: 16:09
 */

namespace ErpService\Service;


class ErpCommonService
{
    /**
     * 获取小票信息
     * @param $params
     * @param $adminInfo
     * @param $adminDefault
     * @return array
     */
    public static function receiveInfoByScan($params, $adminInfo=null, $adminDefault=null)
    {
        $data = $adminDefault['function_name']::receiveInfoByScan($params, $adminInfo, $adminDefault);
        return $data;
    }


    /**
     * 获取小票信息
     * @param $params
     * @param $adminInfo
     * @param $adminDefault
     * @return array
     */
    public static function receiveInfoByWrite($params, $adminInfo=null, $adminDefault=null)
    {
        $data = $adminDefault['function_name']::receiveInfoByWrite($params, $adminInfo, $adminDefault);
        return $data;
    }


    /**
     * 调用erp接口，erp送积分
     * @param $params
     * @param $adminInfo
     * @param $adminDefault
     */
    public static function giveScoreByReceive($params, $adminInfo=null, $adminDefault=null, $receiptInfo=null)
    {
        $data = $adminDefault['function_name']::giveScoreByReceive($params, $adminInfo, $adminDefault, $receiptInfo);
        return $data;
    }



    /**
     * 获取兑换记录列表
     * @param $params
     * @return mixed
     */
    public static function getExchangeList($params, $adminInfo=null, $adminDefault=null)
    {
        $data = $adminDefault['function_name']::getExchangeList($params, $adminInfo, $adminDefault);
        return $data;
    }

    /**
     * 兑换礼品接口
     * @param $params
     * @return mixed
     */
    public static function prizeExchange($params, $adminInfo=null, $adminDefault=null)
    {
        $data = $adminDefault['function_name']::prizeExchange($params, $adminInfo, $adminDefault);
        return $data;
    }

    /**
     * 礼品退还接口
     * @param $params
     * @return mixed
     */
    public static function prizeReturn($params, $adminInfo=null, $adminDefault=null)
    {
        $data = $adminDefault['function_name']::prizeReturn($params, $adminInfo, $adminDefault);
        return $data;
    }

    /**
     * 获取会员礼品列表
     * @param $params
     * @return mixed
     */
    public static function prizeList($params, $adminInfo=null, $adminDefault=null)
    {
        $data = $adminDefault['function_name']::prizeList($params, $adminInfo, $adminDefault);
        return $data;
    }


}