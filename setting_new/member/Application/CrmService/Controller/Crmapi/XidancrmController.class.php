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
        $params['userclid']=I('userclid');
        $params['issuetype']=I('issuetype');
        $params['issuetype']  = !empty($params['issuetype'])?"01":$params['issuetype'];

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
                $url='http://fw.joycity.mobi/kaapi/Api/Index/cutScores';
                $result=http($url, $params,'POST');
                if(empty($result) || !is_string($result)) {
                    $msg['code']=1000;
                }else{
                    $result = json_decode($result,true);
//                     print_r($result['data']);die;
                    if ($result['code'] !=200){
                        $msg['code']=$result['code'];
                    }else{
                        $data['cardno']=$params['cardno'];
                        $data['scorenumber']=$params['scoreno'];
                        $data['why']=$params['why'];
                        $data['scorecode']=$params['scorecode'];
                        $data['cutadd']=1;
                        $admininfo=$this->getMerchant($params['key_admin']);
                        $db=M('score_record',$admininfo['pre_table']);
                        $add=$db->add($data);
                        $msg = $result;
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
                $userinfo=$this->GetUserinfoByCardself($params['key_admin'],$params['carano']);//先获取会员信息，修改会员信息时，保证只修改传入的信息，对不传入的信息原样返回
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
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
            $result = http('http://fw.joycity.mobi/kaapi/Api/Index/getUserInfoByCardnoNew', $params, 'POST');
            $result = json_decode($result, true);
            if(empty($result['data']) && $result['code'] == 200) {
                $msg['code'] = 1000;
            }else{
                $result_array = $result['data'];
                    if (array_key_exists('Error',$result_array)){
                        $msg['code']='104';
                    }else{
                        $admininfo=$this->getMerchant($params['key_admin']);
                        $db=M('mem',$admininfo['pre_table']);
                        $rt['cardno']=empty($result_array['VipCardNo'])?"":$result_array['VipCardNo'];
                        $rt['usermember']=empty($result_array['SurName'])?"":$result_array['SurName'];
                        $rt['idnumber']=empty($result_array['ID'])?"":$result_array['ID'];
                        $rt['status']=@$result_array['Active'];
                        $rt['status_description']='';
                        $rt['getcarddate']="";
                        $rt['expirationdate']="";//到期时间
                        $rt['birthday']=@$result_array['BirthDay'] ? : '';
                        $rt['level'] = @$result_array['Grade'];
                        $rt['company']='';
                        $rt['phone']=@$result_array['Mobile'];
                        $rt['mobile']=@$result_array['Mobile'];
                        $rt['address']="";
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

        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    
    
    
    private function GetUserinfoByCardself($key_admin,$card)
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $postArr = array('key_admin'=>$key_admin,'card'=>$card);
        //执行业务
        $result = http('http://fw.joycity.mobi/kaapi/Api/Index/getUserInfoByCardno', $postArr, 'POST');
        $result = json_decode($result, true);
        if(empty($result['data']) && $result['code'] == 200) {
            $msg['code'] = 1000;
        }else {
            $result_array = $result['data'];
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
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
            $params['issuetype']=I('issuetype');
            $params['issuetype']  = !empty($params['issuetype'])?"01":$params['issuetype'];
            $params['userclid']=I('userclid');
            $result = http('http://fw.joycity.mobi/kaapi/Api/Index/getUserInfoByMobileNew', $params, 'POST');
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
                $rt['status']=@$result_array['Active'] ? : '';
                $rt['status_description']='';
                $rt['getcarddate']="";
                $rt['expirationdate']="";//到期时间
                $rt['birthday']=@$result_array['BirthDay'] ? : '';
                $rt['level'] = @$result_array['Grade'];
                $rt['company']='';
                $rt['phone']=@$result_array['Mobile'] ? : "";
                $rt['mobile']=@$result_array['Mobile'] ? : "";
                $rt['address']="";
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
     * @deprecated 欧亚卖场
     * @传入参数 key_admin、sign 、skt、Jlbh、md
     */
    public function billInfo(){
    
    }

    public function GetUserinfoByOpenid(){

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
    
        $data['qrcode'] = $return_data['data']['qrCode'];
        $data['name'] = $return_data['data']['name'];
        $data['mobile'] = $return_data['data']['mobile'];
        $data['cardno'] = $return_data['data']['cardNo'];
        $data['sex'] = $return_data['data']['grade'];
        $data['score'] = $return_data['data']['usableScore'];
        $data['money'] = $return_data['data']['usableScoreWorth'];
        returnjson(array('code'=>200,'data'=>$data),$this->returnstyle,$this->callback);
    }
    
    /**
     * 解绑
     */
    public function UnBind(){}
    
}

?>

