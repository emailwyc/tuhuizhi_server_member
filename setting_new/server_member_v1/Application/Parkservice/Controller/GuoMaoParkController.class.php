<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 12/07/16
 * Time: 18:09 PM
 */

namespace Parkservice\Controller;

use Common\Controller\WebserviceController;
use Think\Controller;

class GuoMaoParkController extends Controller implements ParkinterfaceController
{
    /**
     * 获取剩余车位数
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getleftpark($sign_key,$key_admin){}

    /**
     * 搜索车辆列表
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function searchcar($carno,$sing_key,$key_admin,$page,$lines){
        $param['plateNo']=$carno;
        $param['parkId']=-1;
        $url = 'http://210.12.123.235:888/parkingapi.svc?wsdl';

        $obj = new WebserviceController('guomao_');
        $client = $obj->soapClient($url);
        $re = $client->GetCarLocInfoForOuter(array('strJson'=>json_encode($param)));
        $curl_re = $re->GetCarLocInfoForOuterResult;
        writeOperationLog(array('guomao_parking_findcarinfo' => $curl_re), 'jaleel_logs');
        if(!is_json($curl_re)){
            return false;
        }
        $res = json_decode($curl_re, true);
        foreach($res['Data'] as $k=>$v){
            $return['carInfo'][$k]['BaseBonus']=0;
            $return['carInfo'][$k]['BeginTime']=$v['EntryTime'];
            $return['carInfo'][$k]['CarSerialNo']=$v['PlateNo'];
            $return['carInfo'][$k]['EndTime']=date('Y-m-d H:i:s');
            $return['carInfo'][$k]['IntValue']=0;
            $return['carInfo'][$k]['MoneyValue']=0;
            $return['carInfo'][$k]['Status']='';
            $return['carInfo'][$k]['VIPBaseBonus']=0;
            $return['carInfo'][$k]['VIPIntValue']=0;
            $return['carInfo'][$k]['carimg']=$v['ImagePath'];;
            $return['carInfo'][$k]['ParkingNo']=$v['SpaceNo'];
            $return['carInfo'][$k]['Area']=$v['Area'];
            $return['carInfo'][$k]['fl']=substr($v['ParkName'], 0, 2);
            $return['carInfo'][$k]['ParkId']=$v['ParkId'];
        }
        $data = $return['carInfo'];
        return $data;
    }

    /**
     * 从列表中选择我的车辆信息
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function choosemycar($carno,$sing_key,$key_admin){}

    /**
     * 支付状态确认
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function paystatus($carno,$sign_key,$paytype,$key_admin){}


    /**
     * 车场车位状态
     * @param unknown $floor
     * @param unknown $build
     * @param unknown $sign_key
     * @param unknown $key_admin
     * 正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getparkstatus($build,$floor,$sign_key,$key_admin,$admininfo){}

    protected function curl_json($url, $data_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        $curl_re = curl_exec($ch);
        curl_close($ch);

        return $curl_re;
    }
}