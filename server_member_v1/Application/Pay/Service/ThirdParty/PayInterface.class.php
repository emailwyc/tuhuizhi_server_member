<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 29/01/2018
 * Time: 16:00
 */

namespace Pay\Service\ThirdParty;


interface PayInterface
{

    /**
     * 支付下单
     * @return mixed
     */
    public static function requestOrder();


    /**
     * 退款
     * @return mixed
     */
    public static function refund();


    /**
     * 订单查询
     * @return mixed
     */
    public static function queryOrder();
}