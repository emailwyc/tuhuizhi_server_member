<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/7/4
 * Time: 16:13
 */

namespace Thirdwechat\Controller\Wechat;


use Common\Controller\CommonController;
use Think\Controller;
use Thirdwechat\Controller\Wechat\QrCode\QrCode;

class QrCodeController extends CommonController
{
    public function _initialize()
    {
        parent::__initialize();
    }

    public function getqrcode()
    {
//        echo time();
        $params['key_admin'] = I('get.key_admin');
        $params['timestamp'] = I('get.timestamp');
        $sign = $params['sign'] = I('get.sign');
        $json = $params['json'] = file_get_contents('php://input');
        //判断参数是完整
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }

        //判断传入的是否是json
        if ( !is_json($params['json']) ) {
            returnjson(array('code'=>1051),$this->returnstyle,$this->callback);
        }
        $ct=time()-$params['timestamp'];
        //如果计算得出来的秒数大于正负100秒，则判定请求方的服务器时间不对
        if ($ct > 100 || $ct < -100){
            returnjson(array('code'=>1056), $this->returnstyle, $this->callback);
        }

        $admininfo = $this->getMerchant($params['key_admin']);
        unset($params['json']);
        unset($params['sign']);

        $params['sign_key']=$admininfo['signkey'];
//        echo sign($params);
        //验证签名
        $checkoutsign = $this->checkParams($params, $params['key_admin'], $sign);
        if ($checkoutsign !== true) {
            returnjson(array('code'=>$checkoutsign),$this->returnstyle,$this->callback);
        }

        $q = new QrCode();
        $code = $q->getQrcode($admininfo['wechat_appid'], $json);
        if (is_json($code)) {
            $arr = json_decode($code, true);
            if (isset($arr['ticket']) && isset($arr['url'])) {//&& isset($arr['expire_seconds'])
                returnjson(array('code' => 200, 'data' => $arr), $this->returnstyle, $this->callback);
            }else {
                returnjson(array('code' => 4003, 'data' => $code), $this->returnstyle, $this->callback);
            }
        }else{
            returnjson(array('code'=>4000, 'data'=>$code),$this->returnstyle,$this->callback);
        }

    }
}