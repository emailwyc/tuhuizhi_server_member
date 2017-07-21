<?php
/**
 * Created by PhpStorm.
 * User: jaleel
 * Date: 8/31/16
 * Time: 2:19 PM
 */

namespace Parkservice\Controller;

use Think\Controller;

use PublicApi\Controller\QiniuController;

class AoYongParkController extends Controller implements ParkinterfaceController
{
    // 科拓停车系统请求相关配置
    protected $url = 'http://pay1.keytop.cn:8099/';
    protected $appId = 29;
    protected $appKey = 'de5b028eb8bd41ef8959ae5b65c842a2';
    protected $parkId = 355;

    /**
     * 获取剩余车位数
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getleftpark($sign_key,$key_admin = '') {
        $data['appId'] = $this->appId;
        $data['key'] = md5(date('Ymd').$this->appKey);
        $this_url = $this->url . 'api/wec/GetParkingLotList';
        $result = http($this_url, $data, 'POST');

        if (!is_json($result)){
            return false;
        }

        $array=json_decode($result,true);

        if (0==$array['resCode']){
            return $array['data'];
        }else{
            return (string)$array['resCode'];
        }
    }


    /**
     * 搜索车辆列表 因科拓没有车辆搜索接口 所以此处返回正确车俩的停车信息
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function searchcar($carno,$sign_key,$key_admin = '',$page,$lines) {
        $data['appId'] = $this->appId;
        $data['parkId'] = $this->parkId;
        $data['plateNo'] = $carno;
        $data['key'] = md5($this->parkId . $carno . date('Ymd') . $this->appKey);
        $this_url = $this->url . 'api/wec/GetParkingPaymentInfo';
        $result = http($this_url, $data, 'POST');

        if (!is_json($result)){
            return false;
        }

        $array=json_decode($result,true);
        if (0==$array['resCode']){
            $re_data['BeginTime'] = $array['data'][0]['entryTime'];
            $re_data['EndTime'] = $array['data'][0]['payTime'];
            $re_data['IntValue'] = $array['data'][0]['payable'] / 100; // 奥永的积分暂时定为一元钱为一积分
            $re_data['MoneyValue'] = $array['data'][0]['payable'] / 100; // 奥永返回的是分 需要转换成元
            $re_data['orderNo'] = $array['data'][0]['orderNo'];
            $re_data['VIPBaseBonus'] = $array['data'][0]['VIPBaseBonus'];
            $re_data['VIPIntValue'] = $array['data'][0]['VIPIntValue'];
            $re_data['carimg'] = $array['data'][0]['imgName'];
            $re_data['CarSerialNo'] = $carno;
            return array($re_data);
        }else{
            return (string)$array['resCode'];
        }
    }

    /**
     * 从列表中选择我的车辆信息
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function choosemycar($carno,$sign_key,$key_admin = '') {
        $data['appId'] = $this->appId;
        $data['parkId'] = $this->parkId;
        $data['plateNo'] = $carno;
        $data['key'] = md5($this->parkId . $carno . date('Ymd') . $this->appKey);
        $this_url = $this->url . 'api/wec/GetParkingPaymentInfo';

        $result = http($this_url, $data, 'POST');

        if (!is_json($result)){
            return false;
        }

        $array=json_decode($result,true);
        if (0==$array['resCode']){
            $re_data['BeginTime'] = $array['data'][0]['entryTime'];
            $re_data['EndTime'] = $array['data'][0]['payTime'];
            $re_data['discount'] = 100; // 活动折扣
            $re_data['PayValue'] = $array['data'][0]['payable']; // 应付金额 单位分
            $re_data['IntValue'] = $array['data'][0]['payable'] / 100; // 奥永的积分暂时定为一元钱为一积分
            $re_data['MoneyValue'] = ($array['data'][0]['payable']) * ($re_data['discount'] / 100); // 奥永返回的是分
//            $re_data['MoneyValue'] = 1; // 奥永返回的是分
            $re_data['discountValue'] = ($array['data'][0]['payable']) - $re_data['MoneyValue']; // 活动折扣金额 单位为分
            $re_data['orderNo'] = $array['data'][0]['orderNo'];
            $re_data['VIPBaseBonus'] = $array['data'][0]['VIPBaseBonus'];
            $re_data['VIPIntValue'] = $array['data'][0]['VIPIntValue'];

            // 对车的图片做处理 将图片拉到七牛上 然后返回相应的url
            $qi = new QiniuController();
            $re = $qi->qiniu_fetch($array['data'][0]['imgName'],'img/carPic/' . strtolower($carno));
            writeOperationLog(array('qiniu fetch result:' . json_encode($re)), 'jaleel_logs');

            if (is_array($re) && isset($re[0]['key'])) {
                $re_data['carimg'] = 'https://oe5n68bv6.qnssl.com/' . $re[0]['key'];
            }

            $re_data['CarSerialNo'] = $carno;
            $need['code'] = 200;
            $need['msg'] = 'success';
            $need['data'] = $re_data;
        }else{
            $need['code'] = $array['resCode'];
            $need['msg'] = $array['resMsg'];
            $need['data'] = array();
        }
        return $need;
    }

    /**
     * 支付状态确认
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function paystatus($carno, $sign_key, $paytype, $key_admin = '', $orderNo = '', $amount = '', $discount = '') {
        $data['appId'] = $this->appId;
        $data['orderNo'] = $orderNo; // orderNo 000120160908184707183 车牌号 闽D02AB2 闽D02AB3 测试用
        $data['amount'] = $amount;
        $data['discount'] = $discount;
        $data['points'] = $amount;
        $data['dType'] = 0;
        $data['dValue'] = $amount;
        $data['key'] = md5($orderNo . $amount . $discount . date('Ymd') . $this->appKey);

        // 不同类型的支付所请求的url地址不同
        if ($paytype == 0) { // 微信支付
            $this_url = $this->url . 'api/wec/PayParkingFee_Wec';
        } else if ($paytype == 1) { // 积分支付
            $this_url = $this->url . 'api/wec/MemberDeduction';
            $data['key'] = md5($orderNo . $data['points'] . $data['dType'] . $data['d'] . date('Ymd') . $this->appKey);
        } else if ($paytype == 2) { // 支付宝支付
            $this_url = $this->url . 'api/wec/PayParkingFee_AliPay';
        }

        $result = http($this_url, $data, 'POST');
        file_put_contents('aoyong.txt', $result, FILE_APPEND);

        if (!is_json($result)){
            return false;
        }

        $array=json_decode($result,true);
        if (0==$array['resCode']){
            return $array['data'];
        }else{
            return (string)$array['resCode'];
        }
    }


    /**
     * 车位详细状态查询
     * @param unknown $build
     * @param unknown $floor
     * @param unknown $sign_key
     * @param string $key_admin
     * @param $admininfo
     * @return bool|string
     * @throws \Exception
     * 正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getparkstatus($build,$floor,$sign_key,$key_admin = '',$admininfo) {
        $data['appId'] = $this->appId;
        $data['parkId'] = $build;
        $data['key'] = md5($build.date('Ymd').$this->appKey);
        $this_url = $this->url . 'api/wec/GetParkingLotList';
        $result = http($this_url, $data, 'POST');

        if (!is_json($result)){
            return false;
        }

        $array=json_decode($result,true);
        if (0==$array['resCode']){
            return $array['data'];
        }else{
            return (string)$array['resCode'];
        }
    }

}