<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 29/01/2018
 * Time: 15:58
 */

namespace Pay\Service\ThirdParty;


class WangFuZhongHuanService implements PayInterface
{
    /**
     * 支付下单
     * @return mixed
     */
    public static function requestOrder()
    {
        // TODO: Implement requestOrder() method.
        return 34643;
    }

    /**
     * 退款
     * @return mixed
     */
    public static function refund()
    {
        // TODO: Implement refund() method.
    }

    /**
     * 订单查询
     * @return mixed
     */
    public static function queryOrder()
    {
        // TODO: Implement queryOrder() method.
    }

}