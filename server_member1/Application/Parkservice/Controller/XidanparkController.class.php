<?php
namespace Parkservice\Controller;

use Think\Controller;
//use Common\Controller\ErrorcodeController;
//use Parkservice\Controller\ParkController;

class XidanparkController extends Controller implements ParkinterfaceController
{
    /**
     * 西单停车接口类，不用给西单传key_admin
     * @var unknown
     */
    private $api_url='http://fw.joycity.mobi/kaapi/Park/Index';//西单打车的url地址，后面还需要跟方法名
 /* 选择
     * @see \Parkservice\Controller\ParkController::choosemycar()
     */
    public function choosemycar($carno,$sing_key,$key_admin='')
    {
        $params['carno']=$carno;
        $params['sign_key']=$sing_key;
        $sign=sign($params);
        $params['sign']=$sign;
        unset($params['sign_key']);
        $result=http($this->api_url.'/getcarcontent', $params,'POST');
        if (is_json($result)){
            $array=json_decode($result,true);
            if (200==$array['code']){
                $array['data']['MoneyValue'] = $array['data']['MoneyValue'] * 100;
                return $array['data'];
            }else{
                return (string)$array['code'];
            }
        }else{
            return false;
        }
    }

 /* (non-PHPdoc)
     * @see \Parkservice\Controller\ParkController::getleftpark()
     */
    public function getleftpark($sign_key,$key_admin='')
    {
        // TODO Auto-generated method stub
        $params['sign_key']=$sign_key;
        $sign=sign($params);
        $params['sign']=$sign;
        unset($params['sign_key']);
        $result=http($this->api_url.'/getfreepark', $params,'POST');
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

 /* (non-PHPdoc)
     * @see \Parkservice\Controller\ParkController::paystatus()
     */
    public function paystatus($carno,$sign_key,$paytype,$key_admin='',$orderNo = '', $amount = '', $discount = '')
    {
        // TODO Auto-generated method stub
        $params['sign_key']=$sign_key;

        if ($paytype == 0) { // 微信支付传递支付类型
            $params['type']=1;
        } else {
            $params['type']=0;
        }

        $params['carno']=$carno;
        $sign=sign($params);
        $params['discount']=$discount; // 抵扣金额
        $params['amount']=$amount; // 实际支付的金额
        $params['sign']=$sign;
        unset($params['sign_key']);//dump($params);die;

        if ($paytype == 1) {
            $url = $this->api_url.'/paystatus'; // 积分支付通知接口
        } else if ($paytype == 0) {
            $url = $this->api_url.'/wechat_notice'; // 微信支付通知接口
        }

        $result=http($url, $params,'POST');

//        writeOperationLog(array('通知车场底层paytype' => $paytype), 'jaleel_logs');
        writeOperationLog(array('通知车场底层url' => $url), 'jaleel_logs');
        writeOperationLog(array('通知车场底层参数' => json_encode($params)), 'jaleel_logs');
        writeOperationLog(array('通知车场底层结果' => $result), 'jaleel_logs');

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

 /* (non-PHPdoc)
     * @see \Parkservice\Controller\ParkController::searchcar()
     */
    public function searchcar($carno,$sing_key,$key_admin='')
    {
        // TODO Auto-generated method stub
        $params['carno']=$carno;
        $params['sign_key']=$sing_key;
        $sign=sign($params);
        $params['sign']=$sign;
        unset($params['sign_key']);
        
        $result=http($this->api_url.'/searchcar', $params,'POST');
        if (is_json($result)){
            $array=json_decode($result,true);
            if (200==$array['code']){
                return $array['data'];
            }else{
                return (string)400;
            }
        }else{
            return false;
        }
    }
    
 /* (non-PHPdoc)
     * @see \Parkservice\Controller\ParkinterfaceController::getparkstatus()
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