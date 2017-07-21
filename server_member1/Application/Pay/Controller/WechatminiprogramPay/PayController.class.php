<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/5/25
 * Time: 15:20
 */

namespace Pay\Controller\WechatminiprogramPay;


use Common\Controller\CommonController;
use Pingpp\Error\Base;
use Pingpp\Order;
use Pingpp\Pingpp;

class PayController extends CommonController
{
    public function pingxx()
    {
        include_once './Class/pingpp-php/init.php';
        Pingpp::setApiKey('sk_test_CKiTm9u90uLOa500KCibL884');                                         // 设置 API Key
        Pingpp::setAppId('app_8KurvDLqvPqLnzT0');                                           // 设置 APP ID
        Pingpp::setPrivateKeyPath('./rsa_key/pingxx_huiyuecheng_rsa_private_key.pem');   // 设置私钥


// 创建商品订单
        $order_no = substr(md5(time()), 0, 10);
        try {
            $or = Order::create(
                array(
                    "amount" => 100,
                    "app" => 'app_8KurvDLqvPqLnzT0',
                    "merchant_order_no" => "201609{$order_no}",
                    "subject" => "subj{$order_no}",
                    "currency" => "cny",
                    "body" => "body{$order_no}",
                    "uid" => "test_user_0001",
                    "client_ip" => "192.168.0.101",
                    'receipt_app' => 'app_8KurvDLqvPqLnzT0',    // 收款方应用
                    'service_app' => 'app_8KurvDLqvPqLnzT0',    // 服务方应用
//                    'time_expire' =>
                )
            );
            echo $or;
        } catch (\Pingpp\Error\Base $e) {
            // 捕获报错信息
            if ($e->getHttpStatus() != NULL) {
                echo $e->getHttpStatus() . PHP_EOL;
                echo $e->getHttpBody() . PHP_EOL;
            } else {
                echo $e->getMessage() . PHP_EOL;
            }
        }
        exit;

    }




    public function pingxxpay()
    {
        include_once './Class/pingpp-php/init.php';
        Pingpp::setApiKey('sk_test_CKiTm9u90uLOa500KCibL884');                                         // 设置 API Key
        Pingpp::setAppId('app_8KurvDLqvPqLnzT0');                                           // 设置 APP ID
        Pingpp::setPrivateKeyPath('./rsa_key/pingxx_huiyuecheng_rsa_private_key.pem');   // 设置私钥
        $order_id=I('orderid');
        // 商品订单支付
//        $order_id = '2011611170000003651';
        $params = [
            'channel'=>'wx_lite',
            'balance_amount'    => 0,
            'charge_amount'     => 100,
        ];
        try {
            $pay = Order::pay($order_id, $params);
            echo $pay;
        } catch (Base $e) {
            if ($e->getHttpStatus() != null) {
                header('Status: ' . $e->getHttpStatus());
                echo $e->getHttpBody();
            } else {
                echo $e->getMessage();
            }
        }
    }


}