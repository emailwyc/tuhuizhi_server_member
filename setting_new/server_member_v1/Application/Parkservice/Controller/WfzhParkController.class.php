<?php

namespace Parkservice\Controller;

use Think\Controller;
use Common\Controller\RedisController;

class WfzhParkController extends Controller implements ParkinterfaceController
{
    // 西安金地停车系统请求相关配置
    protected $url = 'http://111.203.73.67:8090/park_service/parkinfo/parkPayInfo.do';

    /**
     *  获取剩余车位数
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getleftpark($sign_key,$key_admin = '') {
        $params = array(
            "method"=>"getParkList",
            "pageCount"=>6,
            "page"=>1,
            "parkId"=>1
            );
        $url = "http://111.203.73.67:8090/park_service/parkinfo/parkPmsInfo.do";
        $result = http($url, $params);
        $result = iconv("GB2312", "UTF-8", $result);
        if (!is_json($result)){
            return false;
        }
        $array=json_decode($result,true);
        $spaceTotal = 0;
        if($array['data']){
            foreach ($array['data'] as $v){
                $spaceTotal +=(int)$v['portSpaceAvailable'];
            }
        }
        $array = array('code'=>200,'data'=>array(array("location"=>"total","leftnum"=>$spaceTotal)),'msg'=>'SUCCESS');
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
        $params['method']="queryParkInfoByPlate";
        $params['carPlate']=$carno;
        $result = http($this->url, $params,'GET',array(),false,10);
        if (!is_json($result)){
            return false;
        }
        $array=json_decode($result,true);
        $carList = array('code'=>200,'data'=>array(),'msg'=>"success!");
        if($array){
            foreach ($array as $k=>$v){

                $url1="http://111.203.73.67:8090/park_service/parkinfo/parkPmsInfo.do";
                $params1 = array(
                    'method'=>"getCarList",
                    "pageCount" => 6,
                    "page"=>1,
                    "condition"=>1,
                    "plateNo"=>$v['carPlate'],
                );
                $result1 = http($url1,$params1);
                $result1 = iconv("GB2312", "UTF-8", $result1);
                $array1=json_decode($result1,true);
                $floor = !empty($array1['data'][0])?mb_substr((string)$array1['data'][0]['carportId'],0,2,'UTF-8'):"";
                $parkingno = !empty($array1['data'][0])?$array1['data'][0]['spaceNo']:"";

                $record = array();
                $record['CarSerialNo'] = $v['carPlate'];
                $record['BeginTime'] = date('Y-m-d H:i:s',strtotime($v['enterTime']));
                $record['EndTime'] = date('Y-m-d H:i:s',(strtotime($v['enterTime'])+$v['parkDuration']*60));
                $record['Status'] = 2;
                $record['CarImg'] = $v['imageURL'];
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
        $params['method']="queryParkInfo";
        $params['TicketType']="1";
        $params['TicketNo']=$carno;
        $params['DeductMinutes']=0;
        $result = http($this->url, $params,'GET',array(),false,10);
        if (!is_json($result)){
            return false;
        }
        $array=json_decode($result,true);
        $record = array();
        $record['MoneyValue'] = (int)$array['parkCharge']*100;
        $record['CarSerialNo'] = $array['carPlate'];
        $record['BeginTime'] = date('Y-m-d H:i:s',strtotime($array['enterTime']));
        $record['EndTime'] = date('Y-m-d H:i:s',(strtotime($array['enterTime'])+$array['parkDuration']*60));
        $record['Status'] = 2;
        $record['ticketNo'] = $array['ticketNo'];
        $record['enterTime'] = $array['enterTime'];
        $record['CarImg'] = $array['imageURL'];
        $record['floor'] = "";
        $record['ParkingNo'] = "";
        $result = array('code'=>200,'data'=>$record,'msg'=>'success');
        if (!empty($array['parkCharge'])){
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
        $params['method']="setPayStatus";
        $params['TicketNo']=$parkInfo['ticketNo'];
        $params['EnterTime']=$parkInfo['enterTime'];
        $params['PayTrxNo']='09'.$orderNo;
        $params['Amount']=!empty($parkInfo['MoneyValue'])?($parkInfo['MoneyValue']/100):$value;

        $result = http($this->url, $params,'GET',array(),false,10);
        writeOperationLog(array('result' => $result), 'xianjindi_park');
        if (!is_json($result)){
            return false;
        }
        $array=json_decode($result,true);
        if (1==$array['update_status']){
            return $array;
        }else{
            return (string)"104";
        }
    }

    public function getparkstatus($build, $floor, $sign_key, $key_admin,$admininfo){
        return false;
    }


}
