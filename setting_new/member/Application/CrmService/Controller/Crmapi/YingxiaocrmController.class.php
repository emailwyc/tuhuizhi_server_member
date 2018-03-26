<?php
namespace CrmService\Controller\Crmapi;

use CrmService\Controller\CrminterfaceController;
use CrmService\Controller\CommonController;

class YingxiaocrmController extends CommonController implements CrminterfaceController
{
    private $strCallUserCode='ABCDEF123456GHIJK2SS11';
    private $strCallPassword='ABCDEF123456GHIJK';
    private $api_url='https://crmapi.joycity.mobi/member';
 /* (non-PHPdoc)
     * @see \CrmService\Controller\CrminterfaceController::addintegral()
     */
    public function addintegral()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $params['scoreno']=abs(I('scoreno'));
        $params['why']=I('why');
        if (in_array('',$params)){//获取的参数不完整
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['scorecode']=I('scorecode');
        $params['membername']=I('membername');
        
        $addintegral_url=$this->api_url.'/BonusAdjustment';
        $pa['strCallUserCode']=$this->strCallUserCode;
        $pa['strCallPassword']=$this->strCallPassword;
        $pa['strMemberCode']=$params['cardno'];
        $pa['strBonusPoint']=$params['scoreno'];
        $pa['strAdjustReason']=$params['why'];
 
        $result=http($addintegral_url, $pa,'POST');
        
        if(empty($result) || !is_string($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }

        $result_array=xmltoarray($result);

        if ($result_array['Return']['Error']['ErrorCode']){
            returnjson(array('code'=>104,'data'=>$result_array['Return']['Error']['Description']),$this->returnstyle,$this->callback);
        }
        
        $data['cardno']=$params['cardno'];
        $data['scorenumber']=$params['scoreno'];
        $data['why']=$params['why'];
        $data['scorecode']=$params['scorecode'];
        $data['datetime']=date('Y-m-d H:i');
        $data['cutadd']=2;
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('score_record',$admininfo['pre_table']);
        $add=$db->add($data);
        
        $msg['code']=200;
        
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }

/* (non-PHPdoc)
     * @see \CrmService\Controller\CrminterfaceController::cutScore()
     */
    public function cutScore()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $params['scoreno']=abs(I('scoreno'));
        $params['why']=I('why');
        if (in_array('',$params)){//获取的参数不完整
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        $cutScore_url=$this->api_url.'/BonusAdjustment';
        $pa['strCallUserCode']=$this->strCallUserCode;
        $pa['strCallPassword']=$this->strCallPassword;
        $pa['strMemberCode']=$params['cardno'];
        $pa['strBonusPoint']='-'.$params['scoreno'];
        $pa['strAdjustReason']=$params['why'];

        $result=http($cutScore_url, $pa,'POST');
        
        if(empty($result) || !is_string($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $result_array=xmltoarray($result);
        
        if ($result_array['Return']['Error']['ErrorCode']){
            returnjson(array('code'=>104,'data'=>$result_array['Return']['Error']['Description']),$this->returnstyle,$this->callback);
        }
        
        $data['cardno']=$params['cardno'];
        $data['scorenumber']=$params['scoreno'];
        $data['why']=$params['why'];
        $data['scorecode']=$params['scorecode'];
        $data['datetime']=date('Y-m-d H:i');
        $data['cutadd']=1;
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('score_record',$admininfo['pre_table']);
        $add=$db->add($data);
        
        $msg['code']=200;
        
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
 /* (non-PHPdoc)
     * @see \CrmService\Controller\CrminterfaceController::createMember()
     */
    public function createMember()
    {
        $params['strTelephone'] = I('mobile');
        $params['key_admin']=I('key_admin');
        
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        $strVipId = I('cardno');
        if($strVipId){
            $params['strVipId'] = $strVipId;
        }
        $openid = I('openid');
        $params['strGivenName'] = I('name');
        $params['strVipEmail']=I('email');
        $params['strCallUserCode']=$this->strCallUserCode;
        $params['strCallPassword']=$this->strCallPassword;
        $params['idnumber'] = I('idnumber');
        $params['strSurname'] = $params['strGivenName'];
        $params['strSex']=I('sex');
        $params['strIssueStore']  = empty($params['issuetype'])?"5":$params['issuetype'];
        
        $params['strSex'] = (0 == $params['strSex']) ? "M" : "F";

        $create_url = $this->api_url.'/CreateMember';
        $result = http($create_url, $params, 'POST');
        
        if(empty($result) || !is_string($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $result_array=xmltoarray($result);
        
        if ($result_array['Return']['Error']['ErrorCode']){
            returnjson(array('code'=>104,'data'=>$result_array['Return']['Error']['Description']),$this->returnstyle,$this->callback);
        }

        $data['cardno']=$result_array['Return']['Success']['VipCardNo'];
        $data['usermember']=$params['strGivenName'];
        $data['idnumber']=$params['idnumber'];
        $data['getcarddate']=date('Y-m-d');
        $data['phone']=$params['strTelephone'];
        $data['mobile']=$params['strTelephone'];
        $data['sex']=$params['strSex'];
        $data['openid'] = $openid;
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem',$admininfo['pre_table']);
        $add=$db->add($data);
        $msg['code']=200;
        $list=array(
            'cardno'=>$data['cardno'],
            'usermember'=>$params['strGivenName'],
            'getcarddate'=>date('Y-m-d'),
            'expirationdate'=>'',
            'mobile'=>$params['strTelephone'],
            'sex'=>$params['strSex'],
            'idnumber'=>$params['idnumber']
        );
        $msg['data']=$list;
        returnjson($msg,$this->returnstyle,$this->callback);
    }


 /* (non-PHPdoc)
     * @see \CrmService\Controller\CrminterfaceController::editMember()
     */
    public function editMember()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['strTelephone']=I('mobile');
        $params['strVipCode']=I('carano');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
            
            $params['strGivenName']=I('name');
            $openid = I('openid');
            $params['strCallUserCode']=$this->strCallUserCode;
            $params['strCallPassword']=$this->strCallPassword;
            $params['strSex']=(0==I('sex')) ? 'M' : 'F';
            $params['strSurname']=$params['strGivenName'];
            $params['strVipEmail']=I('email');
            $params['strDob_YYYYMMDD']='';
            $params['idnumber'] = I('idnumber');
            $save_url=$this->api_url.'/UpdateMemberInfo';
            
            $result=http($save_url, $params,'POST');
            
            if(empty($result) || !is_string($result)) {
                returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
            }
            
            $result_array=xmltoarray($result);
            
            if ($result_array['Return']['Error']['ErrorCode']){
                returnjson(array('code'=>104,'data'=>$result_array['Return']['Error']['Description']),$this->returnstyle,$this->callback);
            }
            
            $admininfo=$this->getMerchant($params['key_admin']);
            $db=M('mem',$admininfo['pre_table']);
            $rt['mobile']=$params['strTelephone'];
            $rt['sex']= $params['strSex'];
            $rt['idnumber']=$params['idnumber'];
            $rt['usermember']=$params['strSurname'];
            $rt['nickname']=$params['strSurname'];
            $rt['email']=$params['strVipEmail'];
            if($openid != ''){
                $rt['openid'] = $openid;
            }
            
            $sel=$db->where(array('cardno'=>$params['strVipCode']))->find();
            if (null == $sel){
                $rt['cardno']=$params['strVipCode'];
                $sv=$db->add($rt);
            }else{
                unset($rt['usermember']);
                $sv=$db->where(array('cardno'=>$params['strVipCode']))->save($rt);
            }
            
            $data['cardno']=$params['strVipCode'];
            $data['usermember']=$params['strSurname'];
            $data['getcarddate']='';
            $data['expirationdate']='';
            $data['mobile']=$params['strTelephone'];
            $data['sex']=('M'==$params['strSex']) ? 0 : 1;
            $data['idnumber']=$params['idnumber'];
            $msg['code']=200;
            $msg['data']=$data;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }

 /* (non-PHPdoc)
     * @see \CrmService\Controller\CrminterfaceController::GetUserinfoByCard()
     */
    public function GetUserinfoByCard()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['strMemberCode']=I('card');
        if (in_array('',$params)){//获取的参数不完整
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $openid = I('openid');
        
        $cardno_url = $this->api_url.'/GetMemberInfoByCardNo';
        $result = http($cardno_url, $params, 'POST');

        if(empty($result) || !is_string($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $result_array=xmltoarray($result);
//                 print_r($result_array);die;
        if ($result_array['Return']['Error']['ErrorCode']){
            returnjson(array('code'=>104,'data'=>$result_array['Return']['Error']['Description']),$this->returnstyle,$this->callback);
        }
        
        $msg = $this->member_data($result_array,$params,$openid);         
        
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    public function member_data($result_array,$params,$openid=''){
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem',$admininfo['pre_table']);
        $result_data = $result_array['MemberInfo']['Member'];
        $rt['cardno']=empty($result_data['VIPCODE'])?"":$result_data['VIPCODE'];
        $rt['usermember']=empty($result_data['SURNAME'])?"":$result_data['SURNAME'];
        $rt['idnumber']=empty($result_data['VIPID'])?"":$result_data['VIPID'];
        $rt['status']=@$result_data['ACTIVE']?$result_data['ACTIVE']:"";
        $rt['status_description']='';
        $rt['getcarddate']=@$result_data['JOINTDATE']?$result_data['JOINTDATE']:"";
        $rt['expirationdate']=@$result_data['EXPIRYDATE']?$result_data['EXPIRYDATE']:"";//到期时间
        $year = @$result_data['BIRTHDAYYYYY']?$result_data['BIRTHDAYYYYY']:'';
        $moth = @$result_data['BIRTHDAYMM']?$result_data['BIRTHDAYMM']:'';
        $day = @$result_data['BIRTHDAYDD']?$result_data['BIRTHDAYDD']:'';
        if($year!='' && $moth!='' && $day!=''){
            $birthday = $year.'-'.$moth.'-'.$day;
        }
        $rt['birthday']=$birthday?$birthday:'';
        
        if($result_data['GRADE']==1){
            $result_data['GRADE'] = '72';
        }
        
        if($result_data['GRADE']==2){
            $result_data['GRADE'] = '01';
        }
        
        if($result_data['GRADE']==3){
            $result_data['GRADE'] = '02';
        }
        $rt['level'] = $result_data['GRADE'];
        $rt['phone']=$result_data['TELEPHONE'];
        $rt['mobile']=$result_data['TELEPHONE'];
        $rt['address']=@$result_data['ADDRESS1']?$result_data['ADDRESS1']:'';
        $rt['sex']=@$result_data['SEX']?$result_data['SEX']:'';
        $rt['score_num'] = @$result_data['CURRBONUSUSED']?$result_data['CURRBONUSUSED']:'';
        if($openid != ''){
            $rt['openid'] = $openid;
        }
        $sel=$db->where(array('cardno'=>$rt['cardno']))->find();
        if (null == $sel){
            $sv=$db->add($rt);
        }else{
            unset($rt['usermember']);
            $sv=$db->where(array('cardno'=>$rt['cardno']))->save($rt);
        }
        $data['cardno']=$rt['cardno'];
        $data['user']=empty($result_data['SURNAME'])?"":$result_data['SURNAME'];
        $data['cardtype']=$rt['level'];
        $data['status']=$rt['status'];
        $data['status_description']='';
        $data['getcarddate']=$rt['getcarddate'];//创建时间
        $data['expirationdate']=$rt['expirationdate'];//到期时间
        $data['birthday']=$birthday?$birthday:'';
        $data['company']='';
        $data['phone']=@$result_data['TELEPHONE'];
        $data['mobile']=@$result_data['TELEPHONE'];
        $data['address']="";
        $data['sex']=('M'==$rt['sex']) ? 0 : 1;
        $data['score']=@$result_data['CURRBONUSUSED']?$result_data['CURRBONUSUSED']:0;//剩余积分
        $msg['code']=200;
        //         print_R($data);die;
        $msg['data']=$data;
        return $msg;
    }
    
 /* (non-PHPdoc)
     * @see \CrmService\Controller\CrminterfaceController::GetUserinfoByMobile()
     */
    public function GetUserinfoByMobile()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['strTelephone']=I('mobile');
        
        if (in_array('',$params)){//获取的参数不完整
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        $openid = I('openid');
        $params['strCallUserCode']=$this->strCallUserCode;
        $params['strCallPassword']=$this->strCallPassword;
        
        $mobile_url = $this->api_url.'/GetMemberInfo';
        $result = http($mobile_url, $params, 'POST');
        
        if(empty($result) || !is_string($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $result_array=xmltoarray($result);
//         print_r($result_array);die;
        if ($result_array['Return']['Error']['ErrorCode']){
            returnjson(array('code'=>104,'data'=>$result_array['Return']['Error']['Description']),$this->returnstyle,$this->callback);
        }
        
        $msg = $this->member_data($result_array,$params,$openid);
        
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
/* (non-PHPdoc)
     * @see \CrmService\Controller\CrminterfaceController::scorelist()
     */
    public function scorelist(){
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['strMemberCode']=I('cardno');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             $params['startdate'] = I('startdate');
//             $params['enddate'] = I('enddate');
//             $params['strEndRow'] = I('lines');
//             $params['strStartRow'] = I('page');
            
            $scorelist_url = $this->api_url.'/GetMemberBonusLedger';
            
            $result = http($scorelist_url, $params, 'POST');
            
            if(empty($result) || !is_string($result)) {
                returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
            }

            $result_array=xmltoarray($result);
//             print_r($result_array);die;
            if ($result_array['Return']['Error']['ErrorCode']){
                returnjson(array('code'=>104,'data'=>$result_array['Return']['Error']['Description']),$this->returnstyle,$this->callback);
            }

            foreach($result_array['MemberSalesList'] as $k=>$v){
                $data['date'] =$v['TXDATE'].' '.$v['TXTIME'];
                $data['description'] = $v['REMARK'];
                $data['score'] = $v['BONUS'];
                $scorelist[] = $data;
            }
            
            $msg['code'] = 200;
            $msg['data'] = array(
                'cardno'=>$params['strMemberCode'],
                'scorelist'=>$scorelist
            );
        }
       returnjson($msg,$this->returnstyle,$this->callback);
    }
   
/* @deprecated 欧亚卖场
     * @传入参数 key_admin、sign 、skt、Jlbh、md
     */
    public function billInfo(){
    
    }

    /**
     *  openid 获取用户信息
     */
    public function GetUserinfoByOpenid(){
        
        $params['openid'] = I('openid');
        $params['key_admin'] = I('key_admin');
        if(in_array('', $params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem',$admininfo['pre_table']);
        $info = $db->where(array('openid'=>$params['openid']))->find();        
        
        if(empty($info)){
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }
        
        $cardno_url = $this->api_url.'/GetMemberInfoByCardNo';
        
        $param['strMemberCode'] = $info['cardno'];
        $result = http($cardno_url, $param, 'POST');
        
        if(empty($result) || !is_string($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $result_array=xmltoarray($result);
//                         print_r($result_array);die;
        if ($result_array['Return']['Error']['ErrorCode']){
            returnjson(array('code'=>104,'data'=>$result_array['Return']['Error']['Description']),$this->returnstyle,$this->callback);
        }
        
        $msg = $this->member_data($result_array,$params);
        
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    public function db_connect(){
        $connection = array(
            'db_type'    =>   'mysql',
            'db_host'    =>   'http://fw.joycity.mobi/jcmdb/',
            'db_user'    =>   'root',
            'db_pwd'     =>   'rtmap911',
            'db_port'    =>    3306,
            'db_name'    =>    'rtmap_market',
        );
        $market_db = M('user','market_',$connection);
        return $connection;
    }
    
    
    
    /**
     * @see \CrmService\Controller\CrminterfaceController::getInfoByOpenid()
     */
    public function getInfoByOpenid(){
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['openid']=I('openid');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else{
            $result = http('http://fw.joycity.mobi/kaapi/Api/Index/getMemberInfoByOpenid', $params, 'POST');
            $result = json_decode($result, true);
    
            if(empty($result['data']) || $result['code'] != 200) {
                $msg['code'] = 1000;
            }else{
                $result_array = $result['data'];
                $admininfo=$this->getMerchant($params['key_admin']);
                $db=M('mem',$admininfo['pre_table']);
                $rt['cardno']=empty($result_array['VipCardNo'])?"":$result_array['VipCardNo'];
                $rt['usermember']=empty($result_array['SurName'])?"":$result_array['SurName'];
                $rt['idnumber']=empty($result_array['ID'])?"":$result_array['ID'];
                $rt['status']=@$result_array['Active'];
                $rt['status_description']='';
                $rt['getcarddate']="";
                $rt['expirationdate']="";//到期时间
                $rt['birthday']=$result_array['BirthDay']?$result_array['BirthDay']:'';
                $rt['level'] = @$result_array['Grade'];
                $rt['company']='';
                $rt['phone']=@$result_array['Mobile'];
                $rt['mobile']=@$result_array['Mobile'];
                $rt['address']="";
                $rt['openid']=$params['openid'];
                $sel=$db->where(array('cardno'=>$rt['cardno']))->find();
                if (null == $sel){
                    $sv=$db->add($rt);
                }else{
                    unset($rt['usermember']);
                    $sv=$db->where(array('cardno'=>$rt['cardno']))->save($rt);
                }
                $data['cardno']=@$result_array['VipCardNo'];
                $data['user']=@$result_array['SurName'];
                $data['cardtype']=@$result_array['Grade'];
                $data['status']=@$result_array['Active'];
                $data['status_description']='';
                $data['getcarddate']="";//创建时间
                $data['expirationdate']="";//到期时间
                $data['birthday']=@$result_array['BirthDay'];
                $data['company']='';
                $data['phone']=@$result_array['Mobile'];
                $data['mobile']=@$result_array['Mobile'];
                $data['address']="";
                $data['score']=@$result_array['CurrentBonus'];//剩余积分
                $msg['code']=200;
                $msg['data']=$data;
            }
        }
    
        echo returnjson($msg,$this->returnstyle,$this->callback);
    
    }
    
    
    
    public function addintegralbyopenid()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['openid']=I('openid');
        $params['score']=abs((int)I('score'));
        $params['why']=I('why');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
            $params1 = array('openid'=>$params['openid']);
            $result = http('http://fw.joycity.mobi/kaapi/Api/Index/getcardnobyopenid',$params1, 'POST');
            $result = json_decode($result, true);
            if(empty($result['data']['user_card'])) {
                $msg['code'] = 104;
                returnjson($msg,$this->returnstyle,$this->callback);exit;
            }
            $params2 = array(
                'key_admin'=> $params['key_admin'],
                'cardno'=>$result['data']['user_card'],
                'score' =>$params['score'],
                'why'   =>$params['why']
            );
            $result2 = http('http://fw.joycity.mobi/kaapi/Api/Index/addintegral',$params2, 'POST');
            $result2 = json_decode($result2, true);
            if($result2['code'] && $result2['code']==200) {
                $data2 = array();
                $data2['cardno']=$result['data']['user_card'];
                $data2['scorenumber']=$params['score'];
                $data2['why']=$params['why'];
                $data2['scorecode']="";
                $data2['datetime']=date('Y-m-d H:i:s',time());
                $data2['cutadd']=2;
                $admininfo=$this->getMerchant($params['key_admin']);
                $db=M('score_record',$admininfo['pre_table']);
                $db->add($data2);
                returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit;
            }else{
                returnjson(array('code'=>14,'msg'=>"赠送失败"),$this->returnstyle,$this->callback);exit;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    //通过userid送积分
    public function addintegralbycard()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $params['score']=abs((int)I('score'));
        $params['why']=I('why');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
            $params2 = array(
                'key_admin'=> $params['key_admin'],
                'cardno'=>$params['cardno'],
                'score' =>$params['score'],
                'why'   =>$params['why']
            );
            $result2 = http('http://fw.joycity.mobi/kaapi/Api/Index/addintegral',$params2, 'POST');
            $result2 = json_decode($result2, true);
            if($result2['code'] && $result2['code']==200) {
                $data2 = array();
                $data2['cardno']=$params['cardno'];
                $data2['scorenumber']=$params['score'];
                $data2['why']=$params['why'];
                $data2['scorecode']="";
                $data2['datetime']=date('Y-m-d H:i:s',time());
                $data2['cutadd']=2;
                $admininfo=$this->getMerchant($params['key_admin']);
                $db=M('score_record',$admininfo['pre_table']);
                $db->add($data2);
                returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit;
            }else{
                returnjson(array('code'=>14,'msg'=>"赠送积分失败!"),$this->returnstyle,$this->callback);exit;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    
    /**
     * 微信扫码积分
     */
    public function WechatScanScore(){
        set_time_limit(0);
        $params['cardno'] = I('cardno');
        $params['orderno'] = I('orderno');
        $params['key_admin'] = I('key_admin');
    
        if(in_array('', $params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
    
        $admininfo=$this->getMerchant($params['key_admin']);
        $data['cardNo'] = html_entity_decode($params['cardno']);
        $data['orderNo'] = $params['orderno'];
    
        $url = 'https://crmapi.joycity.mobi/api/v1/trade/tradeScore';
        $request = 'POST';
        $sign_data['secret'] = 'c7aefbe1c14e471e970eef7d66a13607';//签名 secret
        $header['key'] = '5b253ed5cf8445aca98dd6a42ff6fd2c';//与签名 secret 匹配 的 key ,如果 secret 有变动,找营销平台要新的key
        $sign_data['request'] = $request;
        $sign_data['url'] = '/api/v1/trade/tradeScore';//请求URL地址.
        $header['timestamp'] = time();
        $header['sign'] = strtoupper(md5($request.$sign_data['url'].$header['timestamp'].$sign_data['secret']));
        $headers = array(
            'key: '.$header['key'],
            'timestamp: '.$header['timestamp'],
            'sign: '.$header['sign']
        );
        $return_msg = http($url,$data,$request,$headers);
    
        if(!is_json($return_msg)){
            returnjson(array('code'=>104),$this->returnstyle,$this->callback);
        }
    
        $return_data = json_decode($return_msg,true);
    
        if($return_data['code']!=0){
            returnjson(array('code'=>104,'data'=>$return_data['msg']),$this->returnstyle,$this->callback);
        }
    
        $msg_data['cardno'] = $return_data['data']['cardNo'];
        $msg_data['mobile'] = $return_data['data']['mobile'];
        $msg_data['username'] = $return_data['data']['name'];
        $msg_data['mobile'] = $return_data['data']['mobile'];
        $msg_data['level'] = $return_data['data']['grade'];
        $msg_data['score'] = $return_data['data']['score'];
        $msg_data['amount'] = $return_data['data']['amount'];//订单金额
        $msg_data['orderno'] = $return_data['data']['orderNo'];//订单号
        $msg_data['tradeno'] = $return_data['data']['tradeNo'];//流水号
    
        returnjson(array('code'=>200,'data'=>$msg_data),$this->returnstyle,$this->callback);
    }
    
    
    /**
     * 积分支付
     */
    public function offset_score(){
        $params['cardNo'] = I('cardno');
        if(in_array('', $params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        $url = 'https://crmapi.joycity.mobi/api/v1/score/generateQRCode';
        $request = 'POST';
        $sign_data['secret'] = 'c7aefbe1c14e471e970eef7d66a13607';//签名 secret
        $header['key'] = '5b253ed5cf8445aca98dd6a42ff6fd2c';//与签名 secret 匹配 的 key ,如果 secret 有变动,找营销平台要新的key
        $sign_data['request'] = $request;
        $sign_data['url'] = '/api/v1/score/generateQRCode';//请求URL地址.
        $header['timestamp'] = time();
        $header['sign'] = strtoupper(md5($request.$sign_data['url'].$header['timestamp'].$sign_data['secret']));
        $headers = array(
            'key: '.$header['key'],
            'timestamp: '.$header['timestamp'],
            'sign: '.$header['sign']
        );
        $return_msg = http($url,$params,$request,$headers);
        
        if(!is_json($return_msg)){
            returnjson(array('code'=>104),$this->returnstyle,$this->callback);
        }
        
        $return_data = json_decode($return_msg,true);
        
        if($return_data['code']!=0){
            returnjson(array('code'=>104,'data'=>$return_data['msg']),$this->returnstyle,$this->callback);
        }
//         $level = array(
//             '72'=>'预享卡',
//             '01'=>'缤纷卡',
//             '02'=>'璀璨卡',
//         );
        
        $data['qrcode'] = $return_data['data']['qrCode'];
        $data['name'] = $return_data['data']['name'];
        $data['mobile'] = $return_data['data']['mobile'];
        $data['cardno'] = $return_data['data']['cardNo'];
        $data['sex'] = $return_data['data']['grade'];
        $data['score'] = $return_data['data']['usableScore'];
        $data['money'] = $return_data['data']['usableScoreWorth'];
        
        if($return_data['data']['cardName'] == '预享卡'){
            $level = '72';
        }
        if($return_data['data']['cardName'] == '缤纷卡'){
            $level = '01';
        }
        if($return_data['data']['cardName'] == '璀璨卡'){
            $level = '02';
        }
        $data['level'] = $level;
        returnjson(array('code'=>200,'data'=>$data),$this->returnstyle,$this->callback);
    }
    
    
//     public function data_all(){
//         set_time_limit(0);
//         $params['key_admin'] = I('key_admin');
        
//         $admininfo=$this->getMerchant($params['key_admin']);
//         $db = M('mem',$admininfo['pre_table']);
        
//         $url = 'http://fw.joycity.mobi/kaapi/Api/Index/data_all';

//         $param['lines'] = 2000;
        
//         for($j=1;$j<=47;$j++){
//             $param['page'] = $j;
//             $data = http($url,$param,'POST');
            
//             $data_all = json_decode($data,true);
            
//             $i=1;
//             foreach($data_all['data']  as $k=>$v){
                
//                 $arr['cardno'] = $v['user_card'];
//                 $arr['idnumber'] = $v['user_idcard'];
//                 $arr['usermember'] = $v['user_name'];
//                 $arr['openid'] = $v['user_openid'];
//                 $arr['mobile'] = $v['user_mobile'];
//                 $arr['phone'] = $v['user_mobile'];
                
//                 $info = $db->where(array('cardno'=>$v['user_card']))->find();
//                 if(empty($info)){
                    
//                     $res = $db->add($arr);
                    
//                     if($res){
//                         $i++;
//                     }else{
//                         $data[]=$v['user_card'];
//                     }
//                 }
//             }
//             echo $i;
//             print_r($data);
            
//             sleep(5);
//         }
//     }
    
    
    public function delKey(){
        $key = I("key");
        $this->redis->del($key);
    }
    
    
    /**
     * 解绑
     */
    public function UnBind(){}
}

?>
