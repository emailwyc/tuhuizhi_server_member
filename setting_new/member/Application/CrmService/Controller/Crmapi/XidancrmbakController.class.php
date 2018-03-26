<?php
namespace CrmService\Controller\Crmapi;

use CrmService\Controller\CrminterfaceController;
use CrmService\Controller\CommonController;

class XidancrmController extends CommonController implements CrminterfaceController
{
    private $strCallUserCode='BE';
    private $strCallPassword='123';
    private $api_url='http://joycitycrmws.cofco.com:8081';
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
        $params['scorecode']=I('scorecode');
        $params['membername']=I('membername');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{//执行业务
                $url=$this->api_url.'/ws_member.asmx/BonusAdjustment';
                $pa['strCallUserCode']=$this->strCallUserCode;
                $pa['strCallPassword']=$this->strCallPassword;
                $pa['strMemberCode']=$params['cardno'];
                $pa['strBonusPoint']=$params['scoreno'];
                $pa['strAdjustReason']=$params['why'];
        
                $header=array('Content-Type:application/x-www-form-urlencoded',true);
                $result=http($url, $pa,'POST',$header);
                if(empty($result) || !is_string($result)) {
                    $msg['code']=1000;
                }else{
                    $result_array=xmltoarray($result);
                    if (isset($result_array['Error']['ErrorCode'])){
                        if ('-15'==$result_array['Error']['ErrorCode']){
                            $msg['code']=319;
                        }elseif ('-14'==$result_array['Error']['ErrorCode']){
                            $msg['code']=1015;
                        }elseif ('-13'==$result_array['Error']['ErrorCode']){
                            $msg['code']=1015;
                        }
                    }else{
                        $data['cardno']=$params['cardno'];
                        $data['scorenumber']=$params['scoreno'];
                        $data['why']=$params['why'];
                        $data['scorecode']=$params['scorecode'];
                        $data['cutadd']=2;
                        $admininfo=$this->getMerchant($params['key_admin']);
                        $db=M('score_record',$admininfo['pre_table']);
                        $add=$db->add($data);
                        if ($add){
                            $msg['code']=200;
                        }else{
                            $msg['code']=200;
                            $msg['data']='数据保存错误';
                        }
                    }
                }
//             }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }

 /* (non-PHPdoc)
     * @see \CrmService\Controller\CrminterfaceController::createMember()
     */
    public function createMember()
    {
        $params['user_mobile'] = I('mobile');
        $params['user_name'] = I('name');
        $params['user_idcard'] = I('idnumber');

        $params['key_admin']=I('key_admin');

        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['birth'] = I('birth');
        $params['user_sex'] = I('sex');

        $result = http('http://fw.joycity.mobi/kaapi/Api/Index/createmember', $params, 'POST');
        $result = json_decode($result, true);
        if(false != $result && $result['code'] == 200){
            $data['cardno']=$result['data']['cardno'];
            $data['datetime']=date('Y-m-d H:i:s');
            $data['usermember']=$params['user_name'];
            $data['idnumber']=$params['user_idcard'];
            $data['getcarddate']=date('Y-m-d');
            $data['phone']=$params['user_mobile'];
            $data['mobile']=$params['user_mobile'];
            $data['sex']=$params['user_sex'];
            $admininfo=$this->getMerchant($params['key_admin']);
            $db=M('mem',$admininfo['pre_table']);
            $add=$db->add($data);
            if ($add){
                $msg['code']=200;
                $list=array(
                    'cardno'=>$data['cardno'],
                    'usermember'=>$params['user_name'],
                    'getcarddate'=>date('Y-m-d'),
                    'expirationdate'=>'',
                    'mobile'=>$params['user_sex'],
                    'sex'=>$params['sex'],
                    'idnumber'=>$params['user_idcard']
                );
                $msg['data']=$list;
            }else{
                $msg['code']=200;
                $msg['data']='数据保存错误';
            }
        }else{
            $msg['code']=104;
            $msg['data']=$result['data'];
        }
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
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{//执行业务
                $url=$this->api_url.'/ws_member.asmx/BonusAdjustment';
                $pa['strCallUserCode']=$this->strCallUserCode;
                $pa['strCallPassword']=$this->strCallPassword;
                $pa['strMemberCode']=$params['cardno'];
                $pa['strBonusPoint']=-$params['scoreno'];
                $pa['strAdjustReason']=$params['why'];
                if ($params['why'] == '停车支付') {
                    $pa['strMallId'] = 000006;
                    $pa['strGiftCode'] = 0047;
                    $pa['strRedeemQty'] = 1;
                }

                $header=array('Content-Type:application/x-www-form-urlencoded',true);
                $result=http($url, $pa,'POST',$header);
                if(empty($result) || !is_string($result)) {
                    $msg['code']=1000;
                }else{
                    $result_array=xmltoarray($result);
                    if (isset($result_array['Error']['ErrorCode'])){
                        if ('-15'==$result_array['Error']['ErrorCode']){
                            $msg['code']=319;
                        }elseif ('-14'==$result_array['Error']['ErrorCode']){
                            $msg['code']=1015;
                        }elseif ('-13'==$result_array['Error']['ErrorCode']){
                            $msg['code']=1015;
                        }
                    }else{
                        $data['cardno']=$params['cardno'];
                        $data['scorenumber']=$params['scoreno'];
                        $data['why']=$params['why'];
                        $data['scorecode']=$params['scorecode'];
                        $data['cutadd']=1;
                        $admininfo=$this->getMerchant($params['key_admin']);
                        $db=M('score_record',$admininfo['pre_table']);
                        $add=$db->add($data);
                        if ($add){
                            $msg['code']=200;
                            $msg['data']=array('cardno'=>$params['cardno'],'scorenumber'=>$params['scoreno'],'why'=>$params['why'],'scorecode'=>$params['scorecode']);
                        }else{
                            $msg['code']=200;
                            $msg['data']=array('cardno'=>$params['cardno'],'scorenumber'=>$params['scoreno'],'why'=>$params['why'],'scorecode'=>$params['scorecode']);
                        }
                    }
                }
//             }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }

 /* (non-PHPdoc)
     * @see \CrmService\Controller\CrminterfaceController::editMember()
     */
    public function editMember()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['mobile']=I('mobile');
        $params['sex']=0==I('sex') ? 'M' : 'F';
        $params['idnumber']=I('idnumber');
        $params['name']=I('name');
        $params['carano']=I('carano');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{//执行业务
                $params['email']=I('email');
                $userinfo=$this->GetUserinfoByCardself($params['carano']);//先获取会员信息，修改会员信息时，保证只修改传入的信息，对不传入的信息原样返回
                if (200 === $userinfo['code']){
                    $url=$this->api_url.'/ws_member.asmx/UpdateMemberInfo';
                    $pa['strCallUserCode']=$this->strCallUserCode;
                    $pa['strCallPassword']=$this->strCallPassword;
                    $pa['strVipCode']=$params['carano'];
                    $pa['strTelephone']=$params['mobile'];
                    $pa['strGivenName']=strval($userinfo['data']['GIVENNAME']);
                    $pa['strSurname']=$params['name'];
                    $pa['strVipId']='171262199906266666';//$userinfo['data']['VIPID'];
                    $pa['strSex']=$params['sex'];
                    $pa['strVipEmail']=$params['email'];
                    $pa['strDOB_YYYYMMDD']='';
                    $pa['strMaritalStatus']=implode('',$userinfo['data']['MARITALSTATUS']);
                    $pa['strZone']=$userinfo['data']['ZONE'];
                    $pa['strAddress1']=$userinfo['data']['ADDRESS1'];
                    $pa['strGroup0']=$userinfo['data']['GROUPID0'];
                    $pa['strGroup1']=$userinfo['data']['GROUPID1'];
                    $pa['strGroup2']=$userinfo['data']['GROUPID2'];
                    $pa['strLicensePlate']=implode('',$userinfo['data']['LICENSEPLATE']);
                    $header=array('Content-Type:application/x-www-form-urlencoded',true);
                    $result=http($url, $pa,'POST',$header);
                    if(empty($result) || !is_string($result)) {
                        $msg['code']=1000;
                    }else{
                        $result_array=xmltoarray($result);
                        if (array_key_exists('Error',$result_array)){
                            $msg['code']=104;
                        }else{
                            $admininfo=$this->getMerchant($params['key_admin']);
                            $db=M('mem',$admininfo['pre_table']);
                            $rt['mobile']=$params['mobile'];
                            $rt['sex']= 'M'==$params['sex'] ? 0 : 1;
                            $rt['idnumber']=$params['idnumber'];
                            $rt['usermember']=$params['name'];
                            $rt['nickname']=$params['name'];
                            $rt['email']=$params['email'];
                            $sel=$db->where(array('cardno'=>$params['carano']))->find();
                            if (null == $sel){
                                $rt['cardno']=$params['carano'];
                                $sv=$db->add($rt);
                            }else{
                                unset($rt['usermember']);
                                $sv=$db->where(array('cardno'=>$params['cardno']))->save($rt);
                            }
                            
                            $data['cardno']=$params['carano'];
                            $data['usermember']=$params['name'];
                            $data['getcarddate']=$userinfo['data']['JOINTDATE'];//创建时间
                            $data['expirationdate']=$userinfo['data']['EXPIRYDATE'];//到期时间
                            $data['mobile']=$params['mobile'];
                            $data['sex']='M'==$params['sex'] ? 0 : 1;
                            $data['idnumber']=$userinfo['data']['VIPID'];
                            $msg['code']=200;
                            $msg['data']=$data;
                        }
                    }
                }else{
                    $msg['code']=$userinfo['code'];
                }
//             }
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
        $params['card']=I('card');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{//执行业务
                $url=$this->api_url.'/ws_member.asmx/GetMemberInfoByCardNo';
                $pa['strCallUserCode']=$this->strCallUserCode;
                $pa['strCallPassword']=$this->strCallPassword;
                $pa['strMemberCode']=$params['card'];
        
                $header=array('Content-Type:application/x-www-form-urlencoded',true);
                $result=http($url, $pa,'POST',$header);
                if(empty($result) || !is_string($result)) {
                    $msg['code']=1000;
                }else{
                    $result_array=xmltoarray($result);
                    if (array_key_exists('Error',$result_array)){
                        $msg['code']='104';
                    }else{
                        $admininfo=$this->getMerchant($params['key_admin']);
                        $db=M('mem',$admininfo['pre_table']);
                        $rt['cardno']=$result_array['Member']['VIPCARDNO'];
                        $rt['usermember']=$result_array['Member']['SURNAME'];
                        $rt['idnumber']=$result_array['Member']['VIPID'];
                        $rt['status']=$result_array['Member']['ACTIVE'];
                        $rt['status_description']='';
                        $rt['getcarddate']=$result_array['Member']['JOINTDATE'];
                        $rt['expirationdate']=$result_array['Member']['EXPIRYDATE'];//到期时间
                        $rt['birthday']=$result_array['Member']['BIRTHDAYYYYY'];
                        $rt['company']='';
                        $rt['phone']=$result_array['Member']['TELEPHONE'];
                        $rt['mobile']=$result_array['Member']['TELEPHONE'];
                        $rt['address']=$result_array['Member']['ADDRESS1'];
                        $sel=$db->where(array('cardno'=>$rt['cardno']))->find();
                        if (null == $sel){
                            $sv=$db->add($rt);
                        }else{
                            unset($rt['usermember']);
                            $sv=$db->where(array('cardno'=>$rt['cardno']))->save($rt);
                        }
                        
                        $data['cardno']=$result_array['Member']['VIPCARDNO'];
                        $data['user']=$result_array['Member']['SURNAME'];
                        $data['name']=$result_array['Member']['SURNAME'];
                        $data['cardtype']=$result_array['Member']['GRADE'];
                        $data['status']=$result_array['Member']['ACTIVE'];
                        $data['email']=$result_array['Member']['VIPEMAIL'];
                        $data['status_description']='';
                        $data['getcarddate']=$result_array['Member']['JOINTDATE'];//创建时间
                        $data['expirationdate']=$result_array['Member']['EXPIRYDATE'];//到期时间
                        $data['birthday']=$result_array['Member']['BIRTHDAYYYYY'];
                        $data['company']='';
                        $data['phone']=$result_array['Member']['TELEPHONE'];
                        $data['mobile']=$result_array['Member']['TELEPHONE'];
                        $data['address']=$result_array['Member']['ADDRESS1'];
                        $data['score']=$result_array['Member']['CURRENTBONUS'];//剩余积分
                        $msg['code']=200;
                        $msg['data']=$data;
                    }
                    
                }
//             }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    
    
    
    private function GetUserinfoByCardself($card)
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['card']=$card;
        //执行业务
        $url=$this->api_url.'/ws_member.asmx/GetMemberInfoByCardNo';
        $pa['strCallUserCode']=$this->strCallUserCode;
        $pa['strCallPassword']=$this->strCallPassword;
        $pa['strMemberCode']=$params['card'];

        $header=array('Content-Type:application/x-www-form-urlencoded',true);
        $result=http($url, $pa,'POST',$header);
        if(empty($result) || !is_string($result)) {
            $msg['code']=1000;
        }else{
            $result_array=xmltoarray($result);
            $msg['code']=200;
            $msg['data']=$result_array['Member'];
        }
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
        $params['mobile']=I('mobile');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{//执行业务
                $url=$this->api_url.'/ws_member.asmx/GetMemberInfo';
                $pa['strCallUserCode']=$this->strCallUserCode;
                $pa['strCallPassword']=$this->strCallPassword;
                $pa['strTelephone']=$params['mobile'];
        
                $header=array('Content-Type:application/x-www-form-urlencoded',true);
                $result=http($url, $pa,'POST',$header);
                if(empty($result) || !is_string($result)) {
                    $msg['code']=1000;
                }else{
                    $result_array=xmltoarray($result);
                    $admininfo=$this->getMerchant($params['key_admin']);
                    $db=M('mem',$admininfo['pre_table']);
                    $rt['cardno']=$result_array['Member']['VIPCARDNO'];
                    $rt['usermember']=$result_array['Member']['SURNAME'];
                    $rt['idnumber']=$result_array['Member']['VIPID'];
                    $rt['status']=$result_array['Member']['ACTIVE'];
                    $rt['status_description']='';
                    $rt['getcarddate']=$result_array['Member']['JOINTDATE'];
                    $rt['expirationdate']=$result_array['Member']['EXPIRYDATE'];//到期时间
                    $rt['birthday']=$result_array['Member']['BIRTHDAYYYYY'];
                    $rt['company']='';
                    $rt['phone']=$result_array['Member']['TELEPHONE'];
                    $rt['mobile']=$result_array['Member']['TELEPHONE'];
                    $rt['address']=$result_array['Member']['ADDRESS1'];
                    $sel=$db->where(array('cardno'=>$rt['cardno']))->find();
                    if (null == $sel){
                        $sv=$db->add($rt);
                    }else{
                        unset($rt['usermember']);
                        $sv=$db->where(array('cardno'=>$rt['cardno']))->save($rt);
                    }
                    
                    $data['cardno']=$result_array['Member']['VIPCARDNO'];
                    $data['user']=$result_array['Member']['SURNAME'];
                    $data['cardtype']=$result_array['Member']['GRADE'];
                    $data['status']=$result_array['Member']['ACTIVE'];
                    $data['status_description']='';
                    $data['getcarddate']=$result_array['Member']['JOINTDATE'];//创建时间
                    $data['expirationdate']=$result_array['Member']['EXPIRYDATE'];//到期时间
                    $data['birthday']=$result_array['Member']['BIRTHDAYYYYY'];
                    $data['company']='';
                    $data['phone']=$result_array['Member']['TELEPHONE'];
                    $data['mobile']=$result_array['Member']['TELEPHONE'];
                    $data['address']=$result_array['Member']['ADDRESS1'];
                    $data['score']=$result_array['Member']['CURRENTBONUS'];//剩余积分
                    $msg['code']=200;
                    $msg['data']=$data;
                }
//             }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }

    
    /**
     * (non-PHPdoc)
     * @see \CrmService\Controller\CrminterfaceController::scorelist()
     */
    public function scorelist(){
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $params['lines'] = I('lines');
        $params['startdate']=I('startdate');
        $params['enddate']=I('enddate');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
            $result = http('http://fw.joycity.mobi/kaapi/Api/Index/scorelist', $params, 'POST');
            echo $result;
        }
//        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    // TODO - Insert your code here
    
    
    /**
     * @deprecated 欧亚卖场
     * @传入参数 key_admin、sign 、skt、Jlbh、md
     */
    public function billInfo(){
    
    }

    public function GetUserinfoByOpenid(){

    }
    
    /**
     * 解绑
     */
    public function UnBind(){}
}

?>