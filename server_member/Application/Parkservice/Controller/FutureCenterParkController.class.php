<?php
/**
 * 未来中心停车接口
 * User: wutong
 * Date: 17/06/19
 * Time: 17:44 PM
 */

namespace Parkservice\Controller;

use Common\Controller\WebserviceController;
use Think\Controller;

class FutureCenterParkController extends Controller implements ParkinterfaceController
{
    // 未来中心停车系统配置
    protected $url = 'http://h0000k.imwork.net:57799/bosiny/wechat';
    protected $post = 9749;

    /**
     * 获取剩余车位数
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     *  http://localhost/member/index.php/Parkservice/FutureCenterPark/getleftpark?sign_key=zhihuitu&key_admin=rtmap911&sing_key=o0b1TweSrqc1B-9kjPh_CaelVY7g
     */
    public function getleftpark($sign_key,$key_admin)
    {
        $mcode='200101';

        $head=$this->getHead($mcode);
        $data='{'.$head.',"body":[{}]}';

        $array = $this->postData($data);

        $return_data = array(
            array('location' => 'total', 'leftnum' => $array['body'][0]['totalleftnum']),
        );

        return $return_data;
    }

    /**
     * 搜索车辆列表(模糊查找)
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     *  http://localhost/member/index.php/Parkservice/FutureCenterPark/searchcar?sign_key=zhihuitu&key_admin=rtmap911&sing_key=o0b1TweSrqc1B-9kjPh_CaelVY7g&carno=%E6%B8%9DT0236F&page=1&lines=1
     */
    public function searchcar($carno,$sing_key,$key_admin,$page,$lines){
        $array = $this->adaptation($carno,$sing_key,$key_admin,$page,$lines);

        if (!empty($array['body'])){
            $re_data['BeginTime'] = $array['body'][0]['indate'].$array['body'][0]['intime'];
            $re_data['EndTime'] = $array['body'][0]['endtime'];
            $re_data['times'] = $array['body'][0]['times'];
            $re_data['IntValue'] = '';
            $re_data['MoneyValue'] = $array['body'][0]['totalfee'] * 100;
            $re_data['orderNo'] = '';
            $re_data['VIPBaseBonus'] = '';
            $re_data['VIPIntValue'] = '';
            $re_data['carimg'] = '';
            $re_data['CarSerialNo'] = $carno;

            return array($re_data);
        }
        else
        {
            return array();
        }
    }

    /**
     * 从列表中选择我的车辆信息(精确查找)
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     *  http://localhost/member/index.php/Parkservice/FutureCenterPark/choosemycar?sign_key=zhihuitu&key_admin=rtmap911&sing_key=o0b1TweSrqc1B-9kjPh_CaelVY7g&carno=localhost/member/index.php/Parkservice/FutureCenterPark/choosemycar?sign_key=zhihuitu&key_admin=rtmap911&sing_key=o0b1TweSrqc1B-9kjPh_CaelVY7g&carno=粤B12345
     */
    public function choosemycar($carno,$sing_key,$key_admin){
        $array = $this->adaptation($carno,$sing_key,$key_admin);

        if (!empty($array['body'])){
            $re_data['BeginTime'] = $array['body'][0]['intime'];
            $re_data['EndTime'] = $array['body'][0]['payTime'];
            $re_data['times'] = $array['body'][0]['times'];
            $re_data['IntValue'] = '';
            $re_data['MoneyValue'] = $array['body'][0]['totalfee'] * 100;
            $re_data['orderNo'] = '';
            $re_data['VIPBaseBonus'] = '';
            $re_data['VIPIntValue'] = '';
            $re_data['carimg'] = '';
            $re_data['CarSerialNo'] = $carno;

            return array('data' => $re_data);
        }
        else
        {
            return array();
        }
    }

    /**
     * 支付状态确认
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     *  http://localhost/member/index.php/Parkservice/FutureCenterPark/paystatus?sign_key=zhihuitu&key_admin=rtmap911&carno=粤B12342&paytype=0
     *  //粤B12341、粤B12342、粤B12343、粤B12344、粤B12345
     */
    public function paystatus($carno,$sign_key,$paytype,$key_admin){
        $mcode='200107';
        $vehicleno=$carno;		//车牌号
        $wechatcode=$sign_key;	//微信号
        $cardno='';
        $couponnum='';
        $payfee = '0';
        $paymode = $paytype;

        $head=$this->getHead($mcode);
        $data='{'.$head.',"body":[{"wechatcode":"'.$wechatcode.'","vehicleno":"'.$vehicleno.'","cardno":"'.$cardno.'","couponnum":"'.$couponnum.'","payfee":"'.$payfee.'","paymode":"'.$paymode.'"}]}';

        $array = $this->postData($data);

        if (!empty($array['body']))
        {
            return $array['body'];
        }
        else
        {
            return array();
        }
    }

    /**
     * 适配按车牌搜索
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     *  //粤B12341、粤B12342、粤B12343、粤B12344、粤B12345
     */
    public function adaptation($carno,$sing_key,$key_admin,$page=1,$lines=1)
    {
        $mcode='200106';
        $vehicleno=$carno;		//车牌号
        $wechatcode=$sing_key;	//微信号
        $cardno='';
        $couponnum='';

        $head=$this->getHead($mcode);
        $data='{'.$head.',"body":[{"wechatcode":"'.$wechatcode.'","vehicleno":"'.$vehicleno.'","cardno":"'.$cardno.'","couponnum":"'.$couponnum.'"}]}';

        $reData = $this->postData($data);

        if(!empty($reData['body'][0]))
        {
            $emdtime = strtotime($reData['body'][0]['indate'].$reData['body'][0]['intime']) + ceil($reData['body'][0]['times'] * 60);
            $reData['body'][0]['endTime'] = date('Ymdhis',$emdtime);
        }

        return $reData;
    }


    /**
     * 车场车位状态
     * @param unknown $floor
     * @param unknown $build
     * @param unknown $sign_key
     * @param unknown $key_admin
     * 正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getparkstatus($build,$floor,$sign_key,$key_admin,$admininfo){}


    //生成头信息
    public function getHead($mcode){
        $data['mcode']=$mcode;
        $data['mid']=$this->getMid();
        $data['date']=$this->getShortDate();
        $data['time']=$this->getShortTime();
        $data['ver']='0001';
        $data['msgatr']='10';
        $data['safeflg']='11';
        $data['key']='02468ACE13579BDF'; // 固定密钥（不需要更改）
        $data['mac']=strtoupper(MD5('mcode='.$data['mcode'].'&mid='.$data['mid'].'&date='.$data['date'].'&time='.$data['time'].'&ver='.$data['ver'].'&msgatr='.$data['msgatr'].'&safeflg='.$data['safeflg'].'&key='.$data['key'].''));
        // dump($data);
        $head='"head":[{"ver":"'.$data['ver'].'","mid":"'.$data['mid'].'","mac":"'.$data['mac'].'","mcode":"'.$data['mcode'].'","msgatr":"10","safeflg":"'.$data['safeflg'].'","time":"'.$data['time'].'","date":"'.$data['date'].'"}]';
        return $head;
    }

    //生成交易流水号
    public function getMid() {
        list($t1, $t2) = explode(' ', microtime());
        $now=(float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $r=rand(100, 999);
        $mid=$now.$r;
        return  $mid;
    }

    //20170621 格式
    public function getShortDate(){
        $date=date('Ymd',time());
        return $date;
    }

    //153026 格式
    public function getShortTime(){
        $date=date('His',time());
        return $date;
    }

    // POST提交到接口并解析JOSIN
    public function postData($data){
        $url= $this->url; //请求接口（需要更改）
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
        $result = curl_exec($ch);
        $result = json_decode($result,true);
        return $result;
    }

}