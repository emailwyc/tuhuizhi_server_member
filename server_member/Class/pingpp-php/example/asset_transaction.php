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

try {
    // 企业清算账户交易列表
    $asts = \Pingpp\AssetTransaction::all(['per_page' => 3]);
    echo $asts . PHP_EOL;

    // 用户账户交易明细
    $ast = \Pingpp\AssetTransaction::retrieve('310016111620182300005101');
    echo $ast . PHP_EOL;
} catch (\Pingpp\Error\Base $e) {
    // 捕获报错信息
    if ($e->getHttpStatus() != NULL) {
        echo $e->getHttpStatus() . PHP_EOL;
        echo $e->getHttpBody() . PHP_EOL;
    } else {
        echo $e->getMessage() . PHP_EOL;
    }
}
