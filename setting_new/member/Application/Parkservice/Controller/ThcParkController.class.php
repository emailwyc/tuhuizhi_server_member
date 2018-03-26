<?php

namespace Parkservice\Controller;

use Think\Controller;
use Common\Controller\RedisController;

class ThcParkController extends Controller implements ParkinterfaceController
{
    // 天河城停车系统请求相关配置
    protected $url = 'http://221.239.56.244:1003/JSONBaseWebService.asmx';
    protected $url1 = 'http://221.239.56.244:1004/VIID/park';

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
     *  搜索车辆列表 此处返回正确车俩的停车信息enterTime,ticketNo,parkDuration,carPlate,imageURL
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function searchcar($carno,$sign_key,$key_admin = '',$page,$lines) {
        $value = array('FAreaCardNo'=>$carno,'PalteNoColor'=>"");
        $params['value']=json_encode($value);
        $result = http($this->url."/GetClassSettlementByPlateNumber", $params);
        $result = $this->xml_parser($result);
        if (!$result || empty($result[0])){
            return false;
        }
        $array = $result[0];
        if (!is_json($array)){
            return false;
        }
        $array = json_decode($array,true);
        $carList = array('code'=>200,'data'=>array(),'msg'=>"success!");
        if($array){
            $record = array();
            $record['MoneyValue'] = (int)$array['PayAmount']*100;
            $record['CarSerialNo'] = $array['PlateNo'];
            $record['BeginTime'] = $array['InDateTime'];
            $record['EndTime'] = $array['FPayDate'];
            $record['Status'] = $array['Status']=="未支付"?2:0;
            //车场信息
            $record['totalAmount'] = $array['totalAmount'];
            $record['BusID'] = $array['BusID'];
            $record['totalSecs'] = $array['totalSecs'];
            $record['PaySecs'] = $array['PaySecs'];
            $record['totalFreeSecs'] = $array['totalFreeSecs'];
            $record['FAreaCardNo'] = $array['FAreaCardNo'];
            $record['FCustID'] = $array['FCustID'];
            //end
            $record['CarImg'] = "";
            $record['floor'] = "";
            $record['fl'] = "";
            $record['ParkingNo'] = "";
            $carList['data'][] = $record;
        }
        if ($array['Status']=="未支付"){
            return $carList['data'];
        }else{
            return array();
        }
    }

    /**
     * 从列表中选择我的车辆信息
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function choosemycar($carno,$sign_key,$key_admin = '') {
        $value = array('FAreaCardNo'=>$carno,'PalteNoColor'=>"");
        $params['value']=json_encode($value);
        $result = http($this->url."/GetClassSettlementByPlateNumber", $params);
        $result = $this->xml_parser($result);
        if (!$result || empty($result[0])){
            return false;
        }
        $array = $result[0];
        if (!is_json($array)){
            return false;
        }
        $array = json_decode($array,true);
        $record = array();
        $record['MoneyValue'] = (int)$array['PayAmount']*100;
        $record['CarSerialNo'] = $array['PlateNo'];
        $record['BeginTime'] = $array['InDateTime'];
        $record['EndTime'] = $array['FPayDate'];
        $record['Status'] = $array['Status']=="未支付"?2:0;
        //车场信息
        $record['totalAmount'] = $array['totalAmount'];
        $record['BusID'] = $array['BusID'];
        $record['totalSecs'] = $array['totalSecs'];
        $record['PaySecs'] = $array['PaySecs'];
        $record['totalFreeSecs'] = $array['totalFreeSecs'];
        $record['FAreaCardNo'] = $array['FAreaCardNo'];
        $record['FCustID'] = $array['FCustID'];
        //end
        $record['CarImg'] = "";
        $record['floor'] = "";
        $record['ParkingNo'] = "";
        $result = array('code'=>200,'data'=>$record,'msg'=>'success');
        if ($array['Status']=="未支付"){
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
        $discount = empty($discount)?0:$discount;
        if($paytype==1){//积分支付
            $value = 0;
        }else{//钱支付
            $value = $amount;
        }
        $redis_con = new RedisController();
        $redis = $redis_con->connectredis();
        $parkInfo = $redis->get("parkservice:$key_admin:$orderNo");
        $parkInfo = json_decode($parkInfo,true);
        $payAmount = !empty($parkInfo['MoneyValue'])?($parkInfo['MoneyValue']/100):$value;
        $FFreeSecs = (int)($parkInfo['totalSecs']-$parkInfo['PaySecs']);
        $value = array(
            'FPayStatus'=>1,
            'FBusID'=>$parkInfo['BusID'],
            'FTypeName'=>"手机支付",
            'FOutCarNo'=>$carno,
            'FAmount'=>$parkInfo['totalAmount'],//应收 number_format(2/3,1);
            'FUserNo'=>"12345",
            'FUserName'=>"12345",
            'FPayAmount'=>$payAmount,
            'FTotalSecs'=>$parkInfo['totalSecs'],
            'FFreeSecs'=>$FFreeSecs,
            'FPaySecs'=>$parkInfo['PaySecs'],
            'FDateEnd'=>date('Y-m-d H:i:s',time()),
            'FOutDevID'=>0,
        );
        $params['value']=json_encode($value);
        $result = http($this->url."/UpdateParkInOutDetail", $params);
        $result = $this->xml_parser($result);
        if (!$result || empty($result[0])){
            return false;
        }
        $array = $result[0];
        if (!is_json($array)){
            return false;
        }
        $array = json_decode($array,true);
        if ("OK"==$array['Status']){
            return $array;
        }else{
            return (string)"104";
        }
    }

    public function getparkstatus($build, $floor, $sign_key, $key_admin,$admininfo){
        return false;
    }

    private function xml_parser($str){
        $xml_parser = xml_parser_create();
        if(!xml_parse($xml_parser,$str,true)){
            xml_parser_free($xml_parser);
            return false;
        }else {
            return (json_decode(json_encode(simplexml_load_string($str)),true));
        }
    }

}