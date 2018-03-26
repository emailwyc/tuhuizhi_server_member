<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 02/11/2017
 * Time: 16:14
 */

namespace ErpService\Service;


interface ErpInterface
{
    /**
     * @param $code
     * @param $adminInfo
     * @param $adminDefault
     * @return 按下面的字段返回
     * @user kaifeng
    {
    "id": "297AC0E77C444D558F8F2D2524E1F61B",
    "shopId": "19BHG010KRAVLL2G8PEQCP2D7N714J6T",
    "shopName": "测试商家",
    "shopEntityId": "19BHG010KRAVLL2G8PEQCP2D7N714J6T",
    "shopEntityName": "测试实体店",
    "shopEntityFullName": "测试实体店",
    "shopEntityAddress": "北京朝阳",
    "telephone": "108888888",
    "saler": "售货员",
    "checkstand": "123",
    "cashier": "收银员",
    "receivableAmount": "1.25",
    "totalNum": "123213",
    "billSerialNumber": "1.25",
    "totalFee": "1.25",
    "paidAmount": 100.96,
    "discountAmount": "0",
    "couponAmount": "0",
    "changeAmount": "1",
    "settlementWay": [
    {
    "a": 5,
    "p": 1
    },
    {
    "a": 95.96,
    "p": 2
    }
    ],
    "saleTime": "2015-08-2015:22:27",
    "memberCardNumber": "1231232432",
    "totalConsumption": "1232.25",
    "billImage": "www.goago.cn/img/001",
    "goodsDetails": [
    {
    "name": "可乐",
    "itemserial": "PUMU00123",
    "price": 5.01,
    "totalnum": 5,
    "totalprice": 25.05
    }
    ]
    }
     */
    /**
     * 通过扫码获取小票信息
     * @param $code
     * @param $adminInfo
     * @param $adminDefault
     * @return mixed
     */
    public static function receiveInfoByScan($params, $adminInfo=null, $adminDefault=null);

    /**
     * 通过手动编写信息获取小票信息
     * @param $code
     * @param $adminInfo
     * @param $adminDefault
     * @return mixed
     */
    public static function receiveInfoByWrite($params, $adminInfo=null, $adminDefault=null);

    /**
     * 调用erp接口，erp赠送积分
     * @param $params
     * @param $adminInfo
     * @param $adminDefault
     * @return mixed
     */
    public static function giveScoreByReceive($params, $adminInfo=null, $adminDefault=null, $receiptInfo=null);


    /**
     * 获取兑换记录列表
     * @param $adminInfo
     * @param $adminDefault
     * @param $params
     * @return mixed
     */
    public static function getExchangeList($params, $adminInfo=null, $adminDefault=null);


    /**
     * 兑换礼品接口
     * @param $adminInfo
     * @param $adminDefault
     * @param $params
     * @return mixed
     */
    public static function prizeExchange($params, $adminInfo=null, $adminDefault=null);


    /**
     * 礼品退还接口
     * @param $adminInfo
     * @param $adminDefault
     * @param $params
     * @return mixed
     */
    public static function prizeReturn($params, $adminInfo=null, $adminDefault=null);


    /**
     * 获取会员礼品列表
     * @param $adminInfo
     * @param $adminDefault
     * @param $params
     * @return mixed
     */
    public static function prizeList($params, $adminInfo=null, $adminDefault=null);


}