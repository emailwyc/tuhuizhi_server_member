<?php
namespace CrmService\Controller\Crmapi;

use CrmService\Controller\CrminterfaceController;
use CrmService\Controller\CommonController;

class XidanNewcrmController extends CommonController implements CrminterfaceController
{
    private $app_id='106425178181';
//     private $strCallPassword='ABCDEF123456GHIJK';
    private $api_url='https://open.joycity.mobi/gw';
    private $key = 'Mk2uROs6WZAYrzYyfnUCbZzGwPLB8HS1';
    private $marketID = '000006';
//     private $api_url='https://open.joycity.mobi/fgw';
 /* (non-PHPdoc)
     * @see \CrmService\Controller\CrminterfaceController::addintegral()
     */
    public function addintegral()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
//         $params['key_admin']=I('key_admin');
        $params['strMemberCode']=I('cardno');
        $params['strBonusPoint']=abs(I('scoreno'));
        $params['strAdjustReason']=I('why');
        if (in_array('',$params)){//获取的参数不完整
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $key_admin = I('key_admin');
        $scorecode = I('scorecode');
        
        $params['strMallId'] = $this->marketID;
        $params['app_id'] = $this->app_id;
        $params['method'] = 'joycity.crm.bonus.BonusAdjustment';
        list($t1, $t2) = explode(' ', microtime());
        $params['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $params['sign'] = $this->sign_action($params,$this->key);
//         print_r($params);
        $result = http($this->api_url, $params, 'POST');
//         print_r($result);die;
        if(empty($result) || !is_json($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $result_array = json_decode($result,true);

        if($result_array['result_code'] != 0 || $result_array['code'] != 0){
            returnjson(array('code'=>104,'data'=>$result_array['result_msg']),$this->returnstyle,$this->callback);
        }
        
        $data['cardno']=$params['strMemberCode'];
        $data['scorenumber']=$params['strBonusPoint'];
        $data['why']=$params['strAdjustReason'];
        $data['scorecode']=$scorecode;
        $data['datetime']=date('Y-m-d H:i');
        $data['cutadd']=2;
        $admininfo=$this->getMerchant($key_admin);
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
//         $params['key_admin']=I('key_admin');
        $params['strMemberCode']=I('cardno');
        $params['strBonusPoint']='-'.abs(I('scoreno'));
        $params['strAdjustReason']=I('why');
        if (in_array('',$params)){//获取的参数不完整
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        $key_admin = I('key_admin');
        $scorecode = I('scorecode');
        
        $params['strMallId'] = $this->marketID;
        $params['app_id'] = $this->app_id;
        $params['method'] = 'joycity.crm.bonus.BonusAdjustment';
        list($t1, $t2) = explode(' ', microtime());
        $params['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $params['sign'] = $this->sign_action($params,$this->key);
        //         print_r($params);
        $result = http($this->api_url, $params, 'POST',array(),false,30);
//         print_r($result);die;
        if(empty($result) || !is_string($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $result_array = json_decode($result,true);
// print_r($result_array);die;
        if($result_array['result_code'] != 0|| $result_array['code'] != 0){
            returnjson(array('code'=>104,'data'=>$result_array['result_msg']),$this->returnstyle,$this->callback);
        }
        
        $data['cardno']=$params['strMemberCode'];
        $data['scorenumber']=$params['strBonusPoint'];
        $data['why']=$params['strAdjustReason'];
        $data['scorecode']=$scorecode;
        $data['datetime']=date('Y-m-d H:i');
        $data['cutadd']=1;
        $admininfo=$this->getMerchant($key_admin);
        $db=M('score_record',$admininfo['pre_table']);
        $add=$db->add($data);
        
        $msg['code']=200;
        
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    protected function sign_action($params,$key){
        ksort($params);
        $str = '';
        foreach ($params as $k => $v) {
            if($v !='' ){
                if ('' == $str) {
                    $str .= $k . '=' . trim($v);
                } else {
                    $str .= '&' . $k . '=' . trim($v);
                }
            }
        }
        //         $secret = 'c7aefbe1c14e471e970eef7d66a13607';
        $str .= '&key='.$key;
//         return $str;die;
        return md5($str);
    }
    
    
 /**
 * 创建会员
 */
    public function createMember()
    {
        $params['mobile'] = I('mobile');
        
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        $issuetype = I('issuetype')?I('issuetype'):'02';
        $userclid = I('userclid');
        $openid = I('openid');
        $params['name'] = I('name');//姓名
        $params['eMail']=I('email');//邮箱
        $params['idcard'] = I('idnumber'); //身份证号
        $params['birthDay'] = I('birthday');//生日
        $params['sex']=I('sex');//性别
        $params['attachedId'] = $issuetype == '02'?$openid:$userclid;
        //需提供的参数
        $params['issueStore'] = $this->marketID;//商场编号
        $params['issueType']  = $issuetype;//第三方ID
        $params['app_id'] = $this->app_id;
        $params['method'] = 'joycity.crm.member.createMemberByUnion';
        list($t1, $t2) = explode(' ', microtime());
        $params['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $params['sex'] = (0 == $params['sex']) ? "M" : "F";
        $key_admin = I('key_admin');
        $params['sign'] = $this->sign_action($params,$this->key);
//         print_r($params);
        $result = http($this->api_url, $params, 'POST');
//         print_r($result);die;
        if(empty($result) || !is_json($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $result_array = json_decode($result,true);

        if($result_array['result_code'] == '-403'){
            returnjson(array('code'=>2001),$this->returnstyle,$this->callback);
        }
        
        if($result_array['result_code'] != 0 || $result_array['code'] != 0){
            returnjson(array('code'=>104,'data'=>$result_array['result_msg']),$this->returnstyle,$this->callback);
        }
        $data['cardno']=$result_array['data']['cardNo'];
        $data['member_id']=$result_array['data']['cid'];
        $data['usermember']=$params['name'];
        $data['idnumber']=$params['idcard'];
        $data['getcarddate']=date('Y-m-d');
        $data['phone']=$params['mobile'];
        $data['mobile']=$params['mobile'];
        $data['sex']=$params['sex'];
        if($issuetype == '02'){
            $data['openid'] = $openid;
        }else{
            $data['userid'] = $userclid;
        }
        
        $admininfo=$this->getMerchant($key_admin);
        $db=M('mem',$admininfo['pre_table']);
        $mem_return = $db->where(array('cardno'=>$data['cardno']))->find();
        
        if($mem_return){
            $add=$db->where(array('cardno'=>$data['cardno']))->save($data);
        }else{
            $add=$db->add($data);
        }
        
        $msg['code']=200;
        $list=array(
            'cardno'=>$data['cardno'],
            'usermember'=>$params['name'],
            'getcarddate'=>date('Y-m-d'),
            'expirationdate'=>'',
            'mobile'=>$params['mobile'],
            'sex'=>$params['sex'],
            'idnumber'=>$params['idcard']
        );
        $msg['data']=$list;
        returnjson($msg,$this->returnstyle,$this->callback);
    }


/**
 * 修改会员
 */
    public function editMember()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['strVipCode']=I('carano')?I('carano'):I('cardno');//卡号
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
            
            $params['strGivenName']=I('name');//姓名
            $openid = I('openid');
            $params['strSex']=(0==I('sex')) ? 'M' : 'F';//性别
            $params['strSurname']=$params['strGivenName'];//姓名
            $params['strVipEmail']=I('email');//邮箱
            $params['strAddress1'] = I('address');//地址
            $params['strDob_YYYYMMDD'] = I('birthday');//生日
            $params['app_id'] = $this->app_id;
            $params['method'] = 'joycity.crm.member.UpdateMemberInfo';
            list($t1, $t2) = explode(' ', microtime());
            $params['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
            $key_admin = I('key_admin');
            $params['sign'] = $this->sign_action($params,$this->key);
            $result=http($this->api_url, $params,'POST');
//             print_r($result);
            if(empty($result) || !is_string($result)) {
                returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
            }
            
            $result_array=json_decode($result,true);
            
            if($result_array['result_code'] != 0 || $result_array['code'] != 0){
                returnjson(array('code'=>104,'data'=>$result_array['result_msg']),$this->returnstyle,$this->callback);
            }
            $msg['code']=200;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }

 /* (non-PHPdoc)
     * @see \CrmService\Controller\CrminterfaceController::GetUserinfoByCard()
     */
    public function GetUserinfoByCard()
    {
        $msg=$this->commonerrorcode;
//         $params['key_admin']=I('key_admin');
        $params['cardNo']=I('card');
//         $params['cardNo'] = I('cardno');
        if (in_array('',$params)){//获取的参数不完整
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $issuetype = I('issuetype')?I('issuetype'):'02';
        $bindtype = I('bindtype')?I('bindtype'):'0';
        $openid = I('openid');
        $userclid = I('userclid');
        $params['issueStore'] = $this->marketID;//商场编号
        $params['issueType']  = $issuetype;//第三方ID
//         $params['attachedId'] = $issuetype=='02'?$openid:$userclid;
        $params['app_id'] = $this->app_id;
        $params['method'] = 'joycity.crm.member.getUnionMemberInfo';
        list($t1, $t2) = explode(' ', microtime());
        $params['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $params['bindType'] = $bindtype;
//         $params['sex'] = (0 == $params['sex']) ? "M" : "F";
//         $params['attachedId'] = 'sdhskjfhakfhkhfk';
        $key_admin = I('key_admin');
        $params['sign'] = $this->sign_action($params,$this->key);
//                 print_r($params);
        $result = http($this->api_url, $params, 'POST');
//         print_r($result);die;
        if(empty($result) || !is_string($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $result_array=json_decode($result,true);
//         print_r($result_array);die;
        if ($result_array['result_code'] != 0 || $result_array['code'] != 0){
            returnjson(array('code'=>104,'data'=>$result_array['result_msg']),$this->returnstyle,$this->callback);
        }
        
        $msg = $this->member_data($result_array,$params,$openid,$key_admin);
        
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    public function member_data($result_array,$params,$openid='',$key_admin,$userclid=''){
        $admininfo=$this->getMerchant($key_admin);
        $db=M('mem',$admininfo['pre_table']);
        $result_data = $result_array['data'];
        $rt['cardno']=empty($result_data['cardNo'])?"":$result_data['cardNo'];
        $rt['usermember']=empty($result_data['name'])?"":$result_data['name'];
//         $rt['idnumber']=empty($result_data['VIPID'])?"":$result_data['VIPID'];
        $rt['status']=@$result_data['active']?$result_data['active']:"";
        $rt['birthday']=@$result_data['birthday']?date('Y-m-d',strtotime($result_data['birthday'])):"";//到期时间
        
        if($result_data['grade']==1){
            $result_data['grade'] = '72';
        }
        
        if($result_data['grade']==2){
            $result_data['grade'] = '01';
        }
        
        if($result_data['grade']==3){
            $result_data['grade'] = '02';
        }
        $rt['level'] = $result_data['grade'];
        $rt['phone']=$result_data['mobile'];
        $rt['mobile']=$result_data['mobile'];
//         $rt['address']=@$result_data['ADDRESS1']?$result_data['ADDRESS1']:'';
        $rt['sex']=@$result_data['sex']?$result_data['sex']:'';
        $rt['email']=@$result_data['email']?$result_data['email']:'';
        $rt['score_num'] = @$result_data['currentBonus']?$result_data['currentBonus']:'';
        $sel=$db->where(array('cardno'=>$rt['cardno']))->find();
        if (null == $sel){
            $sv=$db->add($rt);
        }else{
            unset($rt['usermember']);
            $sv=$db->where(array('cardno'=>$rt['cardno']))->save($rt);
        }
        $data['cardno']=$rt['cardno'];
        $data['user']=empty($result_data['name'])?"":$result_data['name'];
        $data['name']=$data['user'];//会员卡用户名
        $data['cardtype']=$rt['level'];
        $data['status']=$rt['status'];
        $data['birthday']=$rt['birthday'];
        $data['company']='';
        $data['phone']=@$result_data['mobile'];
        $data['mobile']=@$result_data['mobile'];
        $data['address']="";
        $data['sex']=('M'==$rt['sex']) ? 0 : 1;
        $data['email'] = $rt['email'];
        $data['score']=@$result_data['currentBonus']?$result_data['currentBonus']:0;//剩余积分
        $msg['code']=200;
        //         print_R($data);die;
        $msg['data']=$data;
        return $msg;
    }
    
/**
 * 根据手机号查询数据
 */
    public function GetUserinfoByMobile()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
//         $params['key_admin']=I('key_admin');
        $params['mobile']=I('mobile');
//         $params['cardNo'] = I('cardno');
        if (in_array('',$params)){//获取的参数不完整
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $issuetype = I('issuetype')?I('issuetype'):'02';
        $bindtype = I('bindtype')?I('bindtype'):'1';
        $openid = I('openid');
        $userclid = I('userclid');
        $params['issueStore'] = $this->marketID;//商场编号
        $params['issueType']  = $issuetype;//第三方ID
        $params['attachedId'] = $issuetype=='02'?$openid:$userclid;
        $params['app_id'] = $this->app_id;
        $params['method'] = 'joycity.crm.member.getUnionMemberInfo';
        list($t1, $t2) = explode(' ', microtime());
        $params['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $params['bindType'] = $bindtype;
//         $params['sex'] = (0 == $params['sex']) ? "M" : "F";
//         $params['attachedId'] = 'sdkfhsakfhakjfdhkadshf';
        $key_admin = I('key_admin');
        $params['sign'] = $this->sign_action($params,$this->key);
//                 print_r($params);
        $result = http($this->api_url, $params, 'POST');
//         print_r($result);
        if(empty($result) || !is_string($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $result_array=json_decode($result,true);
//         print_r($result_array);die;

        if($result_array['result_code'] == '-501'){
            returnjson(array('code'=>103),$this->returnstyle,$this->callback);
        }
        
        if ($result_array['result_code'] != 0 || $result_array['code'] != 0){
            returnjson(array('code'=>102,'data'=>$result_array['result_msg']),$this->returnstyle,$this->callback);
        }
        
        $msg = $this->member_data($result_array,$params,$openid,$key_admin,$userclid);
        
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 解绑接口
     */
    public function UnBind()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        //         $params['key_admin']=I('key_admin');
        $params['cardNo']=I('cardno');
        //         $params['cardNo'] = I('cardno');
        if (in_array('',$params)){//获取的参数不完整
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $issuetype = I('issuetype')?I('issuetype'):'02';
        $bindtype = '2';
        $openid = I('openid');
        $userclid = I('userclid');
        $params['issueStore'] = $this->marketID;//商场编号
        $params['issueType']  = $issuetype;//第三方ID
        $params['attachedId'] = $issuetype=='02'?$openid:$userclid;
        $params['app_id'] = $this->app_id;
        $params['method'] = 'joycity.crm.member.getUnionMemberInfo';
        list($t1, $t2) = explode(' ', microtime());
        $params['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $params['bindType'] = $bindtype;
        //         $params['sex'] = (0 == $params['sex']) ? "M" : "F";
        //         $params['attachedId'] = 'sdkfhsakfhakjfdhkadshf';
        $key_admin = I('key_admin');
        $params['sign'] = $this->sign_action($params,$this->key);
        //                 print_r($params);
        $result = http($this->api_url, $params, 'POST');
//                 print_r($result);die;
        if(empty($result) || !is_string($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
    
        $result_array=json_decode($result,true);
//         print_r($result_array);die;
    
        if($result_array['result_code'] == '-501'){
            returnjson(array('code'=>103),$this->returnstyle,$this->callback);
        }
    
        if ($result_array['result_code'] != 0 || $result_array['code'] != 0){
            returnjson(array('code'=>104,'data'=>$result_array['result_msg']),$this->returnstyle,$this->callback);
        }
    
        $msg['code'] = 200;
//         $msg = $this->member_data($result_array,$params,$openid,$key_admin,$userclid);
    
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    
    
    
/* (non-PHPdoc)
     * @see \CrmService\Controller\CrminterfaceController::scorelist()
     */
    public function scorelist(){
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
//         $params['key_admin']=I('key_admin');
        $params['strMemberCode']=I('cardno');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
            $key_admin = I('key_admin');
            $params['strEndRow'] = I('lines')?I('lines'):20;
            $params['strStartRow'] = I('page_num')?I('page_num'):'0';
            $key_admin = I('key_admin');
            $params['app_id'] = $this->app_id;
            $params['method'] = 'joycity.crm.bonus.GetMemberBonusLedger';
            list($t1, $t2) = explode(' ', microtime());
            $params['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
            //         $params['sex'] = (0 == $params['sex']) ? "M" : "F";
            $params['sign'] = $this->sign_action($params,$this->key);
//                             print_r($params);
            $result = http($this->api_url, $params, 'POST');
//             print_r($result);die;

            if(empty($result) || !is_string($result)) {
                returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
            }

            $result_array=json_decode($result,true);
//             print_r($result_array);die;
            if ($result_array['result_code'] != 0 || $result_array['code'] != 0){
                returnjson(array('code'=>102,'data'=>$result_array['result_msg']),$this->returnstyle,$this->callback);
            }

            foreach($result_array['data'] as $k=>$v){
                $data['date'] =$v['TXDATE'].' '.$v['TXTIME'];
                $data['description'] = $v['ACTION'];
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
        
        $param['cardNo']=$info['cardno'];
        $param['issueStore'] = $this->marketID;//商场编号
        $param['issueType']  = '02';//第三方ID
        $param['app_id'] = $this->app_id;
        $param['method'] = 'joycity.crm.member.getUnionMemberInfo';
        list($t1, $t2) = explode(' ', microtime());
        $param['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $param['bindType'] = '0';
//         $params['sex'] = (0 == $params['sex']) ? "M" : "F";
        $param['attachedId'] = '';
        $key_admin = I('key_admin');
        $param['sign'] = $this->sign_action($param,$this->key);
        
        $result = http($this->api_url, $param, 'POST');
//         print_r($result);die;
        if(empty($result) || !is_string($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $result_array=json_decode($result,true);
        //         print_r($result_array);die;
        if ($result_array['result_code'] != 0 || $result_array['code'] != 0){
            returnjson(array('code'=>104,'data'=>$result_array['result_msg']),$this->returnstyle,$this->callback);
        }
        
        $msg = $this->member_data($result_array,$params,$params['openid'],$key_admin);
        
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }

    
    /**
     * @see \CrmService\Controller\CrminterfaceController::getInfoByOpenid()
     */
    public function getInfoByOpenid(){
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
        
        $param['cardNo']=$info['cardno'];
        $param['issueStore'] = $this->marketID;//商场编号
        $param['issueType']  = '02';//第三方ID
        $param['app_id'] = $this->app_id;
        $param['method'] = 'joycity.crm.member.getUnionMemberInfo';
        list($t1, $t2) = explode(' ', microtime());
        $param['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $param['bindType'] = '0';
//         $params['sex'] = (0 == $params['sex']) ? "M" : "F";
        $param['attachedId'] = '';
        $key_admin = I('key_admin');
        $param['sign'] = $this->sign_action($param,$this->key);
        
        $result = http($this->api_url, $param, 'POST');
//         print_r($result);die;
        if(empty($result) || !is_string($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $result_array=json_decode($result,true);
        //         print_r($result_array);die;
        if ($result_array['result_code'] != 0 || $result_array['code'] != 0){
            returnjson(array('code'=>104,'data'=>$result_array['result_msg']),$this->returnstyle,$this->callback);
        }
        
        $msg = $this->member_data($result_array,$params,$params['openid'],$key_admin);
        
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

            $admininfo=$this->getMerchant($params['key_admin']);
            $db=M('mem',$admininfo['pre_table']);
            $info = $db->where(array('openid'=>$params['openid']))->find();
            
            if(empty($info)){
                returnjson(array('code'=>102),$this->returnstyle,$this->callback);
            }

            list($t1, $t2) = explode(' ', microtime());
            $timestamp = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
            $params2 = array(
                'key_admin'=> $params['key_admin'],
                'strMemberCode'=>$info['cardno'],
                'strBonusPoint' =>$params['score'],
                'strAdjustReason'   =>$params['why'],
                'strMallId'     =>$this->marketID,
                'app_id'      =>$this->app_id,
                'method'     =>'joycity.crm.bonus.BonusAdjustment',
                'timestamp'     =>$timestamp,
            );
            $params2['sign'] = $this->sign_action($params2,$this->key);
            $result = http($this->api_url, $params2, 'POST');
            
            if(empty($result) || !is_json($result)) {
                returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
            }
            
            $result_array = json_decode($result,true);
            
            if($result_array['result_code'] != 0 || $result_array['code'] != 0){
                returnjson(array('code'=>104,'data'=>$result_array['result_msg']),$this->returnstyle,$this->callback);
            }
            
            $data['cardno']=$params2['strMemberCode'];
            $data['scorenumber']=$params2['strBonusPoint'];
            $data['why']=$params2['strAdjustReason'];
            $data['scorecode']='';
            $data['datetime']=date('Y-m-d H:i');
            $data['cutadd']=2;
            $db=M('score_record',$admininfo['pre_table']);
            $add=$db->add($data);
            
            $msg['code']=200;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    //通过userid送积分
    public function addintegralbycard()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
//         $params['key_admin']=I('key_admin');
        $params['strMemberCode']=I('cardno');
        $params['strBonusPoint']=abs(I('score'));
        $params['strAdjustReason']=I('why');
        if (in_array('',$params)){//获取的参数不完整
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $key_admin = I('key_admin');
        $scorecode = I('scorecode');
        
        $params['strMallId'] = $this->marketID;
        $params['app_id'] = $this->app_id;
        $params['method'] = 'joycity.crm.bonus.BonusAdjustment';
        list($t1, $t2) = explode(' ', microtime());
        $params['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $params['sign'] = $this->sign_action($params,$this->key);
//         print_r($params);
        $result = http($this->api_url, $params, 'POST');
//         print_r($result);die;
        if(empty($result) || !is_json($result)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $result_array = json_decode($result,true);

        if($result_array['result_code'] != 0 || $result_array['code'] != 0){
            returnjson(array('code'=>104,'data'=>$result_array['result_msg']),$this->returnstyle,$this->callback);
        }
        
        $data['cardno']=$params['strMemberCode'];
        $data['scorenumber']=$params['strBonusPoint'];
        $data['why']=$params['strAdjustReason'];
        $data['scorecode']=$scorecode;
        $data['datetime']=date('Y-m-d H:i');
        $data['cutadd']=2;
        $admininfo=$this->getMerchant($key_admin);
        $db=M('score_record',$admininfo['pre_table']);
        $add=$db->add($data);
        
        $msg['code']=200;
        
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    
    /**
     * 微信扫码积分
     */
    public function WechatScanScore(){
        $params['cardno'] = I('cardno');
        $params['orderno'] = I('orderno');
        $params['key_admin'] = I('key_admin');
    
        if(in_array('', $params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
    
        $order_no_lenth = strlen($params['orderno']);
        if($order_no_lenth>50){
            returnjson(array('code'=>1082,'msg'=>'订单号有误'),$this->returnstyle,$this->callback);
        }
        
//         $admininfo=$this->getMerchant($params['key_admin']);
        $data['cardNo'] = html_entity_decode($params['cardno']);
        $data['orderNo'] = $params['orderno'];
    
        $data['app_id'] = $this->app_id;
        $data['method'] = 'joycity.crm.bonus.scScore';
        list($t1, $t2) = explode(' ', microtime());
        $data['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $data['sign'] = $this->sign_action($data,$this->key);
        
        $return_msg = http($this->api_url,$data,'POST');
        
        if(empty($return_msg) || !is_json($return_msg)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
    
        $return_data = json_decode($return_msg,true);
    
        if($return_data['code']!=0 || $return_data['result_code'] != 0){
            $return_data['result_msg'] = $return_data['result_msg']?$return_data['result_msg']:'';
            returnjson(array('code'=>104,'data'=>$return_data['result_msg']),$this->returnstyle,$this->callback);
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
        
        $params['app_id'] = $this->app_id;
        $params['method'] = 'joycity.crm.creditpay.generateQRCode';
        list($t1, $t2) = explode(' ', microtime());
        $params['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $params['sign'] = $this->sign_action($params,$this->key);
        
        $return_msg = http($this->api_url,$params,'POST');
        
        if(empty($return_msg) || !is_json($return_msg)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $return_data = json_decode($return_msg,true);
        
        if($return_data['code']!=0 || $return_data['result_code'] != 0){
            returnjson(array('code'=>104,'data'=>$return_data['result_msg']),$this->returnstyle,$this->callback);
        }
        
        $data['qrcode'] = $return_data['data']['qrCode']?$return_data['data']['qrCode']:'';
        $data['name'] = $return_data['data']['name']?$return_data['data']['name']:'';
        $data['mobile'] = $return_data['data']['mobile']?$return_data['data']['mobile']:'';
        $data['cardno'] = $return_data['data']['cardNo']?$return_data['data']['cardNo']:'';
        $data['sex'] = $return_data['data']['grade']?$return_data['data']['grade']:'';
        $data['score'] = $return_data['data']['usableScore']?$return_data['data']['usableScore']:'';
        $data['money'] = $return_data['data']['usableScoreWorth']?$return_data['data']['usableScoreWorth']:'';
        
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
        returnjson(array('code'=>200,'data'=>$data?$data:array()),$this->returnstyle,$this->callback);
    }
    
    
    /**
     * 积分换礼
     */
    public function ExchangeGiftsByIntegral(){
        $params['strMemberCode'] = I('cardno');
        $params['strGiftCode'] = I('gift_code');
        $params['strBonus'] = I('score');
        
        if(in_array('', $params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        $params['strMallId'] = '000006';
        $params['strRedeemQty'] = 1;
        $params['app_id'] = $this->app_id;
        $params['method'] = 'joycity.crm.bonus.GetMemberGiftRedeemPostBonus';
        list($t1, $t2) = explode(' ', microtime());
        $params['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $params['sign'] = $this->sign_action($params,$this->key);
        
        $return_msg = http($this->api_url,$params,'POST');
        
        if(empty($return_msg) || !is_json($return_msg)) {
            returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
        }
        
        $return_data = json_decode($return_msg,true);
        
        if($return_data['code']!=0 || $return_data['result_code'] != 0){
            returnjson(array('code'=>104,'data'=>$return_data['result_msg']),$this->returnstyle,$this->callback);
        }
        
        $msg['code'] = 200;
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
}

?>
