<?php
namespace CrmService\Controller\Crmapi;

use Org\Util\String;
use CrmService\Controller\CrminterfaceController;
use CrmService\Controller\CommonController;

class OuyashopcrmController extends CommonController implements CrminterfaceController
{
    private  $url = 'https://ouy.rtmap.com';
     /**
     * @deprecated 根据卡号获取会员信息(等会欧亚根据会员卡ID获取会员信息)
     * @传入参数   key_admin、sign、card
     * 
     */
    public function GetUserinfoByCard(){
        $params['key_admin']=I('key_admin');
        $params['unionid']=I('unionid');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $res=$this->member_info($params);
            $params['member_id']=$res['member_id']; 
            $url=$this->url.'/OyCrmservice.php';
            $params['sign_num']=1;
            $params['sign']=md5(md5('OYMCSIGNOuYa').'sign');
            //             echo $url;
            $params['action']='member_info';
            $result=http($url, $params);
            $return_arr=json_decode($result,true);
            if($return_arr['code']==200){
                $rt=$this->member_action($params['key_admin'] , $return_arr['data'],'','GetUserinfoByCard');
        
                $msg['code']=200;
                $msg['data']=$rt;
            }else{
                $msg=$return_arr;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * @deprecated 根据手机号获取会员信息(等于欧亚绑卡接口)
     * @传入参数  key_admin、sign、mobile
     */
    public function GetUserinfoByMobile(){
        
    }
    
    
    /**
     * 公共类
     */
    protected function member_info($params){
        $admininfo=$this->getMerchant($params['key_admin']);
        $where['unionid']=array('eq',$params['unionid']);
        $where['key_admin']=array('eq',$params['key_admin']);
        $where['default_card']=array('eq',1);
        $where['_logic']='and';
        $db=M('mem',$admininfo['pre_table']);
        $res=$db->where($where)->find();
        if(empty($res)){
            $msg['code']=102;
            echo returnjson($msg,$this->returnstyle,$this->callback);die;
        }
        unset($params['unionid']);
        $return['member_id']=$res['member_id'];
        return $return;
    }
    
    /**
     * @deprecated  创建会员
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name
     */
    public function createMember(){
        $params['key_admin']=I('key_admin');
        $params['name']=I('name');
        $params['sex']=I('sex');
        $params['idnumber']=I('idnumber');
        $params['mobile']=I('mobile');
        $params['type']=I('card');
        $params['unionid']=I('unionid');
        $params['openid']=I('openid');
        $par['Oy_createMember_params']=$params;
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $params['birthday']=I('birth');
            $admininfo=$this->getMerchant($params['key_admin']);
            $params['appid']=$admininfo['wechat_appid'];
            $params['sign_num']=1;
            $params['sign']=md5(md5('OYMCSIGNOuYa').'sign');
            $params['action']='member_create';
            $url=$this->url.'/OyCrmservice.php';
            $result=http($url, $params);
            $return_arr=json_decode($result,true);
            $par['Oy_createMember_curl_return']=$return_arr;
            if($return_arr['code']==200){
                //记录入库
                $arr['cardno']=$return_arr['data']['HYK_NO'];
                $arr['usermember']=$params['name'];
                $arr['sex']=$params['sex'];
                $arr['unionid']=$params['unionid'];
                $arr['member_type']=$return_arr['data']['HYKTYPE'];
                $arr['idnumber']=$params['idnumber'];
                $arr['mobile']=$params['mobile'];
                $arr['unionid']=$params['unionid'];
                $arr['openid']=$params['openid'];
                $arr['member_id']=$return_arr['data']['HYID'];
                $arr['getcarddate']=$return_arr['data']['JKRQ'];
                $arr['expirationdate']=$return_arr['data']['YXQ'];
                $arr['birthday']=$params['birthday'];
                $arr['key_admin']=$params['key_admin'];
                $db=M('mem',$admininfo['pre_table']);
                $db->add($arr);    
                //结束
                $msg['code']=200;
                $msg['data']=$arr;
            }else{
                $msg['code']=$return_arr['code'];
                $msg['msg']=$return_arr['msg'];
            }
        }
        writeOperationLog($par,'zhanghang');
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * @deprecated  修改会员信息
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name、cardno
     */
    public function editMember(){
        $params['key_admin']=I('key_admin');
//         $params['mem_id']=I('member_id');
        $params['name']=I('name');
        $params['unionid']=I('unionid');
        $params['birthday']=I('birth');
        $params['sex']=I('sex');
        $par['Oy_editMember_params']=$params;
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $res=$this->member_info($params);
            $params['mem_id']=$res['member_id'];
            $params['idnumber']=I('idnumber');
            $params['mobile']=I('mobile');
            $params['sign_num']=1;
            $params['sign']=md5(md5('OYMCSIGNOuYa').'sign');
            $params['action']='member_save';
            $url=$this->url.'/OyCrmservice.php';
            $result=http($url, $params);
            $return_arr=json_decode($result,true);
            $par['Oy_editMember_curl_return']=$return_arr;
            if($return_arr['code']==200){
                if($params['mobile'] != ''){
                    $arr['mobile']=$params['mobile'];
                }
                if($params['idnumber'] != ''){
                    $arr['idnumber']=$params['idnumber'];
                }
                if($params['birthday'] != ''){
                    $arr['birthday']=$params['birthday'];
                }
                $arr['sex']=$params['sex'];
                $arr['usermember']=$params['name'];
                $admininfo=$this->getMerchant($params['key_admin']);
                $db=M('mem',$admininfo['pre_table']);
                $res=$db->where(array('member_id'=>array('eq',$params['mem_id'])))->find();
                if($res){
                    $db->where(array('member_id'=>array('eq',$params['mem_id'])))->save($arr);
                }else{
                    $arr['member_id']=$params['mem_id'];
                    $arr['key_admin']=$params['key_admin'];
                    $db->add($arr);
                }
                $msg['code']=200;
            }else{
                $msg=$return_arr;
            }
        }
        writeOperationLog($par,'zhanghang');
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    /**
     * @deprecated  积分扣除
     * @传入参数  key_admin、sign、cardno、scoreno、why
     */
    public function cutScore(){
        $params['key_admin']=I('key_admin');
        $params['score']=I('scoreno');
        $params['unionid']=I('unionid');
        $params['cardno']=I('cardno');
        $params['why']=I('why');
        $params['status']='subtract';
        $par['Oy_cutScore_params']=$params;
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $res=$this->member_info($params);
            $params['mem_id']=$res['member_id'];
            $params['sign_num']=1;
            $params['sign']=md5(md5('OYMCSIGNOuYa').'sign');
            $params['action']='member_cut_score';
            $url=$this->url.'/OyCrmservice.php';
            $result=http($url, $params);
            $return_arr=json_decode($result,true);
            $par['Oy_cutScore_curl_return']=$return_arr;
            if($return_arr['code']==200){
                $data['cardno']=$params['cardno'];
                $data['scorenumber']=$params['score'];
                $data['why']=$params['why'];
                $data['scorecode']=$return_arr['data']['scorecode'];
                $data['cutadd']=1;
                $admininfo=$this->getMerchant($params['key_admin']);
                $db=M('score_record',$admininfo['pre_table']);
                $add=$db->add($data);
                
                $msg['code']=200;
            }else{
                $msg=$return_arr;
            }
        }
        writeOperationLog($par,'zhanghang');
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    
    /**
     * @deprecated  积分添加
     * @传入参数  key_admin、sign、cardno、scoreno、scorecode、why、membername
     */
    public function addintegral(){
        $params['key_admin']=I('key_admin');
        $params['score']=I('scoreno');
        $params['unionid']=I('unionid');
        $params['cardno']=I('cardno');
        $params['why']=I('why');
        $params['status']='add';
        $par['Oy_addintegral_params']=$params;
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $res=$this->member_info($params);
            $params['mem_id']=$res['member_id'];
            $params['sign_num']=1;
            $params['sign']=md5(md5('OYMCSIGNOuYa').'sign');
            $params['action']='member_cut_score';
            $url=$this->url.'/OyCrmservice.php';
            $result=http($url, $params);
            $return_arr=json_decode($result,true);
            $par['Oy_addintegral_curl_return']=$return_arr;
            if($return_arr['code']==200){
                $data['cardno']=$params['cardno'];
                $data['scorenumber']=$params['score'];
                $data['why']=$params['why'];
                $data['scorecode']=$return_arr['data']['scorecode'];
                $data['cutadd']=2;
                $admininfo=$this->getMerchant($params['key_admin']);
                $db=M('score_record',$admininfo['pre_table']);
                $add=$db->add($data);
                
                $msg['code']=200;
            }else{
                $msg=$return_arr;
            }
        }
        writeOperationLog($par,'zhanghang');
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * @deprecated 用户积分详细列表
     */
    public function scorelist(){
        $params['key_admin']=I('key_admin');
        $params['mem_id']=I('member_id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $params['page']=I('page');
            $params['lines']=I('lines');
            $params['sign_num']=1;
            $params['sign']=md5(md5('OYMCSIGNOuYa').'sign');
            $params['action']='member_score_list';
            $url=$this->url.'/OyCrmservice.php';
            $result=http($url, $params);
            $return_arr=json_decode($result,true);
            if($return_arr['code']==200){
                foreach($return_arr['data'] as $k=>$v){
                    $arr['description']=$v['ZY'];
                    $arr['score']=$v['TZJF'];
                    $arr['scorecode']=$v['JLBH'];
                    $arr['date']=$v['ZXRQ'];
                    $res[]=$arr;
                }
                
                $data['cardno']=$params['mem_id'];
                $data['scorelist']=$res;
                
                $msg['code']=200;
                $msg['data']=$data;
            }else{
                $msg=$return_arr;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 会员卡ID获取消费金额记录
     */
    public function UserinfoByConsumptionList(){
        $params['key_admin']=I('key_admin');
        $params['mem_id']=I('member_id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $params['time_num']=I('time_num')?I('time_num'):12;
            $params['page']=I('page');
            $params['lines']=I('lines');
            $params['sign_num']=1;
            $params['sign']=md5(md5('OYMCSIGNOuYa').'sign');
            $params['action']='member_consumption_list';
            $url=$this->url.'/OyCrmservice.php';
            $result=http($url, $params);
            $return_arr=json_decode($result,true);
            if($return_arr['code']==200){
                $msg['code']=200;
                $i=0;
                foreach($return_arr['data'] as $k=>$v){
                    $res['cons_no']=$v['XFJLID'];
                    $res['time']=$v['XFSJ'];
                    $res['order_shop_no']=$v['SKTNO'];
                    $res['order_id']=$v['JLBH'];
                    $res['store']=$v['MDMC'];
                    $res['score_num']=$v['JF_NUM'];
                    $data[$i]=$res;
                    foreach($v['data'] as $key=>$val){
                        $data[$i]['money_num']=$data[$i]['money_num']+$val['XSJE'];
                        $ret_arr['cons_no']=$val['XFJLID'];
                        $ret_arr['time']=$val['XFSJ'];
                        $ret_arr['order_shop_no']=$val['SKTNO'];
                        $ret_arr['order_id']=$val['JLBH'];
                        $ret_arr['store']=$val['MDMC'];
                        $ret_arr['shop_no']=$val['SPDM'];
                        $ret_arr['shopname']=$val['SPMC'];
                        $ret_arr['sale_money']=$val['XSJE'];
                        $ret_arr['number']=$val['XSSL'];
                        $ret_arr['discount_score']=$val['ZKJE'];
                        $ret_arr['cons_score']=$val['JF'];
                        $res_arr[]=$ret_arr;
                        $data[$i]['data']=$res_arr;
                    }
                    $i++;
                }                
                $msg['data']=$data;
            }else{
                $msg=$return_arr;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 绑卡接口
     */
    public function UserinfoByTie(){
        $params['key_admin']=I('key_admin');
        $params['mobile']=I('mobile');
        $params['cardno']=I('cardno');
        $params['unionid']=I('unionid');
        $params['appid']=I('appid');
        $params['openid']=I('openid');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $url=$this->url.'/OyCrmservice.php';
            //             echo $url;
            $params['action']='member_info_tie';
            $params['sign_num']=1;
            $params['sign']=md5(md5('OYMCSIGNOuYa').'sign');
            $result=http($url, $params);
            $return_arr=json_decode($result,true);
            if($return_arr['code']==200){
                $rt=$this->member_action($params['key_admin'],$return_arr['data'],$params['unionid'],'UserinfoByTie');
                
                $msg['code']=200;
                $msg['data']=$rt;
            }else{
                $msg=$return_arr;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * unionID获取绑卡会员列表
     */
    public function GetUserlistByUID(){
        $params['key_admin']=I('key_admin');
        $params['unionid']=I('unionid');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $params['sign_num']=1;
            $params['sign']=md5(md5('OYMCSIGNOuYa').'sign');
            $params['action']='uid_mem_list';
            $url=$this->url.'/OyCrmservice.php';
            $result=http($url, $params);
            $return_arr=json_decode($result,true);
            if($return_arr['code']==200){
                foreach($return_arr['data'] as $k=>$v){
                    $rt=$this->member_action($params['key_admin'], $v,$params['unionid'],'GetUserlistByUID');
                    $data[]=$rt;
                }
                $msg['code']=200;
                $msg['data']=$data;
            }else{
                $msg=$return_arr;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
   /**
    * 根据会员卡ID获取用户信息
    */ 
    public function GetUserinfoByID(){
        $params['key_admin']=I('key_admin');
        $params['member_id']=I('member_id');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $url=$this->url.'/OyCrmservice.php';
            $params['sign_num']=1;
            $params['sign']=md5(md5('OYMCSIGNOuYa').'sign');
            //             echo $url;
            $params['action']='member_info';
            $result=http($url, $params);
            $return_arr=json_decode($result,true);
            if($return_arr['code']==200){
                $rt=$this->member_action($params['key_admin'] , $return_arr['data'],'','GetUserinfoByID');

                $msg['code']=200;
                $msg['data']=$rt;
            }else{
                $msg=$return_arr;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
   /**
    * 解除会员绑定
    */
    public function member_untie(){
        $params['unionid']=I('unionid');
        $params['member_id']=I('member_id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $params['sign_num']=1;
            $params['sign']=md5(md5('OYMCSIGNOuYa').'sign');
            //             echo $url;
            $params['action']='member_untie';
            $url=$this->url.'/OyCrmservice.php';
            $result=http($url, $params);
            $return_arr=json_decode($result,true);
            if($return_arr['code']==200){
                $msg['code']=200;
            }else{
                $msg=$return_arr;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 获取卡类别列表
     */
    public function cardtype_list(){
        $params['sign_num']=1;
        $params['sign']=md5(md5('OYMCSIGNOuYa').'sign');
        //             echo $url;
        $params['action']='card_type_list';
        $url=$this->url.'/OyCrmservice.php';
        $result=http($url, $params);
        $return_arr=json_decode($result,true);
        if($return_arr['code']==200){
            foreach ($return_arr['data'] as $k=>$v){
                $res['cardtype_name']=$v['HYKNAME'];
                $res['cardtype_id']=$v['HYKTYPE'];
                $return_res[]=$res;
            }
            $msg['code']=200;
            $msg['data']=$return_res;
        }else{
            $msg['code']=$return_arr['code'];
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    protected function member_action($key_admin,$return_arr,$unionid='',$action){
        $admininfo=$this->getMerchant($key_admin);
        $db=M('mem',$admininfo['pre_table']);
        $rt['usermember']=$return_arr['HY_NAME'];
        $rt['status']=$return_arr['STATUS'];
        $rt['getcarddate']=$return_arr['JKRQ'];
        $rt['expirationdate']=$return_arr['YXQ'];//到期时间
        $rt['idnumber']=$return_arr['SFZBH'];
        $rt['phone']=$return_arr['SJHM'];
        $rt['mobile']=$return_arr['SJHM'];
        $rt['member_type']=$return_arr['HYKTYPE'];
        $rt['score_num']=$return_arr['WCLJF'];//会员积分
        $rt['birthday']=$return_arr['CSRQ'];
        $rt['sex']=$return_arr['SEX'];
        $rt['member_id']=$return_arr['HYID'];
        $rt['cardno']=$return_arr['HYK_NO'];
        $rt['key_admin']=$key_admin;
        if($unionid != ''){
            $rt['unionid']=$unionid;
        }
        $par['Ouya_Member_params_'.$action]=$rt;
        $sel=$db->where(array('member_id'=>$return_arr['HYID']))->find();
        if (null == $sel){
            $sv=$db->add($rt);
        }else{
            $sv=$db->where(array('member_id'=>$return_arr['HYID']))->save($rt);
        }
        
        $par['Ouya_Member_sql_'.$action]=$db->_sql();
        writeOperationLog($par,'zhanghang');
        $rt['name']=$rt['usermember'];
        $rt['birth']=$rt['birthday'];
        return $rt;
    }
    /**
     * 欧亚卖场获取小票
     */
    public function billInfo(){
        $params['Jlbh']=I('ticket_no');
        $params['skt']=I('pos_no');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $params['md']='OYMC';
            $url=$this->url.'/info/XSJL/'.$params['skt'].'/'.$params['Jlbh'].'/'.$params['md'];
//             echo $url;
            $result=http($url, $params);
            if(is_json($result)){
                $msg['code']=200;
                $arr=json_decode($result,true);
                $res['tran_time']=$arr[0]['Jysj'];
                $res['amount']=$arr[0]['Skje'];
                $res['act_amount']=$arr[0]['Xsje'];
                $res['ticket_no']=$arr[0]['Jlbh'];
                $res['pos_no']=$arr[0]['skt'];
                
                $msg['data']=$res;
            }else{
                $msg['code']=104;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    public function GetUserinfoByOpenid(){

    }
}
