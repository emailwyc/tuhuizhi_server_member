<?php
/**
 * 0-999，随便前端随便弹
 *
 * wechat模块状态码为4000——4999
 * #1-50 状态码为临时状态码，不要定义，
 * 支付模块状态码为5000——5999！！！！！！！！！！！
 * 营销平台券、奖品状态码：1500——1999
 */

require_once(__DIR__ . '/errorcode/code100.php');
require_once(__DIR__ . '/errorcode/code1000.php');
require_once(__DIR__ . '/errorcode/code2000.php');
require_once(__DIR__ . '/errorcode/code3000.php');
require_once(__DIR__ . '/errorcode/code4000.php');
require_once(__DIR__ . '/errorcode/code5000.php');
require_once(__DIR__ . '/errorcode/code6000.php');
require_once(__DIR__ . '/errorcode/code7000.php');
require_once(__DIR__ . '/errorcode/code8000.php');
require_once(__DIR__ . '/errorcode/code9000.php');

$errcode = $code['code100'] + $code['code1000'] + $code['code2000'] + $code['code3000'] + $code['code4000'] + $code['code5000'] + $code['code6000'] + $code['code7000'] + $code['code8000'] + $code['code9000'];

return array(
    'ERROR_CODES'=>$errcode,
);

