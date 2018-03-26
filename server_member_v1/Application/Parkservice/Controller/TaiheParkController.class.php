<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 12/07/16
 * Time: 18:09 PM
 */

namespace Parkservice\Controller;

use Think\Controller;

use PublicApi\Controller\QiniuController;

class TaiheParkController extends Controller implements ParkinterfaceController
{
    /**
     * 获取剩余车位数
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getleftpark($sign_key,$key_admin){
        $Bone=$this->carno_num('B1');
        $Btwo=$this->carno_num('B2');
        $arr[0]['location']='total';
        $arr[0]['leftnum']=$Bone['leftnum']+$Btwo['leftnum'];
        $arr[1]=$Bone;
        $arr[2]=$Btwo;
        return $arr;
    }
    protected function carno_num($floor){
        $url = 'http://61.131.50.91:30101/ZRWS.asmx/FindCarInfoByFloor';
        $params['floor']=$floor;
        $params['carUse']=0;
        $Bone_arr_json = http($url,$params,'post');
        if($Bone_arr_json){
            $Bone_arr=json_decode($Bone_arr_json,true);
            $Bone_num=count($Bone_arr['carInfo']);
        }else{
            $Bone_num=0;
        }
        $arr['location']=$floor;
        $arr['leftnum']=$Bone_num;
        return $arr;
    }

    /**
     * 搜索车辆列表
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function searchcar($carno,$sing_key,$key_admin,$page,$lines){
        $param['plateNo']=$carno;
        $param['pageIndex']=$page;
        $param['pageSize']=$lines;
        $url = 'http://61.131.50.91:30101/ZRWS.asmx/FindCarInfo';
        $curl_re = http($url, $param, 'post');
        writeOperationLog(array('riyue_parking_findcarinfo' => $curl_re), 'zhanghang');
        if(!is_json($curl_re)){
            return false;
        }
        $res = json_decode($curl_re, true);
        foreach($res['carInfo'] as $k=>$v){
            $res['carInfo'][$k]['BaseBonus']=0;
            $res['carInfo'][$k]['BeginTime']=$v['DateTime'];
            $res['carInfo'][$k]['CarSerialNo']=$v['CarPlateNo'];
            $res['carInfo'][$k]['EndTime']='';
            $res['carInfo'][$k]['IntValue']=0;
            $res['carInfo'][$k]['MoneyValue']=0;
            $res['carInfo'][$k]['Status']='';
            $res['carInfo'][$k]['VIPBaseBonus']=0;
            $res['carInfo'][$k]['VIPIntValue']=0;
            $res['carInfo'][$k]['carimg']='';
            $res['carInfo'][$k]['ParkingNo']=$v['ParkingNo'];
            if(strpos($v['fl'],"二层")){
                $res['carInfo'][$k]['fl']="B2";
            }else{
                $res['carInfo'][$k]['fl']="B3";
            }

            unset($res['carInfo'][$k]['lc']);
        }
        $data = $res['carInfo'];
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
    public function getparkstatus($build,$floor,$sign_key,$key_admin,$admininfo){
//         $param['key_admin'] = $key_admin;
        $url = 'http://61.131.50.91:30101/ZRWS.asmx/FindCarInfoAll';
        $curl_re = http($url, array());
        if(!is_json($curl_re)){
            return false;
        }
        writeOperationLog(array('riyue_parking_findcarinfoall' => $curl_re), 'zhanghang');
        $res = json_decode($curl_re, true);
        foreach($res['carInfo'] as $k=>$v){
            $arr[$k]['floor']=$floor;
            $arr[$k]['parkspace']=$v['CarParkName'];
            $arr[$k]['status']=$v['CarUse'];
        }
        return $arr;
    }

}