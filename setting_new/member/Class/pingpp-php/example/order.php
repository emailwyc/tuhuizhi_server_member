<?php
/**
 * Ping++ Server SDK
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可根据自己网站需求按照技术文档编写, 并非一定要使用该代码。
 * 该代码仅供学习和研究 Ping++ SDK 使用，仅供参考。
 */

require dirname(__FILE__) . '/../init.php';
// 示例配置文件，测试请根据文件注释修改其配置
require 'config.php';
\Pingpp\Pingpp::setApiKey(APP_KEY);                                         // 设置 API Key
\Pingpp\Pingpp::setAppId(APP_ID);                                           // 设置 APP ID
\Pingpp\Pingpp::setPrivateKeyPath(__DIR__ . '/your_rsa_private_key.pem');   // 设置私钥

// 创建商品订单
$order_no = substr(md5(time()), 0, 10);
try {
    $or = \Pingpp\Order::create(
        array(
            "amount" => 100,
            "app" => APP_ID,
            "merchant_order_no" => "201609{$order_no}",
            "subject" => "subj{$order_no}",
            "currency" => "cny",
            "body" => "body{$order_no}",
            "uid" => "test_user_0001",
            "client_ip" => "192.168.0.101",
            'receipt_app' => APP_ID,    // 收款方应用
            'service_app' => APP_ID,    // 服务方应用
            'royalty_users' => [    //分润的用户列表
                [
                    'user' => 'user_test_0001',
                    'amount' => 10,
                ],
                [
                    'user' => 'user_test_0002',
                    'amount' => 10,
                ],
            ],
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


// 商品订单支付
$order_id = '2011611170000003651';
$params = [
    'balance_amount'    => 100,
    'charge_amount'     => 0,
];
try {
    $pay = \Pingpp\Order::pay($order_id, $params);
    echo $pay;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;

// 商品订单取消
$order_id = '2011611170000003651';
try {
    $pay = \Pingpp\Order::cancel($order_id);
    echo $pay;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;

// 商品订单查询
$order_id = '2011611170000003651';
try {
    $pay = \Pingpp\Order::retrieve($order_id);
    echo $pay;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;

// 创建充值订单
$order_no = substr(hash_hmac('sha256', time(), mt_rand(1111, 9999)), 0, 10);
try {
    $or = \Pingpp\Order::createRecharge([
        "amount" => 100,
        "app" => APP_ID,
        "merchant_order_no" => "201609{$order_no}",
        "subject" => "subj{$order_no}",
        "currency" => "cny",
        "body" => "body{$order_no}",
        "uid" => "test_user_001",
        "client_ip" => "192.168.0.101",
        "channel" => "alipay"
    ]);
    echo $or;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;

// 商品订单列表查询
$params = ['app' => APP_ID];
try {
    $ors = \Pingpp\Order::all($params);
    echo $ors;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;

// 商品退款
try {
    $order_id = '2001704050000001361';
    $orre = \Pingpp\OrderRefund::create($order_id,
        [
            'balance_amount' => 0,  //余额部分退款金额，默认为余额部分全额退款。单位：分。
            'charge_amount' => 10,  //支付渠道部分退款金额，默认为支付渠道部分全额退款。单位：分
            'description' => 'Your description',    //退款附加说明。
            'metadata' => [],
            'refund_mode' => 'to_source',   //退款模式。原路退回：to_source，退至余额：to_balance。默认为原路返回。
            'royalty_users' => [    //退分润的用户列表
                [
                    'user' => 'test_user_001',
                    'amount_refunded' => 10,
                ],
                [
                    'user' => 'test_user_002',
                    'amount_refunded' => 10,
                ],
            ],
        ]
    );
    echo $orre;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;


// 商品退款查询
try {
    $order_id = '2011611160000343961';
    $order_refund_id = '2111611160000012925';
    $orre = \Pingpp\OrderRefund::retrieve($order_id, $order_refund_id);
    echo $orre;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;

// 商品退款列表查询
try {
    $order_id = '2011611160000343961';
    $orres = \Pingpp\OrderRefund::all($order_id);
    echo $orres;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;
