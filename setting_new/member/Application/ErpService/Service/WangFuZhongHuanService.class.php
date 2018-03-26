<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 02/11/2017
 * Time: 16:08
 */

namespace ErpService\Service;



use Common\Service\PublicService;

class WangFuZhongHuanService implements ErpInterface
{
//    private static $appKey = '360537866757285';//测试
//    private static $APPSECRET = "1BLA79TOIUH4UO0034GMGCB3L6NVHCRI";//测试
    private static $appKey = '283791709184767';
    private static $APPSECRET = "1BUHURJATRM4S0002OV0G34JBUC3KOMT";
//    private static $apiUrl = 'http://api.test.goago.cn/oapi/rest';//测试
    private static $apiUrl = 'http://api.gooagoo.com/oapi/rest';

    private static $salePlatformGiveScore = 'http://apima.rtmap.com/crm-web-opr';
    private static $salePlaformMarketId = 12556;
    /**
     * 扫码获取小票信息
     * @param $params
     * @param $adminInfo
     * @param $adminDefault
     * @return array
     */
    public static function receiveInfoByScan($params, $adminInfo=null, $adminDefault=null)
    {
        if (!isset($params['code'])){
            return ['code'=>1030, 'data'=>['w'=>'code','data'=>$params]];
        }
        $params['code'] = htmlspecialchars_decode(urldecode($params['code']));//转码code，传递的时候需要urlencode
        //王府中户的code在二维码的参数里面，二维码的参数是个链接，所以要截取字符串获取code，如http://weixin.qq.com/r/2jhXT9fEW4okrZMs923I/5175GGGGtikKHjgsklsisslgsjlgqoogs
        $arr = explode('/', $params['code']);
        $code = end($arr);
        $data = [
            'method'=>'gogo.bill.qrcode.query',
            'timestamp'=>date('YmdHis'),
            'messageFormat'=>'json',
            'appKey'=>self::$appKey,
            'v'=>'1.0',
            'signMethod'=>'MD5',
            'billFileName'=>$code
        ];
        $sign = sign($data, '&key=' . self::$APPSECRET);
        $data['sign'] = strtoupper($sign);

        $return = http(self::$apiUrl, $data);//请求接口，默认时间为5秒
        $retdata = json_decode($return, true);

        if (isset($retdata['errorToken']) || !isset($retdata['billInfo'])) {
            return ['code'=>104, 'data'=>['data'=>$retdata, 'y'=>'erp error']];
        }
        $retdata['billInfo']['orderid'] = $retdata['billInfo'] ['id'];

        foreach ($retdata['billInfo']['goodsDetails'] as $key => $value) {
            $retdata['billInfo']['goodsDetails'][$key]['class'] = false;
        }

        /**
         * 王府中环没有返回商品详情，其他项目用，暂时先写一个
         */
        $retdata['billInfo']['goodsDetails'][0]['class'] = 'aadsfadf97899hfdp38jsadouiouyterqjdfopighgerljkgfdirt9ipu';
        $retdata['billInfo']['goodsDetails'][0]['name'] = '无商品名';
        $retdata['billInfo']['goodsDetails'][0]['price'] = 10;
        $retdata['billInfo']['goodsDetails'][0]['totalprice'] = $return['billInfo']['paidAmount'];
//
//        $retdata['billInfo']['goodsDetails'][1]['class'] = 'e';
//        $retdata['billInfo']['goodsDetails'][1]['name'] = '商品名b';
//        $retdata['billInfo']['goodsDetails'][1]['price'] = 130;
//        $retdata['billInfo']['goodsDetails'][1]['totalprice'] = 656.3;
//
//        $retdata['billInfo']['goodsDetails'][2]['class'] = 'c';
//        $retdata['billInfo']['goodsDetails'][2]['name'] = '商品名c,品类c';
//        $retdata['billInfo']['goodsDetails'][2]['price'] = 190;
//        $retdata['billInfo']['goodsDetails'][2]['totalprice'] = 998.68;
//
//        $retdata['billInfo']['goodsDetails'][3]['class'] = 'a';
//        $retdata['billInfo']['goodsDetails'][3]['name'] = '商品名a,品类c';
//        $retdata['billInfo']['goodsDetails'][3]['price'] = 190;
//        $retdata['billInfo']['goodsDetails'][3]['totalprice'] = 998.68;
//
//        $retdata['billInfo']['goodsDetails'][4]['class'] = 'z';
//        $retdata['billInfo']['goodsDetails'][4]['name'] = '商品名z,品类z';
//        $retdata['billInfo']['goodsDetails'][4]['price'] = 190;
//        $retdata['billInfo']['goodsDetails'][4]['totalprice'] = 998.68;
//        $retdata['billInfo']['orderid']= $retdata['billInfo']['id'];

        return ['code'=>200, 'data'=>$retdata['billInfo']];
    }

    /**
     * 手动填写信息获取小票信息
     * @param $code
     * @param $adminInfo
     * @param $adminDefault
     */
    public static function receiveInfoByWrite($code, $adminInfo=null, $adminDefault=null)
    {
        // TODO: Implement receiveInfoByWrite() method.
    }

    /**
     * 调用erp接口，erp赠送积分
     * @param $params
     * @param $adminInfo
     * @param $adminDefault
     * @return mixed
     */
    public static function giveScoreByReceive($params, $adminInfo=null, $adminDefault=null, $receiptInfo=null)
    {
        $userInfo = PublicService::getUserInfoByOPenid($params, $adminInfo, $adminDefault);
        if (!$userInfo) {
            return ['code'=>2000];
        }
//        $userInfo['cardno'] = '18631462005';
        //此部分需要调用营销平台接口添加积分
        $url = self::$salePlatformGiveScore . '/api/v1/trade/makeUp';
        $time = strtotime($receiptInfo['data']['saleTime']);
        $time = date('Y-m-d H:i:s', $time);
        $requestData = [
            'cardNo'=>$userInfo['cardno'],
            'tradeType'=>0,
            'orderNo'=>$receiptInfo['data']['billSerialNumber'],
            'shopNo'=>$receiptInfo['data']['shopEntityId'],//'SHOP1000006',
            'tradeTime'=>$time,//$receiptInfo['data']['saleTime'],
            'amount'=>$receiptInfo['data']['paidAmount'],// * 100,
            'marketId'=>self::$salePlaformMarketId
        ];

        $request = http($url, json_encode($requestData), 'POST', ['Content-Type:application/json;charset=UTF-8'], true);
        if (!is_json($request)){
            return ['code'=>101, 'data'=>$request];
        }

        $responseData = json_decode($request, true);
        if (isset($responseData['status']) &&$responseData['status'] == 200){
            return [
                'code'=>200, 'data'=>[
                    'score'=>$responseData['data']['score'],
                    'scoreTotal'=>$responseData['data']['scoreTotal'],
                ]
            ];
        }else{
            return ['code'=>104, 'data'=>$responseData];
        }
    }

    /**
     * 获取兑换记录列表
     * @param $adminInfo
     * @param $adminDefault
     * @param $params
     * @return mixed
     * @user zhanghang
     */
    public static function getExchangeList($params, $adminInfo=null, $adminDefault=null)
    {
        // TODO: Implement getExchangeList() method.
    }

    /**
     * 兑换礼品接口
     * @param $adminInfo
     * @param $adminDefault
     * @param $params
     * @return mixed
     * @user zhanghang
     */
    public static function prizeExchange($params, $adminInfo=null, $adminDefault=null)
    {
        // TODO: Implement prizeExchange() method.
    }

    /**
     * 礼品退还接口
     * @param $adminInfo
     * @param $adminDefault
     * @param $params
     * @return mixed
     * @user zhanghang
     */
    public static function prizeReturn($params, $adminInfo=null, $adminDefault=null)
    {
        // TODO: Implement prizeReturn() method.
    }

    /**
     * 获取会员礼品列表
     * @param $adminInfo
     * @param $adminDefault
     * @param $params
     * @return mixed
     * @user zhanghang
     */
    public static function prizeList($params, $adminInfo=null, $adminDefault=null)
    {
        // TODO: Implement prizeList() method.
    }


}