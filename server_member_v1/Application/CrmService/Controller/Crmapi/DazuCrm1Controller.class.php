<?php
namespace CrmService\Controller\Crmapi;

use CrmService\Controller\CommonController;
use CrmService\Controller\CrminterfaceController;

class DazuCrm1Controller extends CommonController implements CrminterfaceController
{
    protected $request_url = 'http://36.110.53.100:40085/fzapi.ashx';//测试地址
    protected $rappid  = "BJDZ_WX";
    protected $rsecret = "fz7msj3cin";
    protected $rheader  = array('Content-Type:application/json; charset=utf-8');
    protected $issue_store = "BJ-DZGC";
    protected $baseParam;
    protected $curtime;

    public function _initialize()
    {
        parent::_initialize();
        $this->curtime = time();
        $this->baseParam = array(
            'app_id'   => $this->rappid,
            //'format'   => "JSON",
            'charset'  => "utf-8",
            'sign_type'=> "MD5",
            'timestamp'=> date("Y-m-d H:i:s",$this->curtime),
            'version'  => "1.0.0",
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

        $rparams = $this->baseParam;
        $rparams['method'] = "fzapi.crm.member_info_get";
        $rparams['content'] = json_encode(array(
            "member_code" => $params['card'],
        ));
        $rparams['sign'] = $this->getDazuSign($rparams,$this->rsecret);
        $memInfo = http($this->request_url,$rparams,'POST',$this->rheader);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            if(1000 == (int)$array['code']){
                if(!empty($array['data'])){
                    $firstRd = $array['data'];
                    //查询卡号和积分
                    $rt = array();
                    $rt['cardno']=$firstRd['member_code'];
                    $rt['nickname'] = $firstRd['member_name_chn'];
                    $rt['idnumber']="";
                    $rt['status']="";
                    $rt['status_description']='';
                    $rt['expirationdate']="";//到期时间
                    $rt['birthday']="";
                    $rt['company']='';
                    $rt['score_num'] = @$firstRd['member_bonus'];
                    $rt['phone']=@$firstRd['telephone'];
                    $rt['mobile']=@$firstRd['telephone'];
                    $rt['address']=@$firstRd['address'].".";
                    $rt['level']=@$firstRd['grade'];
                    $rt['remark']=@$firstRd['account_no'];
                    $rt['sex']= 'M'==@$firstRd['sex'] ? 1 : 0;
                    $sel=$db->where(array('cardno'=>$rt['cardno']))->find();
                    if (null == $sel){
                        $db->add($rt);
                    }else{
                        $db->where(array('cardno'=>$rt['cardno']))->save($rt);
                    }
                    $datas['cardno']=$rt['cardno'];
                    $datas['xf_vipcardno']=$firstRd['member_code'];
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
                $msg['data'] = $array['msg'];
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
        $store=@I('store');
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);

        $rparams = $this->baseParam;
        $rparams['method'] = "fzapi.crm.member_bonus_sync";
        $rparams['content'] = json_encode(array(
            "member_code" => $params['cardno'],
            "bonus_date" => date("Y-m-d",$this->curtime),
            "bonus_time" => date("H:i:s",$this->curtime),
            "bonus_type" => "03",
            "bonus_description" => $params['why'],
            "bonus_point" =>(int)$params['scoreno'],
            "store_code" => $this->issue_store,
           // "store_name_sc" => $store,
        ));
        $rparams['sign'] = $this->getDazuSign($rparams,$this->rsecret);
        $result = http($this->request_url,$rparams,'POST',$this->rheader);
        if(is_json($result)){
            $array=json_decode($result,true);
            if (1000 == $array['code']){
                $data = array();
                $data['cardno']=$params['cardno'];
                $data['scorenumber']=($params['scoreno']);
                $data['why']=$params['why'];
                $data['scorecode']=@$array['data']['doc_no'];
                $data['cutadd']=2;
                $data['datetime'] = date('Y-m-d H:i:s',$this->curtime);
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
                $msg['data']=$array['msg'];
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
        $params = I('param.');
        $this->paramsCheck($params,array('mobile','openid'),array('mobile','openid'));
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);

        $vip_name = !empty($params['name'])?$params['name']:"";
        $vip_sex = (1== $params['sex']) ? 'M' : 'F';

        $rparams = $this->baseParam;
        $rparams['method'] = "fzapi.crm.create_member";
        $rparams['content'] = json_encode(array(
            "issue_store" => $this->issue_store,
            "member_name_chn" =>$vip_name,
            "telephone" => $params['mobile'],
            'sex' => $vip_sex,
        ));
        $rparams['sign'] = $this->getDazuSign($rparams,$this->rsecret);
        $memInfo = http($this->request_url,$rparams,'POST',$this->rheader);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            if(1000 == (int)$array['code']){
                $db=M('mem', $admininfo['pre_table']);
                $check = $db->where(array('cardno'=>$array['data']['member_code']))->find();
                if(empty($check)) {
                    $d = array();
                    $d['cardno'] = $array['data']['member_code'];
                    $d['openid'] = $params['openid'];
                    $d['datetime'] = date('Y-m-d H:i:s');
                    $d['usermember'] = $vip_name;
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
                    'cardno'=>$array['data']['member_code'],
                    'usermember'=>$vip_name,
                    'getcarddate'=>date('Y-m-d'),
                    'expirationdate'=>'',
                    'mobile'=>$params['mobile'],
                    // 'sex'=>1
                );
                $msg['data']=$list;
                //发送模板消息
            }else{
                $msg['code'] = 15;
                $msg['data'] = $array['msg'];
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
        $store=@I('store');
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);

        $rparams = $this->baseParam;
        $rparams['method'] = "fzapi.crm.member_bonus_sync";
        $rparams['content'] = json_encode(array(
            "member_code" => $params['cardno'],
            "bonus_date" => date("Y-m-d",$this->curtime),
            "bonus_time" => date("H:i:s",$this->curtime),
            "bonus_type" => "03",
            "bonus_description" => $params['why'],
            "bonus_point" => (int)$params['scoreno'],
            "store_code" => $this->issue_store,
            // "store_name_sc" => $store,
        ));
        $rparams['sign'] = $this->getDazuSign($rparams,$this->rsecret);
        $result = http($this->request_url,$rparams,'POST',$this->rheader);
        if(is_json($result)){
            $array=json_decode($result,true);
            if (1000 == $array['code']){
                $data = array();
                $data['cardno']=$params['cardno'];
                $data['scorenumber']=($params['scoreno']);
                $data['why']=$params['why'];
                $data['scorecode']=@$array['data']['doc_no'];
                $data['cutadd']=2;
                $data['datetime'] = date('Y-m-d H:i:s',$this->curtime);
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
                $msg['data']=$array['msg'];
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
        $this->paramsCheck($params,array('key_admin','openid'),array('openid','cardno'));
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);
        //查找会员标识；
        $memCheck = $db->where(array('cardno'=>$params['cardno']))->find();
        if(!$memCheck){
            returnjson(array('code'=>104,'data'=>"根据卡号未找到会员信息"),$this->returnstyle,$this->callback);
        }

        $vip_name = !empty($params['name'])?$params['name']:"";
        $vip_sex = (1== $params['sex']) ? 'M' : 'F';
        $vip_birth = date('Y-m-d',strtotime($params['birth']));
        $rparams = $this->baseParam;
        $rparams['method'] = "fzapi.crm.update_member_info";
        $rparams['content'] = json_encode(array(
            "issue_store" => $this->issue_store,
            "member_name_chn" => $vip_name,
            "sex" => $vip_sex,
            "address" => @$params['address'],
            "telephone" => $memCheck['mobile'],
            "email" => @$params['email'],
            "member_code" => $params['cardno'],
            "certificate_type" =>"1",
            "id_no"  =>@$params['idnumber'],
            // "store_name_sc" => $store,
        ));
        $rparams['sign'] = $this->getDazuSign($rparams,$this->rsecret);
        $memInfo = http($this->request_url,$rparams,'POST',$this->rheader);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            if(1000 == $array['code']){
                $d = array();
                $d['openid'] = $params['openid'];
                $d['usermember'] = $vip_name;
                $d['idnumber'] = @$params['idnumber'];
                $d['birthday'] = date('Y-m-d',strtotime($params['birth']));
                $d['address'] = @$params['address'];
                $d['sex'] = @$params['sex'];
                $sv = $db->where(array('cardno'=>$params['cardno']))->save($d);
                $msg['code']=200;
                $datas = array();
                $datas['cardno']=$params['cardno'];
                $datas['usermember']=$vip_name;
                $datas['getcarddate']='';//创建时间
                $datas['ismarry'] = $params['ismarry'];
                $datas['expirationdate']='';//到期时间
                $datas['sex']=@$params['sex'];
                $datas['idnumber']=@$params['idnumber'];
                $msg['data']=$datas;
                //发送模板消息
            }else{
                $msg['code'] = 3000;
                $msg['data'] = $array['msg'];
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

        $rparams = $this->baseParam;
        $rparams['method'] = "fzapi.crm.member_info_get";
        $rparams['content'] = json_encode(array(
            "telephone" => $params['mobile'],
        ));
        $rparams['sign'] = $this->getDazuSign($rparams,$this->rsecret);
        $memInfo = http($this->request_url,$rparams,'POST',$this->rheader);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            if(1000 == (int)$array['code']){
                if(!empty($array['data'])){
                    $firstRd = $array['data'];
                    //查询卡号和积分
                    $rt = array();
                    $rt['cardno']=$firstRd['member_code'];
                    $rt['nickname'] = $firstRd['member_name_chn'];
                    $rt['idnumber']="";
                    $rt['status']="";
                    $rt['status_description']='';
                    $rt['expirationdate']="";//到期时间
                    $rt['birthday']=@$firstRd['dob'];
                    $rt['company']='';
                    $rt['score_num'] = @$firstRd['member_bonus'];
                    $rt['phone']=@$firstRd['telephone'];
                    $rt['mobile']=@$firstRd['telephone'];
                    $rt['address']=@$firstRd['address'].".";
                    $rt['level']=@$firstRd['grade'];
                    $rt['remark']=@$firstRd['account_no'];
                    $rt['sex']= 'M'==@$firstRd['sex'] ? 1 : 0;
                    $sel=$db->where(array('cardno'=>$rt['cardno']))->find();
                    if (null == $sel){
                        $db->add($rt);
                    }else{
                        $db->where(array('cardno'=>$rt['cardno']))->save($rt);
                    }
                    $datas['cardno']=$rt['cardno'];
                    $datas['xf_vipcardno']=@$firstRd['member_code'];
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
                $msg['data'] = $array['msg'];
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
        $rparams = $this->baseParam;
        $rparams['method'] = "fzapi.crm.member_getbonusinfo";
        $rparams['content'] = json_encode(array(
            "member_code" => $params['cardno'],
            "start_date" => date("Y-m-d",$params['startdate']),
            "end_date" => date("Y-m-d",$params['enddate']),
        ));
        $rparams['sign'] = $this->getDazuSign($rparams,$this->rsecret);
        $memInfo = http($this->request_url,$rparams,'POST',$this->rheader);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            if(1000 == (int)$array['code']){
                $scorelist = array();
                if(!empty($array['data'])) {
                    foreach ($array['data'] as $k => $v) {
                        $scorelist[] = array(
                            "date" => $v['bonus_date'],
                            "description" => $v['bonus_description'],
                            "score" => $v['bonus_point'],
                            "scoreType" => $v['doc_no']
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
        //$param['content'] = str_replace("/","",$param['content']);
        $param['apiSecret'] = $key;
        ksort($param);
        $str = "";
        foreach ($param as $k => $v) {
            if ('' == $str) {
                $str .= $k . '=' . trim($v);
            } else {
                $str .= '&' . $k . '=' . trim($v);
            }
        }
        return strtolower(md5($str));
    }


    /**
     * 解绑
     */
    public function UnBind(){}
}
