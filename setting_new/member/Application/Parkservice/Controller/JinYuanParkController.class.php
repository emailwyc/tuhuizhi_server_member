<?php
/**
 * Created by PhpStorm.
 * User: jaleel
 * Date: 10/26/16
 * Time: 3:20 PM
 */

namespace Parkservice\Controller;

use Think\Controller;

use PublicApi\Controller\QiniuController;

class JinYuanParkController extends Controller implements ParkinterfaceController
{
    // 科拓停车系统请求相关配置
    protected $url = 'http://101.200.187.233:8080/';
    protected $parkKey = 'faef0976c28d425ab3a121536749d386';//31e7ab352628a2dd775b157f0f722eed

    /**
     * 获取剩余车位数
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getleftpark($sign_key,$key_admin = '') {
        $data['parkKey'] = $this->parkKey;
        $data['key_admin'] = $key_admin;
        $this_url = $this->url . 'park/rest/api/parkSpace';
        $result = http($this_url, $data);

        if (!is_json($result)){
            return false;
        }

        $array=json_decode($result,true);

        if (200==$array['code']){
            $return_data = array(
                array('location' => 'total', 'leftnum' => $array['data']['remai_spaces']),
            );
            return $return_data;
        }else{
            return (string)$array['code'];
        }
    }


    /**
     * 世纪金源没有搜索车俩列表接口 因此此接口只是查询本地库中的此车牌号没有支付的定单信息
     */
    public function searchcar($carno,$sign_key,$key_admin = '',$page,$lines) {

        // 查询此车牌号最近一次没有支付的订单
        $order = M('shijijinyuan_carpay_order');
        $result = $order->where(array('carno' => $carno, 'status' => 0))->order('createtime desc')->limit(1)->select();

        if (!$result){
            return '1';
        }

        $re_data['BeginTime'] = $result[0]['begintime'];
        $re_data['EndTime'] = time();
        $re_data['IntValue'] = 0;
        $re_data['MoneyValue'] = $result[0]['total_fee'];
        $re_data['orderNo'] = $result[0]['client_orderno'];
        $re_data['VIPBaseBonus'] = 0;
        $re_data['VIPIntValue'] = 0;
        $re_data['carimg'] = '';
        $re_data['CarSerialNo'] = $carno;
        return array($re_data);
    }

    /**
     * 从列表中选择我的车辆信息
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function choosemycar($carno,$sign_key,$key_admin = '', $orderNo = '') {
        $data['parkKey'] = $this->parkKey;
        $data['key_admin'] = $key_admin;
        $data['orderNo'] = $orderNo;
        $this_url = $this->url . 'park/rest/api/order';

        $result = http($this_url, $data);

        if (!is_json($result)){
            return false;
        }

        $array=json_decode($result,true);

//        var_dump($array);die;

        if (200==$array['code']){
            $re_data['BeginTime'] = $array['data']['enter_Time'];
            $re_data['EndTime'] = date('Y-m-d H:i:s');
            $re_data['MoneyValue'] = $array['data']['total_value'] * 100;
            $re_data['orderNo'] = $orderNo;
            $re_data['VIPBaseBonus'] = 0;
            $re_data['VIPIntValue'] = 0;
            $re_data['CarSerialNo'] = $carno;
            return $re_data;
        }else{
            return (string)$array['code'];
        }
    }

    /**
     * 支付状态确认
     * 正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function paystatus($carno, $sign_key, $paytype, $key_admin = '', $orderNo = '', $amount = '', $discount = '') {
        $data['key_admin'] = $key_admin;
        $data['parkKey'] = $this->parkKey;
        $data['orderNo'] = $orderNo;
        $data['payable'] = $amount / 100;
        $data['realPay'] = $amount / 100 - $discount / 100;
        $data['payTime'] = time();

        $this_url = $this->url . 'park/rest/api/notify';

        $result = http($this_url, $data);

        if (!is_json($result)){
            return false;
        }

        $array=json_decode($result,true);
        if (0==$array['code']){
            return $array;
        }else{
            return (string)$array['code'];
        }
    }


    /**
     * 车位详细状态查询(未提供)
     * 正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getparkstatus($build,$floor,$sign_key,$key_admin = '',$admininfo) {}

    /**
     * 车俩入场下单接口 车场主动调用(对外)
     */
    public function enterCar() {
        $parkKey = I('parkKey');
        $data['carno'] = I('carNo');
        $data['client_orderno'] = I('orderNo');
        $data['begintime'] = strtotime(I('enterTime'));
        $data['createtime'] = time();

        if (empty($parkKey) or empty($data['carno']) or empty($data['client_orderno']) or empty($data['begintime'])) {
            $data = array('relustcode' => 0, 'msg' => 'invalid parameters!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $order = M('shijijinyuan_carpay_order');

        $order_info = $order->where(array('client_orderno' => $data['client_orderno']))->find();

        if (!$order_info) {
            $re = $order->add($data);

            if (!$re) {
                $data = array('relustcode' => 0, 'msg' => 'system error!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }

        $data = array('relustcode' => 1, 'msg' => 'SUCCESS!');
        echo json_encode($data);
    }

    /**
     * 车俩出场接口 车场主动调用(对外)
     * 当用户线下支付时也会调用此接口
     */
    public function outCar() {
        $parkKey = I('parkKey');
        $data['carno'] = I('carNo');
        $data['client_orderno'] = I('orderNo');
        $data['endtime'] = strtotime(I('outTime'));
        $data['total_fee'] = I('totalAmount');

        if (empty($parkKey) or empty($data['carno']) or empty($data['client_orderno']) or empty($data['endtime']) or empty($data['total_fee'])) {
            $data = array('relustcode' => 0, 'msg' => 'invalid parameters!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $order = M('shijijinyuan_carpay_order');
        $order_info = $order->where(array('client_orderno' => $data['client_orderno']))->find();

        if (!$order_info) {
            $data = array('relustcode' => 0, 'msg' => 'invalid order no.!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $save_data = array(
            'endtime' => $data['endtime'],
            'total_fee' => $data['total_fee'],
            'status' => 2,
        );

        // 实际支付金额为空则说明是线下支付的
        if ($order_info['payfee'] == '') {
            $save_data['payfee'] = $data['total_fee'];
            $save_data['pay_time'] = time();
        }

        $re = $order->where(array('client_orderno' => $data['client_orderno']))->save($save_data);

        if ($re === false) {
            $data = array('relustcode' => 0, 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data = array('relustcode' => 1, 'msg' => 'SUCCESS!');
        echo json_encode($data);
    }
}