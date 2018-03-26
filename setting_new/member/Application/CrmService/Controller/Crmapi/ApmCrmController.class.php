<?php
namespace CrmService\Controller\Crmapi;

use CrmService\Controller\CommonController;
use CrmService\Controller\CrminterfaceController;

class ApmCrmController extends CommonController implements CrminterfaceController
{
    protected $request_url = 'http://bjapm.lms.hk/index.php/lmsapi_bjapm_app/';//测试地址
    protected $admin_arr;
    protected $key_admin;
    
    public function _initialize()
    {
        parent::_initialize();
        
        $this->key_admin = I('key_admin');
        
        $this->admin_arr = $this->getMerchant($this->key_admin);
    }

    /**
     * @deprecated 根据Openid获取会员信息
     * @传入参数   key_admin、sign、openid
     */
    public function GetUserinfoByOpenid(){}

    
    /**
     * @deprecated  积分扣除
     * @传入参数  key_admin、sign、cardno、scoreno、why
     */
    public function cutScore(){
        $params = I('param.');
        $this->paramsCheck($params, array('cardno','openid'), array('cardno','openid'));
        
        $data['number'] = $params['cardno'];
        $return_data = $this->memberinfo_action($data);//获取当前用户token
        if($return_data==false || $return_data['token'] == ''){
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);exit;
        }
        $data['token'] = $return_data['token'];
        $data['mobile'] = $return_data['mobile'];
        $data['open_id'] = $params['openid'];
        $data['reason'] = $params['reasoncode']?$params['reasoncode']:'visit';
        $data['bonus_change'] = '-'.abs($params['bonus_change']);
        $data['remarks'] = $params['why'];
        
        $return_data = $this->score_action($data);
        
        print_r($return_data);die;
    }
    
    /**
     * @deprecated  积分添加
     * @传入参数  key_admin、sign、cardno、scoreno、scorecode、why、membername
     */
    public function addintegral(){
        $params = I('param.');
        $this->paramsCheck($params, array('cardno','openid'), array('cardno','openid'));
        
        $data['number'] = $params['cardno'];
        $return_data = $this->memberinfo_action($data);//获取当前用户token
//         print_R($return_data);
        if($return_data['result'] != '0'){
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);exit;
        }
//         echo "1";die;
        $data['token'] = $return_data['token'];
        $data['mobile'] = $return_data['mobile'];
        $data['open_id'] = $params['openid'];
        $data['reason'] = $params['reasoncode']?$params['reasoncode']:'luckydraw';
        $data['bonus_change'] = '+'.abs($params['scoreno']);
        $data['remarks'] = $params['why'];
        print_r($data);
        
        $return_data = $this->score_action($data);
        print_r($return_data);die;
        
        
    }

    
    public function score_action($data){
//         bonus_adjustment
        $create_url = $this->request_url.'bonus_adjustment';
        $return_msg = http($create_url, $data, 'POST');
        if(!is_json($return_msg)){
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);exit;
        }
        
        $return_data = json_decode($return_msg,true);
        return $return_data;
    }
    
    /**
     * @deprecated  创建会员
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name   (address\idnumber\birth)
     */
    public function createMember(){
        $params = I('param.');
        
        $this->paramsCheck($params, array('mobile','openid'), array('mobile','openid'));
        
        $data['member_last_name_cht'] = $params['name']?$params['name']:""; 
        $data['id_number'] = $params['idnumber']?$params['idnumber']:"";
        $data['mobile'] = $params['mobile']?$params['mobile']:"";
        $data['open_id'] = $params['openid']?$params['openid']:"";
        $data['email'] = $params['email']?$params['email']:"";
        $data['gender'] = (1==$params['sex'])?"M":"F";
        
        $create_url = $this->request_url.'register_member';
        $return_msg = http($create_url, $data, 'POST');
        
        if(!is_json($return_msg)){
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);exit;
        }
        
        $return_data = json_decode($return_msg,true);
        if($return_data['result']){
            returnjson(array('code'=>104,'data'=>$return_data['result_message']), $this->returnstyle, $this->callback);exit;
        }
        
        $db = M('mem',$this->admin_arr['pre_table']);
        
        $arr = $db->where(array('vipid'=>$return_data['member_id']))->find();
        if(empty($arr)){
            $save_data['cardno'] = $return_data['number'];//会员卡号
            $save_data['vipid'] = $return_data['member_id'];
            $save_data['level'] = $return_data['member_type'];
            $save_data['usermember'] = $return_data['member_last_name_cht'];
            $save_data['email'] = $return_data['email'];
            $save_data['mobile'] = $return_data['mobile'];
            $save_data['phone'] = $return_data['mobile'];
            $save_data['sex'] = $return_data['gender'];//姓別 (M:男, F:女)
            $save_data['idnumber'] = $return_data['id_number'];
            $save_data['openid'] = $return_data['open_id'];
            $save_data['score_num'] = $return_data['bonus'];
            $save_data['expirationdate'] = $return_data['member_expiry_date'];
            $db->add($arr);
        }
        
        $msg['code']=200;
        $list=array(
            'cardno'=>$return_data['number'],
            'usermember'=>$return_data['member_last_name_cht'],
            'getcarddate'=>date('Y-m-d'),
            'expirationdate'=>$return_data['member_expiry_date'],
            'mobile'=>$params['mobile'],
            'sex'=>$params['sex'],
            'idnumber'=>$params['idnumber']
        );
        
        returnjson($msg,$this->returnstyle,$this->callback);
    }


    /**
     * @deprecated  修改会员信息
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name、cardno
     */
    public function editMember(){
        $params = I('param.');
        
        $this->paramsCheck($params, array('cardno','mobile','openid'), array('cardno','mobile','openid'));
        
        $data['number'] = $params['cardno'];
        $data['mobile'] = $params['mobile'];
        $data['open_id'] = $params['openid'];
        $return_data = $this->memberinfo_action($data);//获取当前用户token
        if($return_data==false || $return_data['token'] == ''){
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);exit;
        }    
        $data['token'] = $return_data['token'];
        $data['email'] = $params['email'];
        $data['member_last_name_cht'] = $params['name'];
        $data['gender'] = (1==$params['sex']) ? 'M' : 'F';
        $data['id_number'] = $params['idnumber'];

        $save_url = $this->request_url.'update_member';
        
        $return_msg = http($save_url, $data, 'POST');
        
        if(!is_json($return_msg)){
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);exit;
        }
        
        $return_data = json_decode($return_msg,true);
        
        if($return_data['result']){
            returnjson(array('code'=>104,'data'=>$return_data['result_message']), $this->returnstyle, $this->callback);exit;
        }
        
        $db=M('mem',$this->admin_arr['pre_table']);
        $rt['mobile']=$params['mobile'];
        $rt['sex']= $params['sex'];
        $rt['idnumber']=$params['idnumber'];
        $rt['usermember']=$params['name'];
        $rt['nickname']=$params['name'];
        $rt['email']=$params['email'];
        $sel=$db->where(array('cardno'=>$params['cardno']))->find();
        if (null == $sel){
            $rt['cardno']=$params['cardno'];
            $sv=$db->add($rt);
        }else{
            unset($rt['usermember']);
            $sv=$db->where(array('cardno'=>$params['cardno']))->save($rt);
        }
        
        $re_data['cardno']=$params['cardno'];
        $re_data['usermember']=$params['name'];
        $re_data['getcarddate']='';//创建时间
        $re_data['expirationdate']=$return_data['member_expiry_date'];//到期时间
        $re_data['mobile']=$params['mobile'];
        $re_data['sex']=$params['sex'];
        $re_data['idnumber']=$params['idnumber'];
        $msg['code']=200;
        $msg['data']=$re_data;
        returnjson($msg, $this->returnstyle, $this->callback);exit;
    }

    /**
     * @deprecated 根据卡号获取会员信息
     * @传入参数   key_admin、sign、card
     */
    public function GetUserinfoByCard(){
        $params = I('param.');
        
        $this->paramsCheck($params, array('cardno'), array('cardno'));
        
        $data['number'] = $params['cardno']?$params['cardno']:"";
        $data['open_id'] = $params['openid']?$params['openid']:"";
        
        $return_data = $this->memberinfo_action($data);
        
        if($return_data['result'] == '0'){
            $data = $this->data_save($return_data);
            $msg['code']=200;
            $msg['data'] = $data;
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * @deprecated 根据手机号获取会员信息
     * @传入参数  key_admin、sign、mobile
     */
    public function GetUserinfoByMobile(){
        $params = I('param.');
        
        $this->paramsCheck($params, array('mobile'), array('mobile'));
        
        $data['mobile'] = $params['mobile']?$params['mobile']:"";
        $data['open_id'] = $params['openid']?$params['openid']:"";
        
        $return_data = $this->memberinfo_action($data);
        
        if($return_data['result'] == '0'){
            $data = $this->data_save($return_data);
            $msg['code']=200;
            $msg['data'] = $data;
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
        
    }

    protected function memberinfo_action($data){
        
        $create_url = $this->request_url.'check_member';
        $return_msg = http($create_url, $data, 'POST');
        if(!is_json($return_msg)){
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);exit;
        }
        
        $return_data = json_decode($return_msg,true);
        return $return_data;
    }
    
    
    protected function data_save($params){
        $db = M('mem',$this->admin_arr['pre_table']);
        $data['cardno'] = $params['number'];
        $data['datetime']=date('Y-m-d H:i:s');
        $data['usermember'] = $params['member_last_name_cht'];
        $data['idnumber']=$params['id_number'];
        $data['email'] = $params['email'];
        $data['mobile'] = $params['mobile'];
        $data['phone'] = $params['mobile'];
        $data['sex'] = ('F'==$params['gender'])?0:1;
        $data['score_num'] = $params['bonus'];
        $data['getcarddate'] = $params['expiry_date'];
        $data['expirationdate'] = $params['member_expiry_date'];
        $data['level'] = $params['member_type_name_cht'];
        $sel=$db->where(array('cardno'=>$data['cardno']))->find();
        if($sel){
            $db->where(array('cardno'=>$data['cardno']))->save($data);
        }else{
            $db->add($data);
        }

        $msg_data['cardno'] = $data['cardno'];
        $msg_data['user'] = $data['usermember'];
        $msg_data['name'] = $data['usermember'];
        $msg_data['cardtype'] = $data['level'];
        $msg_data['email'] = $data['email'];
        $msg_data['getcarddate'] = $data['getcarddate'];
        $msg_data['expirationdate'] = $data['expirationdate'];
        $msg_data['phone'] = $data['mobile'];
        $msg_data['mobile'] = $data['mobile'];
        $msg_data['score'] = $data['score_num'];
        $msg_data['idnumber']=$params['id_number'];
        $msg_data['sex']=('M'==$params['gender']) ? '1' : '0';
        $msg_data['level'] = $params['member_type_name_cht'];
        return $msg_data;
    }
    
    
    
    /**
     * @deprecated 用户积分详细列表
     */
    public function scorelist(){
        $params = I('param.');
        $this->paramsCheck($params, array('cardno','openid'), array('cardno','openid'));
        
        $data['number'] = $params['cardno'];
        $return_data = $this->memberinfo_action($data);//获取当前用户token
        //         print_R($return_data);
        if($return_data['result'] != '0'){
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);exit;
        }
        //         echo "1";die;
        $data['token'] = $return_data['token'];
        $data['mobile'] = $return_data['mobile'];
        $data['open_id'] = $params['openid'];
        $data['return_transactions'] = 'Y';
        $data['return_transactions_from'] = $params['startdate']?date('Y.m.d',$params['startdate']):'';
        $data['return_transactions_to'] = $params['enddate']?date('Y.m.d',$params['enddate']):'';
        $data['return_transactions_offset'] = $params['page'];
        $data['return_transactions_limit'] = $params['limit'];
//         print_r($data);
        
        $score_list_url = $this->request_url.'get_member';
        $return_data = http($score_list_url,$data,'GET');
        
        if(!is_json($return_data)){
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);exit;
        }
        
        $return_data = json_decode($return_data,true);
        
        if($return_data['result'] != '0'){
            returnjson(array('code'=>102,'data'=>$return_data['result']), $this->returnstyle, $this->callback);exit;
        }
        
        if(count($return_data['transactions'])>1){
            foreach($return_data['transactions'] as $k=>$v){
                $arr['date'] = $v['datetime'];
                $arr['description'] = $v['remarks'];
                $arr['score'] = $v['bonus_change'];
                $return_arr[] = $arr;
            }
            $datas['cardno'] = $return_data['number'];
            $datas['scorelist'] = $return_arr;
        }else{
            $datas = array();
        }
        
        returnjson(array('code'=>200,'data'=>$datas), $this->returnstyle, $this->callback);exit;
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
    
    /**
     * 解绑
     */
    public function UnBind(){}
}
