<?php
/**
 * 世欧的erp为猫酷，crm是本公司营销平台，所以某些caozuo 可能同时处理两家接口。
 * Created by PhpStorm.
 * User: zhang
 * Date: 30/11/2017
 * Time: 17:04
 */

namespace ErpService\Service;


use Common\Service\PublicService;

class ShiouService implements ErpInterface
{
    private static $desKey = 'maoku!@#';
//    private static $salePlatformGiveScore = 'http://211.157.182.226:8888/rtscrm/crm-web-opr';
    private static $salePlatformGiveScore = 'http://apima.rtmap.com/crm-web-opr';
    private static $salePlaformMarketId = 12560;
    /**
     * 通过扫码获取小票信息
     * 世欧小票用des加密
     * @param $code
     * @param $adminInfo
     * @param $adminDefault
     * @return mixed
     * user kaifeng
     */
    public static function receiveInfoByScan($params, $adminInfo=null, $adminDefault=null)
    {
        //猫酷给的四个测试连接和文档上的链接，第一个最短的是文档内的
        //http://api.mallcoo.cn/qr/c?_mid=0&m=2001&d=K3HL6NcVQhj08ifuR9X5c3kF%2F8g94x800vEuwwojJnXauhLFLV6fYUBqtEAE10qk
        //http://jffcwx.cndrealty.com/s.php?_mid=S01&m=2001&d=tJ3DrmeZqgvx06hapfzEsl5wXHru6raMt2oTLpC8PI0lnrLiAx%2BrfDbKCCb7FpFYb8Vkn2xZXr4%3D
        //http://jffcwx.cndrealty.com/s.php?_mid=S01&m=2001&d=pBLPjpYHw5ccc8CVQAf2q1ZdH0sfBUOequf2lrYpuo9veVmpfigl2Z%2BTxFjNLNzUKckzIgWEwCc%3D
        //http://jffcwx.cndrealty.com/s.php?_mid=S01&m=2001&d=pBLPjpYHw5ccoTZE3IRWiNZjc%2BqxhhxeB%2F6fveMg5r87U5CoJUxc5O1tzLPMU21Bs6MVbqGjmFU%3D

        if (!isset($params['code'])){
            return ['code'=>1030, 'data'=>['w'=>'code','data'=>$params]];
        }

        $params['code'] = htmlspecialchars_decode(urldecode($params['code']));//转码code，传递的时候需要urlencode
        $queryParams = explode('&d=', $params['code']);//不能用parse_str函数，会把字符串内的某些字符删除
        if (count($queryParams) <= 1){
            return ['code'=>1505, 'data'=>['w'=>'param','data'=>$params]];
        }
        $encryptStr = urldecode($queryParams[1]);
        $code = base64_decode($encryptStr.'==');
        if (!$code){
            return ['code'=>1505, 'data'=>['w'=>'base64','data'=>$params]];
        }
        $decryptStr = openssl_decrypt($code, 'DES-ECB', self::$desKey, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, self::$desKey);
        if (!$decryptStr){
            return ['code'=>1505, 'data'=>['w'=>'dec','data'=>$params]];
        }
        $sArray = str_split($decryptStr);
        //可能由于语言问题，.net生成的加密密文解密后，最右有空白字符，但不是空格，遂将其转为ascii后删除32（含）以下的字符
        foreach ($sArray as $item) {
            $assii = ord($item);
            if ($assii <= 32){
                $decryptStr = preg_replace("/[\s\v".chr($assii)."]+$/","", $decryptStr); //删除此ASCII字符
            }
        }
        $decryptArr = explode(',', $decryptStr);
        if (is_array($decryptArr) && false != $decryptArr){
            $data = [
                'billSerialNumber'=>$decryptArr[0] . $decryptArr[3] . $decryptArr[1],//小票流水号
                'shopEntityId'=>$decryptArr[0],//实体店编号
                'terminalNumber'=>$decryptArr[4],//收银机号
                'saleTime'=>$decryptArr[2],//销售时间
                'paidAmount'=>$decryptArr[1],//实际消费金额
            ];
            $data['billInfo']['goodsDetails'] =[];
            return ['code'=>200, 'data'=>$data];
        }else{
            return ['code'=>'101', 'data'=>$decryptStr];
        }
    }

    /**
     * 通过手动编写信息获取小票信息
     * @param $code
     * @param $adminInfo
     * @param $adminDefault
     * @return mixed
     * @user kaifeng
     */
    public static function receiveInfoByWrite($params, $adminInfo=null, $adminDefault=null)
    {
        // TODO: Implement receiveInfoByWrite() method.
    }

    /**
     * 调用erp接口，erp赠送积分
     * @param $params
     * @param $adminInfo
     * @param $adminDefault
     * @return mixed
     * @user kaifeng
     */
    public static function giveScoreByReceive($params, $adminInfo=null, $adminDefault=null, $receiptInfo=null)
    {
        // TODO: Implement giveScore() method.
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