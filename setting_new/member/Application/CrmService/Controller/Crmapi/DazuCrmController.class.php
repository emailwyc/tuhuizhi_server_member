<?php
namespace CrmService\Controller\Crmapi;

use CrmService\Controller\CommonController;
use CrmService\Controller\CrminterfaceController;

class DazuCrmController extends CommonController implements CrminterfaceController
{
    protected $request_url = 'http://36.110.53.101:8089/query.ashx';//测试地址
    protected $ruser  = "TEST";
    protected $rsecret = "20161220102452";
    protected $MallId = "BJSY01";
    protected $ReasonCode = "00090";
    protected $rheader  = array('Content-Type:application/x-www-form-urlencoded;');
    protected $rcrc;
    protected $baseParam;

    public function _initialize()
    {
        parent::_initialize();
        $rdate = date('Ymd');
        $rtime = date('His');
        $this->rcrc = $rdate.$rtime.rand(10000000,99999999);
        $this->baseParam = array(
            'reqdate' =>$rdate,
            'reqtime' =>$rtime,
            'method'  =>'',
            'user'    =>$this->ruser,
            'crc'     =>$this->rcrc,
            'sign'    =>'',
            'data'    =>array("MallId"=>$this->MallId,"IsThisCircle"=>false),
        );
    }

    /**
     * @deprecated 根据Openid获取会员信息(不是必须)
     * @传入参数   key_admin、sign、openid
     */
    public function GetUserinfoByOpenid(){}

    /**
     * @deprecated 根据卡号获取会员信息
     * @传入参数   key_admin、sign、card
     */
    public function GetUserinfoByCard(){
        $params = I('param.');
        $this->paramsCheck($params,array('key_admin','card'),array('card'));
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);
        $rparam = $this->baseParam;
        $rparam['method'] = "GetVipDis";
        $rparam['data']['VipCode'] = $params['card'];
        $rparam = $this->getDazuSign($rparam,$this->rsecret);
        $memInfo = http($this->request_url,$rparam,'POST',$this->rheader);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            if(0 == (int)$array['Header']['errcode']){
                if(!empty($array['Data'][0])){
                    $firstRd = $array['Data'][0];
                    //查询卡号和积分
                    $rt = array();
                    $rt['cardno']=$firstRd['VipCode'];
                    $rt['nickname'] = $firstRd['SurName'];
                    $rt['idnumber']="";
                    $rt['status']=$firstRd['Active'];
                    $rt['status_description']='';
                    $rt['expirationdate']=$firstRd['ExpiryDate'];//到期时间
                    $bir = @$firstRd['BirthdayYYYY']."-".@$firstRd['BirthdayMM']."-".@$firstRd['BirthdayDD'];
                    $bir = date('Y-m-d',strtotime($bir));
                    $rt['birthday']=$bir;
                    $rt['company']='';
                    $rt['score_num'] = @$firstRd['Bonus'];
                    $rt['phone']=@$firstRd['Mobile'];
                    $rt['mobile']=@$firstRd['Mobile'];
                    $rt['address']=@$firstRd['Address1'].".";
                    $rt['level']=@$firstRd['Grade'];
                    $rt['remark']=@$firstRd['VipCardNo'];
                    $rt['sex']= 'M'==@$firstRd['Sex'] ? 1 : 0;
                    $sel=$db->where(array('cardno'=>$rt['cardno']))->find();
                    if (null == $sel){
                        $db->add($rt);
                    }else{
                        $db->where(array('cardno'=>$rt['cardno']))->save($rt);
                    }
                    $datas['cardno']=$rt['cardno'];
                    $datas['xf_vipcardno']=$firstRd['VipCardNo'];
                    $datas['cardtype']=@$rt['level'];
                    $datas['name']=$rt['nickname'];
                    $datas['user']=$rt['nickname'];
                    $datas['status']=$rt['status'];
                    $datas['status_description']='';
                    $datas['getcarddate']="";//创建时间
                    $datas['expirationdate']=$rt['expirationdate'];//到期时间
                    $datas['birth']=$rt['birthday'];
                    $datas['birthday']=$rt['birthday'];
                    $datas['company']='';
                    $datas['phone']=$rt['phone'];
                    $datas['idnumber']="";
                    $datas['mobile']=$rt['mobile'];
                    $datas['address']=$rt['address'];
                    $datas['sex']=$rt['sex'];
                    $datas['score']=$rt['score_num'];
                    $msg['code']=200;
                    $msg['data']=$datas;
                }else{
                    $msg['code']=101;
                }
            }else{
                $msg['code']=101;
                $msg['data'] = $array['Header']['errmsg'];
            }

        }else{
            $msg['code']=101;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    /**
     * @deprecated  积分添加
     * @传入参数  key_admin、sign、cardno、scoreno、scorecode、why、membername
     */
    public function addintegral(){
        $params = I('param.');
        $this->paramsCheck($params,array('key_admin','cardno','scoreno','why'),array('cardno','scoreno'));
        $params['scoreno'] = (float)(abs($params['scoreno']));
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);
        $rparam = $this->baseParam;
        $rparam['method'] = "BonusChange_WebService";
        $rparam['data']['VipCode'] = $params['cardno'];
        $rparam['data']['Bonus'] = number_format((string)$params['scoreno'],2,".","");
        $rparam['data']['User'] = "zhihuitu";
        $rparam['data']['Action'] = "A";
        $rparam['data']['Remark'] = $params['why'];
        $rparam['data']['ExpDate'] = "20991212";
        $rparam['data']['ReasonCode'] = $this->ReasonCode;
        $rparam = $this->getDazuSign($rparam,$this->rsecret);
        $result = http($this->request_url,$rparam,'POST',$this->rheader);
        if(is_json($result)){
            $array=json_decode($result,true);
            if (0 == $array['Header']['errcode']){
                $store=@I('store');
                $data = array();
                $data['cardno']=$params['cardno'];
                $data['scorenumber']=($params['scoreno']);
                $data['why']=$params['why'];
                $data['scorecode']=@$array['Data']['DocNo'];
                $data['cutadd']=2;
                $data['datetime'] = date('Y-m-d H:i:s',time());
                $admininfo=$this->getMerchant($params['key_admin']);
                $db=M('score_record',$admininfo['pre_table']);
                $data['store']=$store?$store:'';
                $add=$db->add($data);
                if ($add){
                    $msg['code']=200;
                }else{
                    $msg['code']=200;
                    $msg['data']='数据保存错误';
                }
            }else {
                $msg['code']=104;
                $msg['data']=$array['Header']['errmsg'];
            }
        }else{
            $msg['code']=101;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * @deprecated  创建会员
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name   (address\idnumber\birth)
     */
    public function createMember(){
        $params = I('param.');//birth(11039298384),sex,1== $params['sex'] ? 'M' : 'F',
        writeOperationLog($params,'dazucrm');
        $this->paramsCheck($params,array('mobile','openid'),array('mobile','openid'));
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);
        $rparam = $this->baseParam;
        $rparam['method'] = "VipCreate_WebService";
        $rparam['data']['VipCreateData']['SurName'] = !empty($params['name'])?$params['name']:"";
        $rparam['data']['VipCreateData']['Mobile'] = $params['mobile'];
        $rparam['data']['VipCreateData']['MallId'] = $this->MallId;
        $rparam['data']['VipCreateData']['Extend_Vipcode'] ="0500";
        $rparam['data']['VipCreateData']['Grade'] = "05";
        $rparam['data']['VipCreateData']['User'] ="zhihuitu";
        $rparam['data']['VipCreateData']['Sex'] = (1== $params['sex']) ? 'M' : 'F';
        if($params['birth']){
            $rparam['data']['VipCreateData']['Birthday'] = date('Ymd',(int)strtotime($params['birth']));
        }
        $rparam = $this->getDazuSign($rparam,$this->rsecret);
        $memInfo = http($this->request_url,$rparam,'POST',$this->rheader);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            writeOperationLog($array,'dazucrm');
            if(0 == (int)$array['Header']['errcode']){
                $db=M('mem', $admininfo['pre_table']);
                $check = $db->where(array('cardno'=>$array['Data']))->find();
                if(empty($check)) {
                    $d = array();
                    $d['cardno'] = $array['Data'];
                    $d['openid'] = $params['openid'];
                    $d['datetime'] = date('Y-m-d H:i:s');
                    $d['usermember'] = $params['name'];
                    $d['getcarddate'] = date('Y-m-d H:i');
                    //$d['birthday'] = date('Y-m-d',(int)@$params['birth']);
                    $d['phone'] = $params['mobile'];
                    $d['status'] = 1;
                    $d['mobile'] = $params['mobile'];
                    $d['address'] = @$params['address']."-";
                    $add = $db->add($d);
                }
                $msg['code']=200;
                $list=array(
                    'cardno'=>$array['Data'],
                    'usermember'=>$params['name'],
                    'getcarddate'=>date('Y-m-d'),
                    'expirationdate'=>'',
                    'mobile'=>$params['mobile'],
                   // 'sex'=>1
                );
                $msg['data']=$list;
                //发送模板消息
            }else{
                $msg['code'] = 15;
                $msg['data'] = $array['Header']['errmsg'];
            }
        }else{
            $msg['code']=101;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * @deprecated  积分扣除
     * @传入参数  key_admin、sign、cardno、scoreno、why
     */
    public function cutScore(){
        $params = I('param.');
        $this->paramsCheck($params,array('key_admin','cardno','scoreno','why'),array('cardno','scoreno'));
        $params['scoreno'] = (float)(abs($params['scoreno'])*-1);
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);
        $rparam = $this->baseParam;
        $rparam['method'] = "BonusChange_WebService";
        $rparam['data']['VipCode'] = $params['cardno'];
        $rparam['data']['Bonus'] = number_format((string)$params['scoreno'],2,".","");
        $rparam['data']['User'] = "zhihuitu";
        $rparam['data']['Action'] = "A";
        $rparam['data']['Remark'] = $params['why'];
        $rparam['data']['ExpDate'] = "20991212";
        $rparam['data']['ReasonCode'] = $this->ReasonCode;
        $rparam = $this->getDazuSign($rparam,$this->rsecret);
        $result = http($this->request_url,$rparam,'POST',$this->rheader);
        if(is_json($result)){
            $array=json_decode($result,true);
            if (0 == $array['Header']['errcode']){
                $store=@I('store');
                $data = array();
                $data['cardno']=$params['cardno'];
                $data['scorenumber']=($params['scoreno']);
                $data['why']=$params['why'];
                $data['scorecode']=@$array['Data']['DocNo'];
                $data['cutadd']=2;
                $data['datetime'] = date('Y-m-d H:i:s',time());
                $admininfo=$this->getMerchant($params['key_admin']);
                $db=M('score_record',$admininfo['pre_table']);
                $data['store']=$store?$store:'';
                $add=$db->add($data);
                if ($add){
                    $msg['code']=200;
                }else{
                    $msg['code']=200;
                    $msg['data']='数据保存错误';
                }
            }else {
                $msg['code']=104;
                $msg['data']=$array['Header']['errmsg'];
            }
        }else{
            $msg['code']=101;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * @deprecated  修改会员信息
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name、cardno
     */
    public function editMember(){
        $params = I('param.');
        writeOperationLog($params,'dazucrm');
        $this->paramsCheck($params,array('key_admin','openid'),array('openid','cardno'));
        $rparam = $this->baseParam;
        $rparam['method'] = "VipUpdate_WebService";
        $rparam['data']['VipUpdateData']['SurName'] = !empty($params['name'])?$params['name']:"";
        $rparam['data']['VipUpdateData']['MallId'] = $this->MallId;
        $rparam['data']['VipUpdateData']['VipCode'] = $params['cardno'];
        $rparam['data']['VipUpdateData']['User'] ="zhihuitu";
        $rparam['data']['VipUpdateData']['Sex'] = (1== $params['sex']) ? 'M' : 'F';
        $rparam['data']['VipUpdateData']['Birthday'] = date('Ymd',strtotime($params['birth']));
        $rparam = $this->getDazuSign($rparam,$this->rsecret);
        $memInfo = http($this->request_url,$rparam,'POST',$this->rheader);
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);
        //查找会员标识；
        $memCheck = $db->where(array('cardno'=>$params['cardno']))->find();
        if(!$memCheck){
            returnjson(array('code'=>104,'data'=>"根据卡号未找到会员信息"),$this->returnstyle,$this->callback);
        }
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            writeOperationLog($array,'dazucrm');
            if(0 == $array['Header']['errcode']){
                $d = array();
                $d['openid'] = $params['openid'];
                $d['usermember'] = $params['name'];
                $d['idnumber'] = @$params['idnumber'];
                $d['birthday'] = date('Y-m-d',strtotime($params['birth']));
                $d['address'] = @$params['address'];
                $d['sex'] = @$params['sex'];
                $sv = $db->where(array('cardno'=>$params['cardno']))->save($d);
                $msg['code']=200;
                $datas = array();
                $datas['cardno']=$params['cardno'];
                $datas['usermember']=$params['name'];
                $datas['getcarddate']='';//创建时间
                $datas['ismarry'] = $params['ismarry'];
                $datas['expirationdate']='';//到期时间
                $datas['sex']=@$params['sex'];
                $datas['idnumber']=@$params['idnumber'];
                $msg['data']=$datas;
                //发送模板消息
            }else{
                $msg['code'] = 3000;
                $msg['data'] = $array['Header']['errmsg'];
            }
        }else{
            $msg['code']=101;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * @deprecated 根据手机号获取会员信息
     * @传入参数  key_admin、sign、mobile
     */
    public function GetUserinfoByMobile(){
        $params = I('param.');
        $this->paramsCheck($params,array('key_admin','mobile'),array('mobile'));
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);
        $rparam = $this->baseParam;
        $rparam['method'] = "GetVipDis";
        $rparam['data']['Mobile'] = $params['mobile'];
        $rparam = $this->getDazuSign($rparam,$this->rsecret);
        $memInfo = http($this->request_url,$rparam,'POST',$this->rheader);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            if(0 == (int)$array['Header']['errcode']){
                if(!empty($array['Data'][0])){
                    $firstRd = $array['Data'][0];
                    //查询卡号和积分
                    $rt = array();
                    $rt['cardno']=$firstRd['VipCode'];
                    $rt['nickname'] = $firstRd['SurName'];
                    $rt['idnumber']="";
                    $rt['status']=$firstRd['Active'];
                    $rt['status_description']='';
                    $rt['expirationdate']=$firstRd['ExpiryDate'];//到期时间
                    $rt['birthday']=@$firstRd['BirthdayYYYY']."-".@$firstRd['BirthdayMM']."-".@$firstRd['BirthdayDD'];
                    $rt['company']='';
                    $rt['score_num'] = @$firstRd['Bonus'];
                    $rt['phone']=@$firstRd['Mobile'];
                    $rt['mobile']=@$firstRd['Mobile'];
                    $rt['address']=@$firstRd['Address1'].".";
                    $rt['level']=@$firstRd['Grade'];
                    $rt['remark']=@$firstRd['VipCardNo'];
                    $rt['sex']= 'M'==@$firstRd['Sex'] ? 1 : 0;
                    $sel=$db->where(array('cardno'=>$rt['cardno']))->find();
                    if (null == $sel){
                        $db->add($rt);
                    }else{
                        $db->where(array('cardno'=>$rt['cardno']))->save($rt);
                    }
                    $datas['cardno']=$rt['cardno'];
                    $datas['xf_vipcardno']=@$firstRd['VipCardNo'];
                    $datas['cardtype']=@$rt['level'];
                    $datas['name']=$rt['nickname'];
                    $datas['status']=$rt['status'];
                    $datas['status_description']='';
                    $datas['getcarddate']="";//创建时间
                    $datas['expirationdate']=$rt['expirationdate'];//到期时间
                    $datas['birth']=$rt['birthday'];
                    $datas['company']='';
                    $datas['phone']=$rt['phone'];
                    $datas['idnumber']="";
                    $datas['mobile']=$rt['mobile'];
                    $datas['address']=$rt['address'];
                    $datas['sex']=$rt['sex'];
                    $datas['score']=$rt['score_num'];
                    $msg['code']=200;
                    $msg['data']=$datas;
                }else{
                    $msg['code']=101;
                }
            }else{
                $msg['code']=101;
                $msg['data'] = $array['Header']['errmsg'];
            }

        }else{
            $msg['code']=101;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * @deprecated 用户积分详细列表
     */
    public function scorelist(){
        $params = I('param.');
        $this->paramsCheck($params,array('key_admin','cardno','startdate','enddate'),array('cardno'));
        $page = !empty($params['page'])?abs((int)$params['page']):1;
        $offset = !empty($params['lines'])?$params['lines']:20;
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);
        $rparam = $this->baseParam;
        $rparam['method'] = "SelectBonusLedger_WebService";
        $rparam['data']['VipCode'] = $params['cardno'];
        $rparam['data']['PageNo'] = $page;
        $rparam['data']['PageSize'] = $offset;
        $rparam['data']['StartTxDate'] = date('Ymd',strtotime("-1 year"));
        $rparam['data']['EndTxDate'] = date('Ymd');
        $rparam = $this->getDazuSign($rparam,$this->rsecret);
        $memInfo = http($this->request_url,$rparam,'POST',$this->rheader);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            if(0 == (int)$array['Header']['errcode']){
                $scorelist = array();
                if(!empty($array['Data'])) {
                    foreach ($array['Data'] as $k => $v) {
                        $scorelist[] = array(
                            "date" => date("Y-m-d H:i:s", strtotime($v['CreateDateTime'])),
                            "description" => $v['Remark'],
                            "score" => $v['Bonus'],
                            "scoreType" => $v['ReasonDesci']
                        );
                    }
                }
                $msg=array("code"=>200,"data"=>array(
                    'cardno'=>$params['cardno'],
                    'scorelist'=>$scorelist,
                ));
            }else{
                $msg=array("code"=>102);
            }
        }else{
            $msg=array("code"=>101);
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * @deprecated 欧亚卖场
     * @传入参数 key_admin、sign 、skt、Jlbh、md
     */
    public function billInfo(){}


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

    protected function getDazuSign($param,$key){
        $param['data'] = json_encode($param['data']);
        $str = $param['method'].$param['data'].$param['crc'].$key;
        $param['sign'] = md5($str);
        return ($param);
    }

    
    /**
     * 解绑
     */
    public function UnBind(){}
}
