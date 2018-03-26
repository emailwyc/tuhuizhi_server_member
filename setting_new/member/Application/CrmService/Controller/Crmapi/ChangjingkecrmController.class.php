<?php
namespace CrmService\Controller\Crmapi;

use CrmService\Controller\CrminterfaceController;
use CrmService\Controller\CommonController;

class ChangjingkecrmController extends CommonController implements CrminterfaceController
{
    public  $api_url = '/crm-web-opr/';
//     public  $api_url = 'http://211.157.182.226:8888/rtscrm/crm-web-opr';
//     public  $marketId = 12556;
    /**
     * 注册会员
     */
    public function createMember(){
        $params['mobile'] = I('mobile');
        $params['key_admin']=I('key_admin');
        
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['name'] = I('name');
        $params['email']=I('email');
        $params['idcard'] = I('idnumber');
        $params['sex']=I('sex');
        $params['address'] = I('address');
        $params['sourceType'] = 2;
        $params['marketId'] = $this->marketId_info($params['key_admin']);
        $params['birthday'] = I('birth');
        $params['sourceId'] = I('openid');
        $params['regionId'] = I('area');
        $params['sex'] = (0 == $params['sex']) ? "F" : "M";
        if($params['birthday']){
            $params['birthday'] = date('Ymd',strtotime($params['birthday']));
        }
        $params['timestamp'] = time();
        $key_value = json_decode($this->marketId_info($params['key_admin'],'crmkeyandsecret'),true);
        $params['key'] = $key_value['key'];
        $sign = $this->sign_action($params, $key_value['secret']);
        $create_url = C('CJKDOMAIN_TWO').$this->api_url.'/api/v1/member/create?key='.$params['key'].'&sign='.$sign.'&timestamp='.$params['timestamp'];
        $param = json_encode($params);
        $result = http_auth($create_url, $param, 'POST','',array('Content-Type: application/json; charset=utf-8'),true);
//         print_r($result);die;
        if(!is_json($result)) {
            returnjson(array('code'=>104),$this->returnstyle,$this->callback);
        }
        
        $result_array = json_decode($result,true);
//         print_r($result_array);die;
        if($result_array['status'] != 200){
            if($result_array['status'] == 402){
                returnjson(array('code'=>2001),$this->returnstyle,$this->callback);
            }
            if($result_array['status'] == 100){
                $code = 104;
            }else if($result_array['status'] == 301){
                $code = 2001;
            }else if($result_array['status'] == 401){
                $code = 1050;
            }else if($result_array['status'] == 403){
                $code = 1049;
            }else if($result_array['status'] == 404){
                $code = 2001;
            }else if($result_array['status'] == 405){
                $code = 1812;
            }else{
                $code = 104;
            }
            returnjson(array('code'=>$code),$this->returnstyle,$this->callback);
        }
        
        $data['cardno']=$result_array['data']['cardNo'];
        $data['usermember']=$result_array['data']['name'];
        $data['mobile']=$result_array['data']['mobile'];
        $data['sex']=$result_array['data']['sex'];
        $data['phone']=$result_array['data']['mobile'];
        $data['openid']=$params['sourceId'];
        $data['address']=$result_array['data']['address'];
        $data['email']=$result_array['data']['email'];
        $data['score_num']=$params['scoreBalance'];
        $data['getcarddate']=date('Y-m-d');
        $data['birthday']=$params['birthday'];
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem',$admininfo['pre_table']);
        $add=$db->add($data);
        $msg['code']=200;
        $list=array(
            'cardno'=>$data['cardno'],
            'usermember'=>$data['usermember'],
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
    public function editMember(){
        
        $params['cardNo'] = I('cardno');
        $params['key_admin']=I('key_admin');
        
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['name'] = I('name');
        $params['email']=I('email');
        $params['idcard'] = I('idnumber');
        $params['sex']=I('sex');
        $params['address'] = I('address');
        $params['sourceType'] = 2;
        $params['marketId'] = $this->marketId_info($params['key_admin']);
        $params['birthday'] = I('birth');
        $params['sourceId'] = I('openid');
        $params['regionId'] = I('area');
        $params['sex'] = (0 == $params['sex']) ? "F" : "M";
        if($params['birthday']){
            $birthday = strtotime($params['birthday'])?strtotime($params['birthday']):$params['birthday'];
            $params['birthday'] = date('Ymd',$birthday);
        }
        $params['timestamp'] = time();
        $key_value = json_decode($this->marketId_info($params['key_admin'],'crmkeyandsecret'),true);
        $params['key'] = $key_value['key'];
        $sign = $this->sign_action($params, $key_value['secret']);
        
        $create_url = C('CJKDOMAIN_TWO').$this->api_url.'/api/v1/member/update?key='.$params['key'].'&sign='.$sign.'&timestamp='.$params['timestamp'];
        $result = http_auth($create_url, json_encode($params), 'POST','',array('Content-Type: application/json; charset=utf-8'),true);
//         print_r($result);die;
        if(!is_json($result)) {
            returnjson(array('code'=>104),$this->returnstyle,$this->callback);
        }
        
        $result_array = json_decode($result,true);
//         print_r($result_array);die;
        if($result_array['status'] != 200){
            if($result_array['status'] == 100){
                $code = 104;
            }else if($result_array['status'] == 301){
                $code = 2001;
            }else if($result_array['status'] == 401){
                $code = 1050;
            }else if($result_array['status'] == 402){
                $code = 2003;
            }else if($result_array['status'] == 403){
                $code = 1049;
            }else if($result_array['status'] == 404){
                $code = 2001;
            }else if($result_array['status'] == 405){
                $code = 1812;
            }else{
                $code = 104;
            } 
            returnjson(array('code'=>$code),$this->returnstyle,$this->callback);
        }else{
            $msg['code']=200;
        }
       
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 卡号获取会员信息
     */
    public function GetUserinfoByCard(){
        
        $params['cardNo'] = I('card');
        $params['key_admin']=I('key_admin');
        
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        $info = $this->UserinfoAction($params);
        
        returnjson(array('code'=>200,'data'=>$info),$this->returnstyle,$this->callback);
    }
    
    public function UserinfoAction($params){
        $params['marketId'] = $this->marketId_info($params['key_admin']);
        $params['timestamp'] = time();
        $key_value = json_decode($this->marketId_info($params['key_admin'],'crmkeyandsecret'),true);
        $params['key'] = $key_value['key'];
        $sign = $this->sign_action($params, $key_value['secret']);
        
        $create_url = C('CJKDOMAIN_TWO').$this->api_url.'/api/v1/member/info?key='.$params['key'].'&sign='.$sign.'&timestamp='.$params['timestamp'];
//         print_r($params);die;
        $result = http_auth($create_url, json_encode($params), 'POST','',array('Content-Type: application/json; charset=utf-8'),true);
        
        if(!is_json($result)) {
            returnjson(array('code'=>104),$this->returnstyle,$this->callback);
        }
        
        $result_array = json_decode($result,true);
//         print_r($result_array);die;
        if($result_array['status'] != 200){
            returnjson(array('code'=>104),$this->returnstyle,$this->callback);
        }
        
        $data['cardno'] = $result_array['data']['cardNo'];
        $data['usermember'] = $result_array['data']['name'];
        $data['sex'] = $result_array['data']['sex'];
        $data['email'] = $result_array['data']['email']?$result_array['data']['email']:'';
        $data['address'] = $result_array['data']['address']?$result_array['data']['address']:'';
        $data['level'] = $result_array['data']['grade']?$result_array['data']['grade']:'';
        $data['birthday'] = $result_array['data']['birthday']?$result_array['data']['birthday']:'';
        $data['mobile'] = $result_array['data']['mobile'];
        $data['phone'] = $result_array['data']['mobile'];
        $data['score_num'] = $result_array['data']['scoreBalance'];
//         $data['cardno'] = $result_array['data']['cardNo'];
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem',$admininfo['pre_table']);
        $info = $db->where(array('cardno'=>$data['cardno']))->find();
        if($info){
            $db->where(array('cardno'=>$data['cardno']))->save($data);
        }else{
            $db->add($data);
        }
        
        $arr['cardno']=$data['cardno'];//卡号
        $arr['user']=$data['usermember'];//会员卡用户名
        $arr['name']=$data['usermember'];//会员卡用户名
        $arr['cardtype']=$data['level'];//会员卡类别
        $arr['birthday']=$data['birthday']?date('Y-m-d',$data['birthday']/1000):'';//会员生日
        $arr['birth']=$data['birthday']?date('Y-m-d',$data['birthday']/1000):'';//会员生日
        $arr['phone']=$data['mobile'];//会员手机号
        $arr['mobile']=$data['mobile'];//会员手机号
        $arr['email'] = $data['email'];
        $arr['address']=$data['address'];//会员地址
        $arr['score']=$data['score_num'];//会员积分
        $arr['area']=$result_array['data']['areaId'];//会员积分
        $arr['city']=$result_array['data']['cityId'];//会员积分
        $arr['province']=$result_array['data']['provinceId'];//会员积分
        $arr['areaname']=$result_array['data']['areaName'];//会员积分
        $arr['cityname']=$result_array['data']['cityName'];//会员积分
        $arr['provincename']=$result_array['data']['provinceName'];//会员积分
        $arr['sex']=('F' == $data['sex']) ? "0" : "1";;//会员积分
        return $arr;
    }
    
    
    /**
     * 手机号获取会员信息
     */
    public function GetUserinfoByMobile(){
        
        $params['mobile'] = I('mobile');
        $params['key_admin']=I('key_admin');
        
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        $info = $this->UserinfoAction($params);
        
        returnjson(array('code'=>200,'data'=>$info),$this->returnstyle,$this->callback);
        
    }
    
    /**
     * 手机号获取会员信息
     */
    public function GetUserinfoByOpenid(){
        $params['openid'] = I('openid');
        $params['key_admin'] = I('key_admin');
        if(in_array('',$params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem',$admininfo['pre_table']);
        
        $data = $db->where(array('openid'=>$params['openid']))->find();
        
        if(!$data){
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }
        
        $params['cardNo'] = $data['cardno'];
        unset($params['openid']);
        $info = $this->UserinfoAction($params);
        
        returnjson(array('code'=>200,'data'=>$info),$this->returnstyle,$this->callback);
    }
    
    //获取商户marketID
    public function marketId_info($key_admin,$function_name = ''){
        
        $function_name = $function_name?$function_name:'crmmarketid';
        $admininfo=$this->getMerchant($key_admin);
        
        $market_data=$this->GetOneAmindefault($admininfo['pre_table'], $key_admin, $function_name);
        
        if($market_data['function_name']==''){
            returnjson(array('code'=>1814),$this->returnstyle,$this->callback);
        }
        
        return $market_data['function_name'];
    }
    
    
    
    /**
     * 增加积分
     */
    public function addintegral(){
        $params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $params['scoreno']= I('scoreno');
        $params['why']=I('why');
        if (in_array('',$params)){//获取的参数不完整
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['scorecode']=I('scorecode');
        $params['membername']=I('membername');
        $params['scoreno'] = abs((float)$params['scoreno']);
        $msg = $this->score_action($params);
        
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    public function score_action($params){
        $pa['marketId']=$this->marketId_info($params['key_admin']);
        $pa['cardNo']=$params['cardno'];
        $pa['score']=$params['scoreno'];
        $pa['comment']=$params['why'];
        $pa['timestamp'] = time();
        $key_value = json_decode($this->marketId_info($params['key_admin'],'crmkeyandsecret'),true);
        $pa['key'] = $key_value['key'];
        $sign = $this->sign_action($pa, $key_value['secret']);
        
        $addintegral_url=C('CJKDOMAIN_TWO').$this->api_url.'/api/v1/score/adjust?key='.$pa['key'].'&sign='.$sign.'&timestamp='.$pa['timestamp'];
        $adjust_str = json_encode($pa);
        $adjust_one = substr($adjust_str,0,strpos($adjust_str,'score'));
        $adjust_two = substr($adjust_str,strpos($adjust_str,'score'));
        $adjust_data = $adjust_one.'score":'.$params['scoreno'].substr($adjust_two, strpos($adjust_two, ','));

        $result = http_auth($addintegral_url, $adjust_data, 'POST','',array('Content-Type: application/json; charset=utf-8'),true);
        $par1['addjifen_json_turn'] = $result;
        writeOperationLog($par1, 'newIntegral');
        if(!is_json($result)) {
            returnjson(array('code'=>104),$this->returnstyle,$this->callback);
        }
        
        $return = json_decode($result,true);
//         print_r($return);die;
        if($return['status'] == 100){
            returnjson(array('code'=>1082,'msg'=>'有点小问题哦～稍等一会儿再试试'),$this->returnstyle,$this->callback);
        }
        
        if($return['status'] != 200){
            echo returnjson(array('code'=>104),$this->returnstyle,$this->callback);
        }
        
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
        return $msg;
    }
    
    
    /**
     * 扣减积分
     */
    public function cutScore(){
        $params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $params['scoreno']='-'.abs(I('scoreno'));
        $params['why']=I('why');
        if (in_array('',$params)){//获取的参数不完整
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['scorecode']=I('scorecode');
        $params['membername']=I('membername');
        
        $msg = $this->score_action($params);
        
        echo returnjson($msg,$this->returnstyle,$this->callback);
        
    }
    
    
    /**
     * 积分记录
     */
    public function scorelist(){
        $params['key_admin']=I('key_admin');
        $params['cardNo']=I('cardno');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
            $params['startDate'] = I('startdate')?I('startdate'):strtotime("-1 month");
            $params['endDate'] = I('enddate')?I('enddate'):time();
            $params['startDate'] = date('Y-m-d H:i:s',$params['startDate']);
            $params['endDate'] = date('Y-m-d H:i:s',$params['endDate']);
            $params['pageSize'] = I('lines')?I('lines'):10;
            $params['pageNum'] = I('page')?I('page'):1;
            $params['marketId']=$this->marketId_info($params['key_admin']);
            $params['timestamp'] = time();
            $key_value = json_decode($this->marketId_info($params['key_admin'],'crmkeyandsecret'),true);
            $params['key'] = $key_value['key'];
            $sign = $this->sign_action($params, $key_value['secret']);
            $scorelist_url = C('CJKDOMAIN_TWO').$this->api_url.'/api/v1/score/flowlist?key='.$params['key'].'&sign='.$sign.'&timestamp='.$params['timestamp'];

            $result = http_auth($scorelist_url, json_encode($params), 'POST','',array('Content-Type: application/json; charset=utf-8'),true);
            if(empty($result) || !is_string($result)) {
                returnjson(array('code'=>1000),$this->returnstyle,$this->callback);
            }

            $result_array = json_decode($result,true);
            if ($result_array['status'] != 200){
                returnjson(array('code'=>102),$this->returnstyle,$this->callback);
            }

            foreach($result_array['data']['list'] as $k=>$v){
                $data['date'] =$v['createTime'];
                $data['description'] = $v['comment'];
                $data['score'] = $v['score'];
                $scorelist[] = $data;
            }
            
            $msg['code'] = 200;
            $msg['data'] = array(
                'cardno'=>$params['cardNo'],
                'scorelist'=>$scorelist
            );
        }
       returnjson($msg,$this->returnstyle,$this->callback);
        
    }
    
    /**
     * @deprecated 欧亚卖场
     * @传入参数 key_admin、sign 、skt、Jlbh、md
     */
    public function billInfo(){}
    
    
    /**
     * 省市区
     */
    public function Provincialcity(){
        $params['id'] = I('id');
        $params['key_admin'] = I('key_admin');
        $params['timestamp'] = time();
        $key_value = json_decode($this->marketId_info($params['key_admin'],'crmkeyandsecret'),true);
        $params['key'] = $key_value['key'];
        $params['sign'] = $this->sign_action($params, $key_value['secret']);
        $create_url = C('CJKDOMAIN_TWO').$this->api_url.'/api/v1/region/region';

        $result = http($create_url, $params, 'GET');
        
        if(!is_json($result)) {
            returnjson(array('code'=>104),$this->returnstyle,$this->callback);
        }
        
        $result_array = json_decode($result,true);
//         print_r($result_array);die;
        if($result_array['code'] != 0){
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }
        
        returnjson(array('code'=>200,'data'=>$result_array['data']),$this->returnstyle,$this->callback);
    }
    
    
    public function sign_action($params,$secret){
//         $params['timestamp'] = time();
//         $params['key'] = '5b253ed5cf8445aca98dd6a42ff6fd2c';
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
        $str .= '&secret='.$secret; 
        
        return strtoupper(md5($str));
    }
    
    public function marketid_del(){
        $params['key_admin'] = I('key_admin');
        $params['function_name'] = I('function_name');
        $this->redis->del('admin:default:one:'.$params['function_name'].':'.$params['key_admin']);
    }
    
    /**
     * 解绑
     */
    public function UnBind(){}
}

?>
