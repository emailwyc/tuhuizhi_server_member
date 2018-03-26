<?php
/**
 * 水晶城停车缴费支付应用类
 * User: jaleel
 * Date: 7/14/16
 * Time: 3:07 PM
 */

namespace ParkApp\Controller;

use Common\Controller\JaleelController;

class ParkPayController extends JaleelController
{
    /**
     * 获取空闲车位数
     * @throws \Exception
     */
    public function getfreeparking() {
        $mer_chant = $this->getMerchant($this->ukey);
        $data['key_admin'] = $this->ukey;
        $data['sign_key'] = $mer_chant['signkey'];
        $data['sign'] = sign($data);
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/Parkservice/Parkoutput/get_left_park';
        $curl_re = http($url, $data, 'post');
        dump($curl_re);die;
        writeOperationLog(array('get free parking' => $curl_re), 'jaleel_logs');
        $data = json_decode($curl_re, true);
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 按车牌号搜索车辆
     * @throws \Exception
     */
    public function searchcar() {
        $carno = I('carno');
        if (!$carno) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $mer_chant = $this->getMerchant($this->ukey);
        $data['key_admin'] = $this->ukey;
        $data['sign_key'] = $mer_chant['signkey'];
        $data['carno'] = $carno;
        $data['sign'] = sign($data);
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/Parkservice/Parkoutput/searchcar';
        $curl_re = http($url, $data, 'post');

        writeOperationLog(array('search car result' => $curl_re), 'jaleel_logs');
        $data = json_decode($curl_re, true);
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 选择我的车
     * @throws \Exception
     */
    public function choosecar() {
        $carno = I('carno');
        if (!$carno) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $mer_chant = $this->getMerchant($this->ukey);

        //获取车的信息
        $data = $this->getCarInfo($carno, $mer_chant['signkey']);
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 按车牌号查询停车的详细信息
     * @param $carno
     * @param $signkey
     * @return mixed
     * @throws \Exception
     */
    protected function getCarInfo($carno, $signkey) {
        $data['key_admin'] = $this->ukey;
        $data['sign_key'] = $signkey;
        $data['carno'] = $carno;
        $data['sign'] = sign($data);
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/Parkservice/Parkoutput/choosemycar';
        $curl_re = http($url, $data, 'post');
        writeOperationLog(array('choose car result' => $curl_re), 'jaleel_logs');
        $data = json_decode($curl_re, true);
        $data['data']['MoneyValue'] = $data['data']['MoneyValue'] / 100; // 返回实际支付金额单位是分 转换成元
        $data['data']['discountValue'] = $data['data']['discountValue'] / 100; // 返回优惠金额单位是分 转换成元
        $data['data']['PayValue'] = $data['data']['PayValue'] / 100; // 返回应付金额单位是分 转换成元
        return $data;
    }

    /**
     * 获取会员停车信息接口(包含会员积分余额)
     * @throws \Exception
     */
    public function getpayinfo() {
        $carno = urldecode(I('carno'));
        if (!$carno) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);

        $car = $this->getCarInfo($carno, $mer_chant['signkey']);
        $score_fee = $car['data']['IntValue']; // 单位积分
        $rmb_fee = $car['data']['MoneyValue']; // 单位元
        $begin = $car['data']['BeginTime'];
        $end = $car['data']['EndTime'];

        // 查询会员信息
        $uinfo = $this->getUserCardByOpenId($mer_chant['pre_table'], $this->user_openid);
        if (!$uinfo) {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 根据会员卡号查询会员积分
        $post_data['card'] = $uinfo['cardno'];
        $post_data['key_admin'] = $this->ukey;
        $post_data['sign_key'] = $mer_chant['signkey'];
        $post_data['sign'] = sign($post_data);
        unset($post_data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/getuserinfobycard';
        $curl_uinfo = http($url, $post_data, 'post');
        writeOperationLog(array('park car get member by card' => $curl_uinfo), 'jaleel_logs');

        $uinfo_arr = json_decode($curl_uinfo, true);

        if ($uinfo_arr['errcode'] != 200) {
            $data = array('code' => '2000', 'msg' => '会员卡号不存在!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data = array('code' => '200', 'msg' => 'SUCCESS!', 'data' => array('score_pay' => $score_fee, 'rmb_pay' => $rmb_fee, 'bonus' => $uinfo_arr['data']['score'], 'begin' => $begin, 'end' => $end));
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 积分支付下单接口
     */
    public function cscoreorder() {
        $carno = urldecode(I('carno'));

        // 验证参数
        if (!$carno or !$this->user_openid) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);

        $car = $this->getCarInfo($carno, $mer_chant['signkey']);
        //$total_fee = $car['data']['IntValue']; // 单位积分
        $begintime = strtotime($car['data']['BeginTime']); // 停车起始时间
        $endtime = strtotime($car['data']['EndTime']); // 停车结束时间
        $client_orderno = $car['data']['orderNo'];
        $total_fee = 1; // 单位积分

        /**
         * 支付积分为零则直接提示下单失败
         */
        if ($total_fee == 0) {
            $data = array('code' => '5000', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询会员信息
        $uinfo = $this->getUserCardByOpenId($mer_chant['pre_table'], $this->user_openid);
        if (!$uinfo) {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 插入定单
        $order = M('carpay_order', $mer_chant['pre_table']);
        $order_no = uniqid('jf') . date('YmdHis') . rand(1000, 9999);
        $in_order = $this->createOrder($order, $carno, $total_fee, 1, $order_no, $begintime, $endtime, $client_orderno);

        if (!$in_order) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data = array('code' => '200', 'msg' => 'SUCCESS!', 'data' => array('orderNo' => $order_no, 'payscore' => $car['data']['IntValue']));
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 积分下单接口(对外)
     * 需要签名验证
     * 此处需要传递支付的积分数 因为此外是对接的轻停 他们是优惠一定数量的积分 具体的积分数是他们传递过来的
     */
    public function cscoreorderv2() {
        $carno = urldecode(I('carno'));
        $score = I('score');
        $timestamp = I('timestamp');
        $sign_par = I('sign');

        // 验证参数
        if (!$carno or !$this->user_openid or !$timestamp or !$this->ukey or !$sign_par or !$score) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);

        $sign_arr = array(
            'key_admin' => $this->ukey,
            'openid'    => $this->user_openid,
            'carno'     => $carno,
            'timestamp' => $timestamp,
            'score'     => $score,
            'sign_key'  => $mer_chant['signkey'],
        );

        $sign = sign($sign_arr);
        writeOperationLog(array('调用方传递的参数:' . json_encode($sign_arr) . "\n\n"), 'jaleel_logs');
        writeOperationLog(array('调用方传递的sign:' . $sign_par . "\n\n"), 'jaleel_logs');
        writeOperationLog(array('我方加密码的sign:' . $sign . "\n\n"), 'jaleel_logs');

        // 签名错误
        if ($sign != $sign_par) {
            $data = array('code' => '1002', 'msg' => 'invalid sign!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $total_fee = $score; // 单位积分

        /**
         * 支付积分为零则直接提示下单失败
         */
        if ($total_fee == 0) {
            $data = array('code' => '5000', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询会员信息
        $uinfo = $this->getUserCardByOpenId($mer_chant['pre_table'], $this->user_openid);
        if (!$uinfo) {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 插入定单
        $order = M('carpay_order', $mer_chant['pre_table']);
        $order_no = uniqid('jfo') . date('YmdHis') . rand(1000, 9999);
        $in_order = $this->createOrder($order, $carno, $total_fee, 1, $order_no, '', '', '');

        if (!$in_order) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data = array('code' => '200', 'msg' => 'SUCCESS!', 'data' => array('orderNo' => $order_no));
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 积分支付接口
     * @throws \Exception
     */
    public function paybyscore() {
        $carno = urldecode(I('carno'));
        $orderNo = I('orderno');
        if (!$carno or !$orderNo or !$this->user_openid) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);

        // 查询会员信息
        $uinfo = $this->getUserCardByOpenId($mer_chant['pre_table'], $this->user_openid);
        if (!$uinfo) {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $car = $this->getCarInfo($carno, $mer_chant['signkey']);
//        $total_fee = $car['data']['IntValue']; // 单位积分
        $total_fee = 1; // 单位积分

        /**
         * 支付积分为零则直接提示下单失败
         */
        if ($total_fee == 0) {
            $data = array('code' => '5000', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询定单信息
        $order = M('carpay_order', $mer_chant['pre_table']);
        $order_info = $order->where(array('orderno' => $orderNo))->find();

        if (!$order_info) {
            $data = array('code' => '2012', 'msg' => '无效定单!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 判断下单时间是否超过十分钟
        if (is_array($order_info) && time()-$order_info['createtime'] > 600) {
            $data = array('code' => '2013', 'msg' => '定单过期!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 定单已经完成
        if ($order_info['status'] == 2) {
            $data = array('code' => '2016', 'msg' => '定单已完成!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 根据会员卡号查询会员积分
        $post_data['card'] = $uinfo['cardno'];
        $post_data['key_admin'] = $this->ukey;
        $post_data['sign_key'] = $mer_chant['signkey'];
        $post_data['sign'] = sign($post_data);
        unset($post_data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/getuserinfobycard';
        $curl_uinfo = http($url, $post_data, 'post');

        writeOperationLog(array('park car get member by card' => $curl_uinfo), 'jaleel_logs');

        $uinfo_arr = json_decode($curl_uinfo, true);
        
        if ($uinfo_arr['code'] != 200) {
            $data = array('code' => '2000', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        
        // 判断用户的积分是否够支付
        if ($uinfo_arr['data']['score'] < $total_fee) {
            $data = array('code' => '319', 'msg' => '积分不足!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 请求扣除积分接口
        $post_arr['scoreno'] = abs($total_fee); // 单位积分
        $post_arr['cardno'] = $uinfo['cardno'];
        $post_arr['why'] = '停车支付';
        $post_arr['key_admin'] = $this->ukey;
        $post_arr['sign_key'] = $mer_chant['signkey'];
        $post_arr['sign'] = sign($post_arr);
        unset($post_arr['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/cutScore';
        $curl_re = http($url, $post_arr, 'post');
        $curl_arr = json_decode($curl_re, true);

        if ($curl_arr['code'] != 200) {
            $data = array('code' => '2014', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 更新定单状态为支付成功状态
        $up_re = $order->where(array('orderno' => $orderNo))->save(array('status' => 1));

        if ($up_re === false) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 通知车场支付成功
        $info['key_admin'] = $this->ukey;
        $info['sign_key'] = $mer_chant['signkey'];
        $info['carno'] = $carno;
        $info['paytype'] = 1;
        $info['sign'] = sign($carno);
        $info['orderNo'] = $orderNo;
        $info['amount'] = $total_fee;
        $info['discount'] = 0;
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/Parkservice/Parkoutput/pay';
        $curl_re = http($url, $data, 'post');
        writeOperationLog(array('积分支付通知车场结果' => $curl_re), 'jaleel_logs');
        $confirm_re = json_decode($curl_re, true);

        if ($confirm_re['code'] != 200) {
            $data = array('code' => '2015', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 更新定单状态为通知车场成功状态
        $up_re = $order->where(array('orderno' => $orderNo))->save(array('status' => 2));
        if ($up_re === false) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data = array('code' => '200', 'msg' => 'SUCCESS!');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 积分支付接口(对外)
     * 需要签名
     * 目前对接的轻停没有涉及到通知车场接口 因为那边不是积分 只是做的积分抵扣部分现金
     * @throws \Exception
     */
    public function paybyscorev2() {
        $carno = urldecode(I('carno'));
        $orderNo = I('orderno');
        $timestamp = I('timestamp');
        $sign_par = I('sign');

        if (!$carno or !$orderNo or !$this->user_openid or !$timestamp or !$sign_par) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);

        // 签名验证
        $sign_arr = array(
            'key_admin' => $this->ukey,
            'openid'    => $this->user_openid,
            'timestamp' => $timestamp,
            'orderno'   => $orderNo,
            'carno'     => $carno,
            'sign_key'  => $mer_chant['signkey'],
        );

        $sign = sign($sign_arr);

        // 签名错误
        if ($sign != $sign_par) {
            $data = array('code' => '1002', 'msg' => 'invalid sign!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

//        writeOperationLog(array('merchant infor' => json_encode($mer_chant)), 'jaleel_logs');
//        writeOperationLog(array('key_admin' => $this->ukey), 'jaleel_logs');

        // 查询会员信息
        $uinfo = $this->getUserCardByOpenId($mer_chant['pre_table'], $this->user_openid);
        if (!$uinfo) {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询定单信息
        $order = M('carpay_order', $mer_chant['pre_table']);
        $order_info = $order->where(array('orderno' => $orderNo))->find();

        if (!$order_info) {
            $data = array('code' => '2012', 'msg' => '无效定单!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $total_fee = $order_info['total_fee']; // 单位积分

        // 判断下单时间是否超过十分钟
        if (is_array($order_info) && time()-$order_info['createtime'] > 600) {
            $data = array('code' => '2013', 'msg' => '定单过期!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 根据会员卡号查询会员积分
        $post_data['card'] = $uinfo['cardno'];
        $post_data['key_admin'] = $this->ukey;
        $post_data['sign_key'] = $mer_chant['signkey'];
        $post_data['sign'] = sign($post_data);
        unset($post_data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/getuserinfobycard';
        $curl_uinfo = http($url, $post_data, 'post');

        writeOperationLog(array('park car get member by card' => $curl_uinfo), 'jaleel_logs');

        $uinfo_arr = json_decode($curl_uinfo, true);

        if ($uinfo_arr['code'] != 200) {
            $data = array('code' => '2000', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 判断用户的积分是否够支付
        if ($uinfo_arr['data']['score'] < $total_fee) {
            $data = array('code' => '319', 'msg' => '积分不足!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 请求扣除积分接口
        $post_arr['scoreno'] = abs($total_fee); // 单位积分
        $post_arr['cardno'] = $uinfo['cardno'];
        $post_arr['why'] = '停车支付';
        $post_arr['key_admin'] = $this->ukey;
        $post_arr['sign_key'] = $mer_chant['signkey'];
        $post_arr['sign'] = sign($post_arr);
        unset($post_arr['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/cutScore';
        $curl_re = http($url, $post_arr, 'post');
        $curl_arr = json_decode($curl_re, true);

        if ($curl_arr['code'] != 200) {
            $data = array('code' => '2014', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 更新定单状态为支付成功状态
        $up_re = $order->where(array('orderno' => $orderNo))->save(array('status' => 1));
        if ($up_re === false) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 更新定单状态为通知车场成功状态
        $up_re = $order->where(array('orderno' => $orderNo))->save(array('status' => 2));
        if ($up_re === false) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data = array('code' => '200', 'msg' => 'SUCCESS!');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 按用户openid查询用户信息
     * @param $prefix
     * @param $openid
     * @return mixed
     */
    protected function getUserCardByOpenId($prefix, $openid) {
        $user = M('mem', $prefix);
        $re = $user->where(array('openid' => $openid))->find();
        return $re;
    }

    /**
     * 微信支付下单接口
     * @throws \Exception
     */
    public function paybyweixin() {
        $carno = urldecode(I('carno'));
        $pay_class = @I('pay_class');
        if (!$carno or !$this->user_openid) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 验证key_admin
        $mer_chant = $this->getMerchant($this->ukey);

        writeOperationLog(array('get merchant' => json_encode($mer_chant)), 'jaleel_logs');

        $car = $this->getCarInfo($carno, $mer_chant['signkey']);
        $begintime = strtotime($car['data']['BeginTime']); // 停车起始时间
        $endtime = strtotime($car['data']['EndTime']); // 停车结束时间
        $client_orderno = $car['data']['orderNo']; // 停车客户端定单编号
        $total_fee = $car['data']['MoneyValue'] * 100; // 单位为分
        $discountValue = $car['data']['discountValue'] * 100; // 折扣金额 单位为分
        $discount = $car['data']['discount']; // 折扣

//        $total_fee = 0.01; // 单位元

        /**
         * 支付金额为零则直接提示下单失败
         */
        if ($total_fee == 0) {
            $data = array('code' => '5000', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询会员信息
        $uinfo = $this->getUserCardByOpenId($mer_chant['pre_table'], $this->user_openid);
        if (!$uinfo) {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 请求微信支付接口进行支付
        $post_arr['total_fee'] = $total_fee; // 单位分
        $post_arr['openId'] = $this->user_openid;
        $post_arr['attach'] = urlencode(json_encode(array('carNo' => $carno, 'key_admin' => $this->ukey, 'payType' => 0, 'amount' => $total_fee, 'discount' => $discountValue, 'client_orderno' => $client_orderno)));
        $post_arr['notify_url'] = C('DOMAIN') . "/ParkApp/ParkPay/confirmPay";
        $post_arr['body'] = '停车支付';

        // 查询当前商户的支付是否是子商户支付
        if($pay_class=="applet") {
            $url = "http://pay.rtmap.com/pay/api/wxpay/{$mer_chant['applet_appid']}/jsapi/unifiedorder";
        }else{
            if ($mer_chant['wechat_pay_type'] == 2) {

                // 查询子商户账号
                $default = M('default', $mer_chant['pre_table']);
                $def_re = $default->where(array('customer_name' => 'subpayacc'))->find();
                $sub_mich = $def_re['function_name'];
                $post_arr['open_id'] = $this->user_openid;
                $post_arr['appid'] = $mer_chant['wechat_appid'];
                $url = "http://123.56.103.28/pay/api/pay/wx/{$sub_mich}/JSAPI/prePay";
            } else {
                $url = "http://pay.rtmap.com/pay/api/wxpay/{$mer_chant['wechat_appid']}/jsapi/unifiedorder";
            }
        }

        $curl_re = http($url, $post_arr);
        writeOperationLog(array('请求微信支付接口参数' => json_encode($post_arr)), 'jaleel_logs');
        writeOperationLog(array('请求微信支付请求url' => $url), 'jaleel_logs');
        writeOperationLog(array('请求微信支付回调url' => $post_arr['notify_url']), 'jaleel_logs');
        writeOperationLog(array('请求微信支付接口' => $curl_re), 'jaleel_logs');
        $curl_arr = json_decode($curl_re, true);

        if ($curl_arr['errcode'] != 200) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 插入定单
        $order = M('carpay_order', $mer_chant['pre_table']);
        $in_order = $this->createOrder($order, $carno, $total_fee, 0, $curl_arr['obj']['outTradeNo'], $begintime, $endtime, $client_orderno);

        if (!$in_order) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data = array('code' => '200', 'msg' => 'SUCCESS!', 'data' => $curl_arr['obj']);
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 生成订单
     * @param $order
     * @param $carno
     * @param $total_fee
     * @param $paytype
     * @param $orderNo
     * @param $begintime
     * @param $endtime
     * @return mixed
     */
    protected function createOrder($order, $carno, $total_fee, $paytype, $orderNo, $begintime, $endtime, $client_orderno) {

        $order_arr['orderno'] = $orderNo;
        $order_arr['openid'] = $this->user_openid;
        $order_arr['carno'] = $carno;
        $order_arr['total_fee'] = $total_fee;
        $order_arr['paytype'] = $paytype;
        $order_arr['client_orderno'] = $client_orderno;
        $order_arr['begintime'] = $begintime;
        $order_arr['endtime'] = $endtime;
        $order_arr['createtime'] = time();
        $in_order = $order->add($order_arr);
        return $in_order;
    }

    /**
     * 停车缴费支付回调通知接口(用于微信支付成功后的回调)
     * @return array
     * @throws \Exception
     */
    public function confirmPay() {

        /**
         * 此处接收的是json字符串
         * 注意不能使用TP中的I函数
         * 因为会被转义
         * 转义后无法使用json_decode函数转换成数组
         */
        $content = file_get_contents("php://input");
        writeOperationLog(array('停车缴费回调参数' => $content), 'jaleel_logs');
        $par_arr = json_decode($content, true);
        $attach = json_decode(urldecode($par_arr['attach']), true);
        writeOperationLog(array('停车缴费回调参数attach' => json_encode($attach)), 'jaleel_logs');
        $orderNo = $par_arr['out_trade_no'];
        $carNo = $attach['carNo'];
        $key_admin = $attach['key_admin'];
        $payType = $attach['payType'];
        $amount = $attach['amount'];
        $discount = $attach['discount'];
        $client_orderno = $attach['client_orderno'];

        $mer_chant = $this->getMerchant($key_admin);

        $order = M('carpay_order', $mer_chant['pre_table']);
        $order_info = $order->where(array('orderno' => $orderNo))->find();

        if (!$order_info) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 更新定单状态为支付成功状态
        $up_re = $order->where(array('orderno' => $orderNo))->save(array('status' => 1));
        if ($up_re === false) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data['key_admin'] = $key_admin;
        $data['sign_key'] = $mer_chant['signkey'];
        $data['carno'] = $carNo;
        $data['paytype'] = $payType;
        $data['sign'] = sign($data);
        $data['orderNo'] = $client_orderno;
        $data['amount'] = $amount;
        $data['discount'] = $discount;
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/Parkservice/Parkoutput/pay';
        $curl_re = http($url, $data, 'post');
        writeOperationLog(array('通知车场支付成功结果' => $curl_re), 'jaleel_logs');
        $data = json_decode($curl_re, true);

        // 更新定单状态为通知车场成功状态
        if ($data['code'] == 200) {
            $order = M('carpay_order', $mer_chant['pre_table']);
            $up_re = $order->where(array('orderno' => $orderNo))->save(array('status' => 2));
            if (!$up_re) {
                $data = array('code' => '1011', 'msg' => 'system error!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 车辆入场记录接口
     */
    public function carenter() {
        $carno = I('carno');

        if (!$carno) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $mer_chant = $this->getMerchant($this->ukey);
        $action = M('car_action', $mer_chant['pre_table']);
        $data['carno'] = $carno;
        $data['action'] = 1;
        $data['createtime'] = time();
        $re = $action->add($data);

        if (!$re) {
            $data = array('code' => '1011', 'msg' => 'system error');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data = array('code' => '200', 'msg' => 'SUCCESS!');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 车辆出场记录接口
     */
    public function carexit() {
        $carno = I('carno');

        if (!$carno) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $mer_chant = $this->getMerchant($this->ukey);
        $action = M('car_action', $mer_chant['pre_table']);
        $data['carno'] = $carno;
        $data['action'] = 0;
        $data['createtime'] = time();
        $re = $action->add($data);

        if (!$re) {
            $data = array('code' => '1011', 'msg' => 'system error');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data = array('code' => '200', 'msg' => 'SUCCESS!');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 获取会员免费停车时长(对外)
     * 需要签名验证
     */
    public function getFreeParkTime() {
        if (!$this->ukey or !$this->user_openid) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);

        $static = M('total_static');

        // 查询会员卡样 根据卡样的的等级和用户是哪个等级来确定会员免费停车时长
        $static->where(array('admin_id' => $mer_chant['id'], 'tid' => '5'))->find();
    }

    /**
     * 免费停车时长核销接口(对外)
     * 需要签名验证
     */
    public function checkFreePark() {}
    
    
    /**
     * 根据车牌号获取车位号（对内）
     */
    public function get_ParkingNo(){
        $params['carno']=I('plateno');
        $params['key_admin']=I('key_admin');
        
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $admininto=$this->getMerchant($params['key_admin']);
            $params['sign_key']=$admininto['signkey'];
            $params['sign']=sign($params);
            unset($params['sign_key']);
            $url = C('DOMAIN') . '/Parkservice/Parkoutput/searchcar';
            $params['page']=I('page');
            $params['lines']=I('lines');
            $curl_re = http($url, $params, 'post');
            writeOperationLog(array('RiYue_FindCarInfo' => $curl_re), 'zhanghang');
            $db=M('buildid','total_');
            $buildid=$db->where(array('adminid'=>$admininto['id']))->find();
            $arr=json_decode($curl_re,true);
            if($arr['code']==200){
                $msg['code']=200;
                $msg['data']['buildid']=$buildid['buildid'];
                $msg['data']['data']=$arr['data'];
            }else{
                $msg['code']=$arr['code'];
            }
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 获取车场剩余车位数（对内）
     */
    public function get_ParkingNo_num(){
        $params['key_admin']=I('key_admin');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $admininto=$this->getMerchant($params['key_admin']);
            $params['sign_key']=$admininto['signkey'];
            $params['sign']=sign($params);
            unset($params['sign_key']);
            $url = C('DOMAIN') . '/Parkservice/Parkoutput/get_left_park';
            $curl_re = http($url, $params, 'post');
            writeOperationLog(array('RiYue_FindCarInfo_num' => $curl_re), 'zhanghang');
            $arr=json_decode($curl_re,true);
            if($arr['code']==200){
                $msg['code']=200;
                $msg['data']=$arr['data'];
            }else{
                $msg['code']=$arr['code'];
            }
            returnjson($msg, $this->returnstyle, $this->callback);
        }
    }

    public function getParkIntro() {
        $mer_chant = $this->getMerchant($this->ukey);

        $result = $this->GetOneAmindefault($mer_chant['pre_table'], $this->ukey, 'carpayintro');

        if ($result) {
            $result['content'] = $result['content'];
            $data = array('code' => '200', 'msg' => 'success!', 'data' => $result);
        } else {
            $data = array('code' => '1011', 'msg' => 'failed!');
        }

        returnjson($data, $this->returnstyle, $this->callback);
    }
}