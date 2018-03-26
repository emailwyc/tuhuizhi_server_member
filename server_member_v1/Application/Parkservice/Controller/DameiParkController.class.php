<?php

namespace Parkservice\Controller;

use Think\Controller;
use Common\Controller\RedisController;

class DameiParkController extends Controller implements ParkinterfaceController
{

    protected $url = 'http://prep.tingjiandan.com/openapi/gateway';
    protected $acc = "e3f556ceefef4d61ba3eacd1a6844430";
    protected $pwd = "decddbd9ff4b48a2a813eca9fcc56168";
    protected $rheader  = array(
        'Content-Type:application/json; charset=utf-8'
    );

    protected function getPublicParams() {
        $par = array();
        $par['version'] = "1.0";
        $par['partner'] = $this->acc;
        $par['timestamp'] = date("Y-m-d H:i:s",time());
        $par['charset'] = "utf-8";
        $par['signType'] = "md5";
        return $par;
    }
    protected function DameiSign($par) {
        unset($par['signType']);
        ksort($par);
        $str = "";
        foreach ($par as $k => $v) {
            if ('' == $str) {
                $str .= $k . '=' . trim($v);
            } else {
                $str .= '&' . $k . '=' . trim($v);
            }
        }
        $str.=$this->pwd;
        return md5($str);
    }
    /**
     *  获取剩余车位数
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getleftpark($sign_key,$key_admin = '') {
        $array = array('code'=>200,'data'=>array(array("location"=>"total","leftnum"=>"暂无")),'msg'=>'SUCCESS');
        if (200==$array['code']){
            return $array['data'];
        }else{
            return (string)$array['code'];
        }
    }


    /**
     *  搜索车辆列表 此处返回正确车俩的停车信息enterTime,ticketNo,parkDuration,carPlate,imageURL
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function searchcar($carno,$sign_key,$key_admin = '',$page,$lines) {
        $params = $this->getPublicParams();
        $params['service']="parkhub.order.infoForFreeMins";
        $params['freeMins']="0";
        $params['carNum'] = $carno;
        $params['sign'] = $this->DameiSign($params);
        $result = http($this->url, json_encode($params),'POST',$this->rheader,true);
        if (!is_json($result)){
            return false;
        }
        $array=json_decode($result,true);
        //print_r($array);exit;
        $carList = array('code'=>200,'data'=>array(),'msg'=>"success!");
        if($array){
                $floor = "";
                $parkingno = "";
                if($array['returnCode'] == "T" && $array['inDt']){
                    $record = array();
                    $record['CarSerialNo'] = $carno;
                    $record['BeginTime'] = date('Y-m-d H:i:s',strtotime($array['inDt']));
                    $record['EndTime'] = date('Y-m-d H:i:s',strtotime($array['outDt']));
                    $record['Status'] = 2;
                    $record['CarImg'] = "1";
                    $record['floor'] = $floor;
                    $record['ParkingNo'] = $parkingno;
                    $record['fl'] = $floor;
                    $carList['data'][] = $record;
                }
        }
        if (200==$carList['code']){
            return $carList['data'];
        }else{
            return (string)$carList['msg'];
        }
    }

    /**
     * 从列表中选择我的车辆信息
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function choosemycar($carno,$sign_key,$key_admin = '') {
        $params = $this->getPublicParams();
        $params['service']="parkhub.order.infoForFreeMins";
        $params['freeMins']="0";
        $params['carNum'] = $carno;
        $params['sign'] = $this->DameiSign($params);
        $result = http($this->url, json_encode($params),'POST',$this->rheader,true);
        if (!is_json($result)){
            return false;
        }
        $array=json_decode($result,true);
        $record = array();
        $record['MoneyValue'] = (int)($array['unPayAmount']*100);
        $record['CarSerialNo'] = $carno;
        $record['BeginTime'] = date('Y-m-d H:i:s',strtotime($array['inDt']));
        $record['EndTime'] = date('Y-m-d H:i:s',strtotime($array['outDt']));
        $record['Status'] = 2;

        $record['tradeId'] = $array['tradeId'];
        $record['unPayAmount'] = $array['unPayAmount'];
        $record['accountId'] = $array['accountId'];

        $record['CarImg'] = "1";
        $record['floor'] = "";
        $record['ParkingNo'] = "";
        $result = array('code'=>200,'data'=>$record,'msg'=>'success');
        if (!empty($array['unPayAmount'])){
            return $result;
        }else{
            return "1103";
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
        if($paytype==1){//积分支付
            $value = 0;
        }else{//钱支付
            $value = $amount;
        }
        if(strlen($orderNo)>32){
            $orderNo1 = substr($orderNo,0,32);
        }else{
            $orderNo1 = $orderNo;
        }
        $redis_con = new RedisController();
        $redis = $redis_con->connectredis();
        $parkInfo = $redis->get("parkservice:$key_admin:$orderNo");
        $parkInfo = json_decode($parkInfo,true);

        $params = $this->getPublicParams();
        $params['service']="parkhub.order.deductionNotSettle";
        $params['tradeId']=$parkInfo['tradeId'];
        $params['deductionAmount']=$parkInfo['unPayAmount'];
        $params['outTradeNo']=$orderNo1;
        $params['accountId']=$parkInfo['accountId'];
        $params['sign'] = $this->DameiSign($params);
        $result = http($this->url, json_encode($params),'POST',$this->rheader,true);
        writeOperationLog(array('result' => $result), 'damei_park');
        if (!is_json($result)){
            return false;
        }
        $array=json_decode($result,true);
        if ($array['returnCode']=="T" && $array["isSuccess"]=="0"){
            return $array;
        }else{
            return (string)"104";
        }
    }

    public function getparkstatus($build, $floor, $sign_key, $key_admin,$admininfo){
        return false;
    }


}
