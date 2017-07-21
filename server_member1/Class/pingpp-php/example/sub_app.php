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

// 创建子商户应用 sub_app
$sub_app = \Pingpp\SubApp::create([
    'display_name' => 'sub_app_display_name',
    'user' => 'user_102',
    'metadata' => [
        'key' => 'value'
    ],
]);
echo $sub_app; // 返回子商户应用
exit;

//查询子商户应用 sub_app
$sub_app = \Pingpp\SubApp::retrieve('app_1Gqj58ynP0mHeX1q');
echo $sub_app;
exit;

//更新子商户应用
$sub_app = \Pingpp\SubApp::update('app_1Gqj58ynP0mHeX1q', [
    'display_name' => 'display_name_2',
    'metadata' => [
        'key' => 'value2'
    ],
    'description' => 'Your description',
]);
echo $sub_app;
exit;

//删除子商户应用
$sub_app = \Pingpp\SubApp::delete('app_1Gqj58ynP0mHeX1q');
echo $sub_app;
exit;

// 查询子商户应用列表
$sub_app_list = \Pingpp\SubApp::all();
echo $sub_app_list;
exit;