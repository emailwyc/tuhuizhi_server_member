<?php
/**
 * Created by PhpStorm.
 * User: zhanghang
 * Date: 31/10/17
 * Time: 11:20 AM
 */

namespace Parkservice\Controller;

use Think\Controller;
use Common\Controller\DESController;

class SuzhouchengtaiParkController extends Controller implements ParkinterfaceController
{
    public $url = 'http://58.210.54.154:8097/api/find';
    public $user = 'ktapi';
    public $pwd = '0406F1';
    public $key = '90625D453C0324CBB0B3C916';
    
    /**
     * 获取剩余车位数
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getleftpark($sign_key,$key_admin){
//     public function getleftpark(){
        $params['floorId']='-1';
        $params['areaId']='-1';
        $data = json_encode($params);
        $date = date('Ymd');
        $des = new DESController($this->key,$date);
        
        //         echo $data;die;
        $des_info['data'] = $des->encrypt($data);
        $url = $this->url.'/GetFreeSpaceNum';
        $headers = array(
            'user:'.$this->user,
            'pwd:'.$this->pwd,
            'Content-Type:application/json;charset=utf-8'
        );
        $json_data = json_encode($des_info);
        $return_info = http_auth($url,$json_data,'POST',"",$headers,true);
        print_R($return_info);die;
        $return_data = json_decode($return_info,true);
        if($return_data['resCode'] == '0' &&  is_json($return_info)){
            $arr['location']='total';
            $arr['leftnum']=$return_info['data']['freeSpaceNum'];
        }else{
            $arr = $return_info['resMsg'];
        }
        return $arr;
    }
    
    /**
     * 搜索车辆列表
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function searchcar($carno,$sing_key,$key_admin,$page,$lines){
        $param['plateNo']=$carno;
//         $param['pageIndex']=$page;
//         $param['pageSize']=$lines;
        $date = date('Ymd');
        $data = json_encode($param);
        $des = new DESController($this->key,$date);
        
//         echo $data;die;
        $des_info['data'] = $des->encrypt($data);
        $url = $this->url.'/GetCarLocInfo';
//         $auth_str = "user:ktapi,pwd:0406F1";
        $headers = array(
            'user:'.$this->user,
            'pwd:'.$this->pwd,
            'Content-Type:application/json;charset=utf-8'
        );
        $json_data = json_encode($des_info);
        $return_info = http_auth($url,$json_data,'POST',"",$headers,true);
//         print_r($return_info);//die;
        $return_data = json_decode($return_info,true);
        if($return_data['resCode'] == '0' &&  is_json($return_info)){
            
            foreach($return_data['data'] as $k=>$v){
                $res_data['fl'] = $v['floorName'];
                $res_data['ParkingNo']=$v['spaceNo'];
                $res_data['DateTime']=$v['inTime'];
                $res_data['CarPlateNo']=$carno;
                $res_data['BaseBonus']=0;
                $res_data['BeginTime']=$v['inTime'];
                $res_data['CarSerialNo']=$carno;
                $res_data['EndTime']='';
                $res_data['parkname']=$v['parkName'];
                $res_data['carimg']=$v['carImage'];
                $msg_data[] = $res_data;
            }
        }else{
            $msg_data = $res_data['resMsg'];
        }
//         print_R($msg_data);
        return $msg_data;
    }
    

    /**
     * 从列表中选择我的车辆信息
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function choosemycar($carno,$sing_key,$key_admin){
        $param['plateNo']=$carno;
        $date = date('Ymd');
        $data = json_encode($param);
        $des = new DESController($this->key,$date);
        
        //         echo $data;die;
        $des_info['data'] = $des->encrypt($data);
        $url = $this->url.'/GetCarLocInfo';
        //         $auth_str = "user:ktapi,pwd:0406F1";
        $headers = array(
            'user:'.$this->user,
            'pwd:'.$this->pwd,
            'Content-Type:application/json;charset=utf-8'
        );
        $json_data = json_encode($des_info);
        $return_info = http_auth($url,$json_data,'POST',"",$headers,true);
//         print_r($return_info);die;
        $return_data = json_decode($return_info,true);
        if($return_data['resCode'] == '0' &&  is_json($return_info)){
            $res_data['fl'] = $return_data['data']['floorName'];
            $res_data['ParkingNo']=$return_data['data']['spaceNo'];
            $res_data['DateTime']=$return_data['data']['inTime'];
            $res_data['CarPlateNo']=$carno;
            $res_data['BaseBonus']=0;
            $res_data['BeginTime']=$return_data['data']['parkTime'];
            $res_data['CarSerialNo']=$carno;
            $res_data['EndTime']='';
            $res_data['parkname']=$return_data['data']['parkName'];
            $res_data['carimg']=$return_data['data']['carImage'];
        }else{
            $res_data = $return_data['resMsg'];
        }
        return $res_data;
    }

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
    }

}