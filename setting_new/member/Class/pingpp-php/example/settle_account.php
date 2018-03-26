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

// 创建结算账户对象-alipay
$settle_account = \Pingpp\SettleAccount::create('user_004', [
    'channel' => 'alipay',
    'recipient' => [
        'type' => 'b2c', //转账类型。b2c：企业向个人付款，b2b：企业向企业付款。
        'account' => 'account01@alipay.com',
        'name' => '李狗',
    ],
]);
echo $settle_account;
exit;

//创建结算账户对象-unionpay
$settle_account = \Pingpp\SettleAccount::create('user_004', [
    'channel' => 'unionpay',
    'recipient' => [
        'account' => '6214666666666666',
        'name' => '张三',
        'type' => 'b2b', //转账类型。b2c：企业向个人付款，b2b：企业向企业付款。
        'open_bank' => '招商银行',
        'open_bank_code' => '0308',
    ],
]);
exit;

// 创建结算账户对象-wx_pub
$settle_account = \Pingpp\SettleAccount::create('user_004', [
    'channel' => 'wx_pub',
    'recipient' => [
        'account' => 'open_id',
        'name' => '李四',
        'type' => 'b2c', //转账类型。b2c：企业向个人付款，b2b：企业向企业付款。
        'force_check' => false,
    ],
]);

// 查询结算账户对象
$settle_account = \Pingpp\SettleAccount::retrieve('user_004', '320217031816231000001001');
echo $settle_account;
exit;

//删除结算账户对象
$delete_sa = \Pingpp\SettleAccount::delete('user_004', '320217031816231000001001');
echo $delete_sa;
exit;

// 查询结算账户对象列表
$settle_account = \Pingpp\SettleAccount::all('user_004');
echo $settle_account;