<?php
/* *
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



// 余额转账
$params = [
    'user'           => 'wanghong',                 // 发送方用户 ID，必填
    'recipient'      => 'u-s.e_r1479281694040',     // 接收方用户 ID，必填
    'amount'         => 10,                         // 转账金额，必填
    'description'    => '余额转账',                  // 描述，非必填
    'user_fee'       => 0                           // 发送方承担手续费，默认值：0，非必填
];
try {
    $tr = \Pingpp\Balance::transfer($params);
    echo $tr;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;

// 用户收款接口
$params = [
    'receipts'      => [                            // 收款的列表，包含用户和收款的金额，必填
        [
            'user'       => 'user_001',             // 收款的用户 ID
            'amount'     => 10                      // 收款的金额
        ],
        [
            'user'       => 'user_002',
            'amount'     => 10
        ]
    ],
    'type'           => 'receipts_earning',         // 明细类型，值为 receipts_earning 或  receipts_extra，非必填
    'description'    => '测试用户收款接口',           // 描述，非必填
];
try {
    $tr = \Pingpp\Balance::createReceipts($params);
    echo $tr;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;
