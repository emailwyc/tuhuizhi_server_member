<?php
/**
 * Created by PhpStorm.
 * User: soone
 * Date: 17-5-12
 * Time: 上午10:48
 */
namespace CrmService\Controller\Crmapi;

use CrmService\Controller\CommonController;
use CrmService\Controller\CrminterfaceController;

class KaidexingCrmController extends CommonController implements CrminterfaceController
{

    protected $request_url = 'http://kdmallapipv.companycn.net/rtmaps/member_api';//测试地址
    //protected $request_url = 'http://api.capitaland.com.cn/rtmaps/member_api';//正式地址
    protected $nonce;
    public function __construct(){
        $this->nonce = $this->generate();
        $this->returnstyle = true;
    }

    /**
     * @deprecated 根据Openid获取会员信息
     * @传入参数   key_admin、sign、openid
     */
    public function GetUserinfoByOpenid(){
        $params = I('param.');
        $this->paramsCheck($params,array('openid'),array('openid'));
        //根据openid查找会员卡号;
        $reqParam = array('tp'=>'get_member_no', 'openid'=>$params['openid'], 'timestamp'=>(string)time(), 'nonce'=>$this->nonce);
        $reqParam =$this->getSignByParams($reqParam);
        $url = $this->request_url."?".$reqParam['str'];
        $memCard = http($url,$reqParam['arr'],'post');
        if(is_json($memCard)) {
            $memCardArr = json_decode($memCard,true);
            if($memCardArr['result']==2001){
                $cardno = $memCardArr['data'];
            }else{
                returnjson(array("code"=>11,"data"=>$memCardArr['result'],'msg'=>$memCardArr['msg']),$this->returnstyle,$this->callback);
            }
        }else{
            returnjson(array("code"=>101),$this->returnstyle,$this->callback);
        }

        //根据会员卡号获取会员信息
        $reqParam1 = array('tp'=>'get_primary_info','memberNo'=>$cardno,'timestamp'=>(string)time(), 'nonce'=>$this->nonce);
        $reqParam1 =$this->getSignByParams($reqParam1);
        $url1 = $this->request_url."?".$reqParam1['str'];
        $memInfo = http($url1,$reqParam1['arr'],'post');
        if(is_json($memInfo)) {
            $memInfoArr = json_decode($memInfo,true);
            if($memInfoArr['result']==1001){
                //整理数据
                $return = array(
                    'cardno'  =>@$memInfoArr['data']['memberNo'],
                    'user'    =>@$memInfoArr['data']['nickName'],
                    'head_img'=>@$memInfoArr['data']['head_img_url'],
                    'cardtype'=>@$memInfoArr['data']['cardType'],
                    'score'=>@$memInfoArr['data']['integral']

                );
                returnjson(array("code"=>200,"data"=>$return),$this->returnstyle,$this->callback);
            }else{
                returnjson(array("code"=>11,"data"=>$memInfoArr['result'],'msg'=>$memCardArr['msg']),$this->returnstyle,$this->callback);
            }
        }else{
            returnjson(array("code"=>101),$this->returnstyle,$this->callback);
        }
    }

    /**
     * @deprecated 根据卡号获取会员信息
     * @传入参数   key_admin、sign、card
     */
    public function GetUserinfoByCard(){
        $params = I('param.');
        $this->paramsCheck($params,array('card'),array('card'));
        //根据会员卡号获取会员信息
        $reqParam1 = array('tp'=>'get_primary_info','memberNo'=>$params['card'],'timestamp'=>(string)time(), 'nonce'=>$this->nonce);
        $reqParam1 =$this->getSignByParams($reqParam1);
        $url1 = $this->request_url."?".$reqParam1['str'];
        $memInfo = http($url1,$reqParam1['arr'],'post');
        if(is_json($memInfo)) {
            $memInfoArr = json_decode($memInfo,true);
            if($memInfoArr['result']==1001){
                $return = array(
                    'cardno'  =>@$memInfoArr['data']['memberNo'],
                    'user'    =>@$memInfoArr['data']['nickName'],
                    'head_img'=>@$memInfoArr['data']['head_img_url'],
                    'cardtype'=>@$memInfoArr['data']['cardType'],
                    'score'=>@$memInfoArr['data']['integral']

                );
                returnjson(array("code"=>200,"data"=>$return),$this->returnstyle,$this->callback);
            }else{
                returnjson(array("code"=>11,'data'=>$memInfoArr['result'],'msg'=>$memInfoArr['msg']),$this->returnstyle,$this->callback);
            }
        }else{
            returnjson(array("code"=>101),$this->returnstyle,$this->callback);
        }
    }
    /**
     * @deprecated  积分添加
     * @传入参数  key_admin、sign、cardno、scoreno、scorecode、why、membername
     */
    public function addintegral(){}

    /**
     * @deprecated  创建会员
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name
     */
    public function createMember(){}

    /**
     * @deprecated  积分扣除
     * @传入参数  key_admin、sign、cardno、scoreno、why
     */
    public function cutScore(){
        $params = I('param.');
        $this->paramsCheck($params,array('cardno','scoreno','why'),array('cardno','scoreno'));
        //扣除积分
        $scoreno = (int)$params['scoreno'];
        $params['merchant_id'] = isset($params['merchant_id'])?$params['merchant_id']:"";
        if($scoreno<=0){
            returnjson(array("code"=>1051),$this->returnstyle,$this->callback);
        }
        $reqParam1 = array(
            'tp'=>'consume_integral',
            'memberNo'=>$params['cardno'],
            'integral'=>$params['scoreno'],
            'description'=>$params['why'],
            'merchant_id'=>@$params['merchant_id'],
            'timestamp'=>(string)time(),
            'nonce'=>$this->nonce
        );
        $reqParam1 =$this->getSignByParams($reqParam1);
        $url1 = $this->request_url."?".$reqParam1['str'];
        $memInfo = http($url1,$reqParam1['arr'],'post');
        if(is_json($memInfo)) {
            $memInfoArr = json_decode($memInfo,true);
            if($memInfoArr['result']==1001){
                $result = array(
                    'cardno'=>$params['cardno'],
                    'merchant_id'=>@$params['merchant_id'],
                    'scorenumber'=>$params['scoreno'],
                    'why'=>$params['why']
                    );
                returnjson(array("code"=>200,"data"=>$result,"msg"=>$memInfoArr['msg']),$this->returnstyle,$this->callback);
            }else{
                returnjson(array("code"=>11,'data'=>$memInfoArr['result'],'msg'=>$memInfoArr['msg']),$this->returnstyle,$this->callback);
            }
        }else{
            returnjson(array("code"=>101),$this->returnstyle,$this->callback);
        }

    }

    /**
     * @deprecated  修改会员信息
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name、cardno
     */
    public function editMember(){}

    /**
     * @deprecated 根据手机号获取会员信息
     * @传入参数  key_admin、sign、mobile
     */
    public function GetUserinfoByMobile(){}

    /**
     * @deprecated 用户积分详细列表
     */
    public function scorelist(){}

    /**
     * @deprecated 欧亚卖场
     * @传入参数 key_admin、sign 、skt、Jlbh、md
     */
    public function billInfo(){}

    protected function getSignByParams($params){
        $snArr = array((string)$params['timestamp'],(string)$params['nonce'],(string)$params['tp']);
        sort($snArr,SORT_STRING);
        $snstr = implode('^',$snArr);
        $snstr_md5 = md5($snstr);
        $sign = md5(substr_replace($snstr_md5,"Companycn",10,0));
        $params['sn'] = $sign;

        $str = '';
        foreach ($params as $k => $v) {
            if ('' == $str) {
                $str .= $k . '=' . trim($v);
            } else {
                $str .= '&' . $k . '=' . trim($v);
            }
        }
        return array('arr'=>$params,'str'=>$str);
    }


    protected function paramsCheck($params,$key_arr,$emptyArr=array()){
        if(!empty($key_arr)) {
            foreach ($key_arr as $v) {
                if (!isset($params[$v])) {
                    $msg['code'] = 1051;
                    returnjson($msg, $this->returnstyle, $this->callback);exit;
                }
            }
        }
        if(!empty($emptyArr)) {
            foreach ($emptyArr as $k) {
                if (empty($params[$k])) {
                    $msg['code'] = 1030;
                    returnjson($msg, $this->returnstyle, $this->callback);exit;
                }
            }
        }
    }
    protected function generate($length=6,$chars = 'abcdefghijklmnopqrstuvwxyz0123456789'){
        $password = '';
        for ( $i = 0; $i < $length; $i++ )  {
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $password;
    }

    
    /**
     * 解绑
     */
    public function UnBind(){}
}