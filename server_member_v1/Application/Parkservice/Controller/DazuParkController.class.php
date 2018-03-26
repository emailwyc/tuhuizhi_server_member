<?php
/**
 * Created by PhpStorm.
 * User: soone
 * Date: 17-12-06
 * Time: 下午2:56
 */
namespace Parkservice\Controller;

use Think\Controller;

class DazuParkController extends Controller implements ParkinterfaceController
{
    // 西安金地停车系统请求相关配置
    protected $url = 'http://117.36.74.210:9090/Parking/Handheld/';
    protected $rheader  = array(
        'Content-Type:application/json; charset=utf-8'
    );
    protected $secretkey = "mVmb3JtZXJyZWZvcm1lcg==";

    /**
     *  获取剩余车位数
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getleftpark($sign_key,$key_admin = '') {
        $params['sign_key']=$sing_key;
        $this_url = $this->url . 'GetSerachCarInfo';
        //$result = http($this_url, $par);
        $array = array('code'=>200,'data'=>array(),'msg'=>'SUCCESS');
        if (200==$array['code']){
            return $array['data'];
        }else{
            return (string)$array['code'];
        }
    }


    /**
     *  搜索车辆列表 此处返回正确车俩的停车信息
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function searchcar($carno,$sign_key,$key_admin = '',$page,$lines) {
        $params['carno']=$carno;
        $params['sign_key']=$sing_key;
        $params['sign']=sign($params);
        unset($params['sign_key']);
        $par = json_encode($params, JSON_UNESCAPED_UNICODE);
        $this_url = $this->url . 'GetSerachCarInfo';
        $result = http_auth($this_url, $par, 'POST',"",$this->rheader,true);
        if (!is_json($result)){
            return false;
        }
        $array=json_decode($result,true);
        if (200==$array['code']){
            return $array['data'];
        }else{
            return (string)$array['msg'];
        }
    }

    /**
     * 从列表中选择我的车辆信息
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function choosemycar($carno,$sign_key,$key_admin = '') {
        $params['carno']=$carno;
        $params['sign_key']=$sing_key;
        $params['sign']=sign($params);
        unset($params['sign_key']);
        $par = json_encode($params,JSON_UNESCAPED_UNICODE);
        $this_url = $this->url . 'GetCarPayInfo';
        $result = http_auth($this_url, $par, 'POST',"",$this->rheader,true);
        if (!is_json($result)){
            return false;
        }
        $array=json_decode($result,true);
        if (200==$array['code']){
            if($array['data']['Status']==1){
                return "1130";
            }
            return $array;
        }else{
            return (string)$array['code'];
        }
    }

    /**
     * 支付状态确认
     *  正确数据以数组方式返回，否则，返回的是一个错误码(amount按照分走)
     */
    public function paystatus($carno, $sign_key, $paytype, $key_admin = '', $orderNo = '', $amount = '', $discount = '') {
        $paytype = $paytype == 1?1:2;
        //金额跟积分换算;
        $pv = @(int)$this->scorepv;
        $discount = empty($discount)?0:$discount;
        if($paytype==1){//积分支付
            $value = (($amount+$discount)/$pv);
            $value = number_format($value, 2, '.', '');
            $value = $value*100;
            $score_value = (int)$amount;
        }else{//钱支付
            $score_value = 0;
            $value = ($amount+$discount)*100;
        }
        $params['sign_key']=$sign_key;
        $params['type']=$paytype;
        $params['carno']=$carno;
        $params['value']=$value; // 实际支付的金额
        $params['score_value']=$score_value;
        $params['sign']=sign($params);
        unset($params['sign_key']);
        $par = json_encode($params,JSON_UNESCAPED_UNICODE);
        $this_url = $this->url . 'GetPaidMess';
        $result = http_auth($this_url, $par, 'POST',"",$this->rheader,true);
        writeOperationLog(array('result' => $result), 'xianjindi_park');
        if (!is_json($result)){
            return false;
        }
        $array=json_decode($result,true);
        if (0==$array['code']){
            return $array['data'];
        }else{
            return (string)$array['code'];
        }
    }

    public function getparkstatus($build, $floor, $sign_key, $key_admin,$admininfo){
        return false;
    }


}