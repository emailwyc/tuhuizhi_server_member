<?php
namespace Parkservice\Controller;

use Think\Controller;

class ShiouParkController extends Controller implements ParkinterfaceController
{
    /**
     * 世欧停车接口类
     * @var unknown
     */
    private $api_url='http://183.252.17.251:8020/ParkService.asmx';
    /*
    *按车牌号查询停车的详细信息(返回金额按照元处理)
    */
    public function choosemycar($carno,$sing_key,$key_admin='')
    {
        $params['carno']=$carno;
        $params['sign_key']=$sing_key;
        $sign=sign($params);
        $params['sign']=$sign;
        unset($params['sign_key']);
        $result=http($this->api_url.'/QueryPassDetailInfo', json_encode($params),'POST',array(),true);
        if (is_json($result)){
            $array=json_decode($result,true);
            if (200==$array['code']){
                if($array['data']['Status']==0){
                    return "1103";
                }
                $array['data']['MoneyValue'] = $array['data']['MoneyValue'] * 100;
                return $array;
            }else{
                return $array;
            }
        }else{
            return false;
        }
    }

    /*
    *获取空闲车位数
    */
    public function getleftpark($sign_key,$key_admin='')
    {
        // TODO Auto-generated method stub
        $params['sign_key']=$sign_key;
        $sign=sign($params);
        $params['sign']=$sign;
        unset($params['sign_key']);
        $result=http($this->api_url.'/CarSpaces', json_encode($params),'POST',array(),true);
        if (is_json($result)){
            $array=json_decode($result,true);
            if (200==$array['code']){
                return $array['data'];
            }else{
                return (string)$array['code'];
            }
        }else{
            return false;
        }

    }

    /*
    * 通知车场支付成功
    */
    public function paystatus($carno, $sign_key, $paytype, $key_admin = '', $orderNo = '', $amount = '', $discount = '')
    {
        // TODO Auto-generated method stub
        $paytype = $paytype == 1?1:2;
        //金额跟积分换算;
        $pv = @(int)$this->scorepv;
        if($paytype==1){
            $value = ($amount/$pv);
            $value = number_format($value, 2, '.', '');
        }else{
            $value = $amount;
        }
        $params['sign_key']=$sign_key;
        $params['type']=$paytype;
        $params['carno']=$carno;
        $params['value']=$value;
        $params['discount']=$discount;
        $sign=sign($params);
        $params['sign']=$sign;
        unset($params['sign_key']);//dump($params);die;
        writeOperationLog(array('result' => $params), 'shiou_park');
        $result=http($this->api_url.'/PrePayNotice', json_encode($params),'POST',array(),true);
        if (is_json($result)){
            $array=json_decode($result,true);
            if (200==$array['code']){
                return $array;
            }else{
                return (string)$array['code'];
            }
        }else{
            return false;
        }
    }

    /*
    * 模糊搜索车辆列表
    */
    public function searchcar($carno,$sing_key,$key_admin='',$page,$lines)
    {
        // TODO Auto-generated method stub
        $params['carno']=$carno;
        $params['sign_key']=$sing_key;
        $sign=sign($params);
        $params['sign']=$sign;
        unset($params['sign_key']);
        $result=http($this->api_url.'/QueryPassInfo', json_encode($params),'POST',array(),true);
        if (is_json($result)){
            $array=json_decode($result,true);
            if (200==$array['code']){
                $newArr = array();
                if($array['data']) {
                    foreach ($array['data'] as $k => $v) {
                        if($v['CarSerialNo']!="null" && !empty($v['CarSerialNo'])){
                            $v['fl'] = $v['floor'];
                            $newArr[]=$v;
                        }
                    }
                }
                return $newArr;
            }else{
                return (string)$array['msg'];
            }
        }else{
            return false;
        }
    }

    /*
    * 车场车位详细信息
    */
    public function getparkstatus($build, $floor, $sign_key, $key_admin,$admininfo)
    {
        // TODO Auto-generated method stub
        $params['sign_key']=$sign_key;
        $params['floor']=$floor;
        $sign=sign($params);
        $params['sign']=$sign;
        unset($params['sign_key']);

        $result=http($this->api_url.'/getparkstatus', $params,'POST');
        if (is_json($result)){
            $array=json_decode($result,true);
            if (200==$array['code']){
                $poidb=M('map_poi_'.$build,$admininfo['pre_table']);
                $thisfloorpoi=$poidb->where(array('floor'=>$floor))->select();
                $returndata=null;//返回本楼层数据
                //究竟是把从数据库查出来的数据放外层循环好呢，还是把西单拉取的一大堆数据放外层循环呢？这得综合考虑大部分数据
                foreach ($thisfloorpoi as $key => $val){//数据库里面的poi点
                    foreach ($array['data'] as $k =>$v){//西单返回的数据
                        if ($v['parkname']==$val['poi_name']){
                            $returndata[]=array('floor'=>$floor,'parkspace'=>$v['parkname'],'status'=>(int)$v['parkstatus']);
                        }
                    }
                }
                return $returndata;
            }else{
                return (string)$array['code'];
            }
        }else{
            return false;
        }



    }

}

?>
