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


// 余额提现申请
$params = [
    "user" => 'u-s.e_r1479281694040',                       // 用户 ID
    "amount" => 200,                                        // 转账金额
    "channel" => 'unionpay',                                // 提现使用渠道。银联：unionpay，支付宝：alipay，微信：wx
    "user_fee" => 10,                                       // 用户需要承担的手续费
    "description" => "test232description",
    "order_no" => time() . mt_rand(11111, 99999),           // 提现订单号，为长度不大于 16 的数字
    "extra" => [
        "card_number" => "6225210207073918",
        "user_name" => "翁旭锋",
        "open_bank_code" => "0102",
        "prov" => "上海",
        "city" => "上海"
    ]
];
try {
    $withdrawal = \Pingpp\Withdrawal::create($params);
    echo $withdrawal;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;


try {
     // 余额提现列表查询
    $withdrawals = \Pingpp\Withdrawal::all(['per_page' => 3]);
    echo $withdrawals . PHP_EOL;

    // 余额提现查询
    $withdrawalId = '1711611161932569404';
    $withdrawal = \Pingpp\Withdrawal::retrieve($withdrawalId);
    echo $withdrawal . PHP_EOL;

    // 余额提现取消
    $withdrawalId = '1711611161932569404';
    $withdrawal = \Pingpp\Withdrawal::cancel($withdrawalId);
    echo $withdrawal . PHP_EOL;

} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;

// 批量提现确认
$params = [
    'withdrawals' => [
        '1701611150302360654',
        '1701611151015078981'
    ]
];
try {
    $batch_withdrawal = \Pingpp\BatchWithdrawal::confirm($params);
    echo $batch_withdrawal;                                 // 输出 Ping++ 返回的 withdrawal 对象
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;

// 批量提现查询
$batch_withdrawal_id = '1901611151015122025';
try {
    $batch_withdrawal = \Pingpp\BatchWithdrawal::retrieve($batch_withdrawal_id);
    echo $batch_withdrawal;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;

// 批量提现列表查询
$params = ['per_page' => 3];
try {
    $batch_withdrawal = \Pingpp\BatchWithdrawal::all($params);
    echo $batch_withdrawal;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != null) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}
exit;