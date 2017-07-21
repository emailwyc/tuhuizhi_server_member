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

/**
 * 分润结算对象 SDK 示例
 */

//创建分润结算对象
$royalty_settlement = \Pingpp\RoyaltySettlement::create([
    'payer_app' => APP_ID,
    'method' => 'alipay',   //分润的方式，余额 balance 或渠道名称，例如 alipay
    'recipient_app' => APP_ID,
    'created' => [
        'gt' => 1489826451,
        'lt' => 1492418451,
    ],
    'source_user' => 'user_002',
    //'source_no' => '',
    'min_amount' => 1,
    'metadata' => [
        'key' => 'value'
    ],
]);
echo $royalty_settlement;
exit;

// 查询分润结算对象
$royalty_settlement = \Pingpp\RoyaltySettlement::retrieve('431170318144700001');
echo $royalty_settlement;
exit;

// 查询分润结算对象列表
$royalty_settlement_list = \Pingpp\RoyaltySettlement::all([
    'payer_app' => APP_ID
]);
echo $royalty_settlement_list;
exit;

//更新分润结算对象
$royalty_settlement_list = \Pingpp\RoyaltySettlement::update('431170318144700001', [
    'status' => 'pending'  // pending, canceled
]);
echo $royalty_settlement_list;
exit;