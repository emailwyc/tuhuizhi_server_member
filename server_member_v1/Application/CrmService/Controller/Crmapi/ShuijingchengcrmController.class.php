<?php
namespace CrmService\Controller\Crmapi;

// use CrmService\Controller\CommonController;
use CrmService\Controller\CrminterfaceController;
class ShuijingchengcrmController extends CrmController implements CrminterfaceController
{

    
    
    

    /**
     * @desc    通过卡号获取会员信息
     */
    public function GetUserinfoByCard() {
        //echo '{"code":200,"data":{"cardno":"80000001","user":"www","status":"N","status_description":"06","getcarddate":"11-JUN-14","expirationdate":"10-JUN-15","birthday":"01-JAN-76","company":"","phone":"","mobile":"","address":"","score":"193"},"msg":"SUCCESS."}';die;
        $key_admin=I('key_admin');
        $othersign=I('sign');
        $params['card']=I('card');
        $msg=$this->commonerrorcode;
        if (empty($key_admin)){//没有获取到key_admin
            $msg['code']=1001;
        }elseif (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }
//         else if (false == $this->sign($key_admin,$params,$othersign)){//签名失败
//             $msg['code']=1002;
//         }
        else {
            //验证成功，去获取会员信息
            $crmdata=$this->redis->get('crmservice:admin'.$key_admin);
            $crmdata=json_decode($crmdata,true);
            $db=M('api',$crmdata['pre_table']);
            $sel=$db->where(array('api_type'=>5))->select();
            if (null != $sel && 1==count($sel)){
                $userinfo=$this->getuserinfo_card($sel[0],$crmdata,$sel[0]['request_type'],$params['card']);
            }else{
                $msg['code']=3000;
            }
            if (false==$userinfo){
                $msg['code']=102;
            }else{
                try {
                    $apikeyarr=json_decode($sel[0]['api_response'],true);
                } catch (Exception $e) {
                    $msg['']='';
                }
                $userinfos=apiarr_to_params($userinfo,$apikeyarr);//根据对应的key值，赋给对应的key
                //先获取redis里面的值，如果有值，则跳过保存，如果没有，则保存
                $card=$this->redis->get('crm:'.$crmdata['pre_table'].':'.$userinfos['cardno']);
                $dbmem= M('mem',$crmdata['pre_table']);
                $selmem=$dbmem->where(array('cardno'=>$userinfos['cardno']))->find();
                /**注意：此处用到了事物，避免mysql或redis只有一个存上，而另一个没有保存*/
                //                 if ('' == $card){//如果redis里面没有
                //                     $selmem=$dbmem->where(array('cardno'=>$userinfos['cardno']))->find();
                if (null == $selmem){//如果mysql里面没有
                    $dbmem->startTrans();
                    $data=$userinfos;
                    unset($data['idnumber']);
                    $add=$dbmem->add($data);
                    //$crmcard=$this->redis->set('crm:'.$crmdata['pre_table'].':'.$userinfos['cardno'],'yes');
                    if ($add){
                        $dbmem->commit();
                    }else{
                        $dbmem->rollback();
                    }
                }else{
                    $dbmem->startTrans();
                    $data=$userinfos;
                    unset($data['idnumber']);
                    $save=$dbmem->where(array('cardno'=>$userinfos['cardno']))->save($data);
                    //$crmcard=$this->redis->set('crm:'.$crmdata['pre_table'].':'.$userinfos['cardno'],'yes');
                    if ($save){
                        $dbmem->commit();
                    }else{
                        $dbmem->rollback();
                    }
                }
                //                 }
                unset($userinfos['IDcode']);
                unset($userinfos['IDnumber']);
                $userinfos['user']=$userinfos['usermember'];
                $msg['code']=200;
                $msg['data']=$userinfos;
                /**事物结束*/
            }
    
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * @desc 根据手机号获取信息
     */
    public function GetUserinfoByMobile(){
        $key_admin=I('key_admin');
        $othersign=I('sign');
        $params['mobile']=I('mobile');
        $msg=$this->commonerrorcode;
        if (empty($key_admin)){//没有获取到key_admin
            $msg['code']=1001;
        }elseif (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }
//         else if (false == $this->sign($key_admin,$params,$othersign)){//签名失败
//             $msg['code']=1002;
//         }
        else {
            //验证成功，去获取会员信息
            $crmdata=$this->redis->get('crmservice:admin'.$key_admin);
            $crmdata=json_decode($crmdata,true);
            $db=M('api',$crmdata['pre_table']);
            $sel=$db->where(array('api_type'=>9))->select();
            if (null != $sel && 1==count($sel)){
                $userinfo=$this->getuserinfo_mobile($sel[0],$crmdata,$sel[0]['request_type'],$params['mobile']);
            }else{
                $msg['code']=3000;
            }
            if (false==$userinfo){
                $dbmem= M('mem',$crmdata['pre_table']);
                $sel=$dbmem->field(array('cardno','usermember'=>'user','status_description','getcarddate','expirationdate','birthday','company','phone','mobile','address'))->where(array('phone'=>$params['mobile'],'mobile'=>$params['mobile'],'_logic'=>'or'))->select();
                if (0<count($sel)){
                    $sel[0]['score']=0;
                    $msg['code']=200;
                    $msg['data']=$sel[0];
                }else{
                    $msg['code']=102;
                }
            }else{
                try {
                    $apikeyarr=json_decode($sel[0]['api_response'],true);
                } catch (Exception $e) {
                    $msg['']='';
                }
                $userinfos=apiarr_to_params($userinfo,$apikeyarr);//根据对应的key值，赋给对应的key
                //先获取redis里面的值，如果有值，则跳过保存，如果没有，则保存
                //$card=$this->redis->get('crm:'.$crmdata['pre_table'].':'.$userinfos['cardno']);
                $dbmem= M('mem',$crmdata['pre_table']);
                $selmem=$dbmem->where(array('cardno'=>$userinfos['cardno']))->find();
                /**注意：此处用到了事物，避免mysql或redis只有一个存上，而另一个没有保存*/
                if (null == $selmem){//如果mysql里面也没有
                    $dbmem->startTrans();
                    $data=$userinfos;
                    unset($data['idnumber']);
                    $add=$dbmem->add($data);
                    //$crmcard=$this->redis->set('crm:'.$crmdata['pre_table'].':'.$userinfos['cardno'],'yes');
                    if ($add){
                        $dbmem->commit();
                    }else{
                        $dbmem->rollback();
                    }
                }else{
                    $dbmem->startTrans();
                    $data=$userinfos;
                    unset($data['idnumber']);
                    $save=$dbmem->where(array('cardno'=>$userinfos['cardno']))->save($data);
                    //$crmcard=$this->redis->set('crm:'.$crmdata['pre_table'].':'.$userinfos['cardno'],'yes');
                    if ($save){
                        $dbmem->commit();
                    }else{
                        $dbmem->rollback();
                    }
                }
                unset($userinfos['IDcode']);
                unset($userinfos['IDnumber']);
                $userinfos['user']=$userinfos['usermember'];
                $msg['code']=200;
                $msg['data']=$userinfos;
            }
    
            /**事物结束*/
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    
    }
    
    
    /**A,201,01,__card__,__startdate__,__enddate__,__name__,__sex__,__mobile__,__mobile__,__mobile__,,,
     * 创建会员
     */
    public function createMember(){
        //A,201,80,80201606281556016486,28-JUN-16,28-JUN-18,姓名,M,__mobile__,13489076849,16789087689,28-JUN-09,地址,jfdsjl@a.a
        //echo '{"code": "200","data": {"name": "姓名","card": "21234345465","mobile": "13312344321","card_type": "80","sex": "1","cardsstarttime": "201606060606","cardstoptime": "211606060606","e-mail": "a@a.a"},"msg": "SUCCESS"}';
        $key_admin=I('key_admin');
        $othersign=I('sign');
        $params['mobile']=I('mobile');
        $params['sex']=I('sex');
        $params['idnumber']=I('idnumber');
        $params['name']=I('name');
        if ($params['sex']==null || $params['sex']=='' || $params['sex']=='null'){
            unset($params['sex']);
        }
    
        $msg=$this->commonerrorcode;
        if (empty($key_admin)){//没有获取到key_admin
            $msg['code']=1001;
        }elseif (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }
//         else if (false == $this->sign($key_admin,$params,$othersign)){//签名失败
//             $msg['code']=1002;
//         }
        else{
            //验证成功，去创建会员信息
            $crmdata=$this->redis->get('crmservice:admin'.$key_admin);
            $crmdata=json_decode($crmdata,true);
            $dbmem=M('mem',$crmdata['pre_table']);
            $selmem=$dbmem->where(array('mobile'=>$params['mobile'],'phone'=>$params['mobile'],'_logic'=>'or'))->select();
            if (0 == count($selmem)){
                $db=M('api',$crmdata['pre_table']);
                $sel=$db->where(array('api_type'=>6))->select();
                if (null != $sel && 1==count($sel)){
                    $userinfo=$this->create_member($sel[0],$crmdata,$sel[0]['request_type'],$params);
    
                    $member=array_merge($userinfo,$params);
    
                    $dbmem->startTrans();
                    $add=$dbmem->add($member);
                    if ($add){
                        $dbmem->commit();
                        $msg['code']=200;
                        $msg['data']=$member;
                    }else{
                        $dbmem->rollback();
                        $msg['code']=1011;
                    }
                }else{
                    $msg['code']=3000;
                }
                /**事物结束*/
            }else{
                $msg['code']=1012;
            }
    
    
    
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * @desc    修改会员
     */
    public function editMember(){
        //echo '{"code": "200","data": {"name": "姓名","card": "21234345465","mobile": "13312344321","card_type": "80","sex": "1","cardsstarttime": "201606060606","cardstoptime": "211606060606","e-mail": "a@a.a"},"msg": "SUCCESS"}';die;
        $key_admin=I('key_admin');
        $othersign=I('sign');
        $params['mobile']=I('mobile');
        $params['sex']=I('sex');
        $params['idnumber']=I('idnumber');
        $params['name']=I('name');
        $params['cardno']=I('cardno');
        if ($params['sex']==null || $params['sex']=='' || $params['sex']=='null'){
            unset($params['sex']);
        }
    
        $msg=$this->commonerrorcode;
        if (empty($key_admin)){//没有获取到key_admin
            $msg['code']=1001;
        }elseif (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }
//         else if (false == $this->sign($key_admin,$params,$othersign)){//签名失败
//             $msg['code']=1002;
//         }
        else{
            //验证成功，去创建会员信息
            $crmdata=$this->redis->get('crmservice:admin'.$key_admin);
            $crmdata=json_decode($crmdata,true);
            $db=M('api',$crmdata['pre_table']);
            $sel=$db->where(array('api_type'=>10))->select();
            if (null != $sel && 1==count($sel)){
    
                $dbmem=M('mem',$crmdata['pre_table']);
                $selmem=$dbmem->where(array('mobile'=>$params['mobile'],'phone'=>$params['mobile'],'_logic'=>'or'))->select();
                if (1 >=  count($selmem) && $selmem[0]['cardno']==$params['cardno'] ){
                    $userinfo=$this->edit_member($sel[0],$crmdata,$sel[0]['request_type'],$params);
                    $member=array_merge($userinfo,$params);
    
                    $dbmem->startTrans();
                    $add=$dbmem->where(array('cardno'=>$params['cardno']))->save($member);
                    if ($add){
                        $dbmem->commit();
                        $msg['code']=200;
                        $msg['data']=$member;
                    }else{
                        $dbmem->rollback();
                        $msg['code']=1011;
                    }
                }else{
                    $msg['code']=1012;
                }
    
            }else{
                $msg['code']=3000;
            }
            /**事物结束*/
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * @desc    会员积分扣除
     */
    public function cutScore(){
        $key_admin=I('key_admin');
        $othersign=I('sign');
        $params['cardno']=I('cardno');
        $params['scoreno']=I('scoreno');//-1 1
        $params['why']=I('why');
        //$params['member']=I('name');//会员用户名
        $msg=$this->commonerrorcode;
        if (empty($key_admin)){//没有获取到key_admin
            $msg['code']=1001;
        }elseif (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }
//         else if (false == $this->sign($key_admin,$params,$othersign)){//签名失败
//             $msg['code']=1002;
//         }
        else{
            //验证成功，去创建会员信息
            $crmdata=$this->redis->get('crmservice:admin'.$key_admin);
            $crmdata=json_decode($crmdata,true);
            $db=M('api',$crmdata['pre_table']);
            $api=$db->where(array('api_type'=>7))->select();
            if (null != $api && 1==count($api)){
                $status=$this->cut_score($api[0],$crmdata,$api[0]['request_type'],$params['scoreno'],$params['cardno'],$params['member']);
                if (true==$status){
                    $dbscore=M('score_record',$crmdata['pre_table']);
                    $data['cardno']=$params['cardno'];
                    $data['scorenumber']=$params['scoreno'];
                    $data['why']=$params['why'];
                    $data['cutadd']=1;
                    $scorecode=$this->redis->get($params['cardno'].':scorenumber');
                    if (''!=$scorecode){
                        $data['scorecode']=$scorecode;
                    }
                    $add=$dbscore->add($data);
                    $msg['data']=$data;
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
            }else{
                $msg['code']=3000;
            }
            /**事物结束*/
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * @desc    积分添加
     */
    public function addintegral(){
        $key_admin=I('key_admin');
        $othersign=I('sign');
        $params['cardno']=I('cardno');
        $params['scoreno']=I('scoreno');//-1 1
        $params['scorecode']=I('scorecode');
        $params['why']=I('why');
        $params['membername']=I('membername');
        //$params['member']=I('name');//会员用户名
        $msg=$this->commonerrorcode;
        if (empty($key_admin)){//没有获取到key_admin
            $msg['code']=1001;
        }elseif (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }
//         else if (false == $this->sign($key_admin,$params,$othersign)){//签名失败
//             $msg['code']=1002;
//         }
        else{
            $store=I('store');
            //验证成功，去创建会员信息
            $crmdata=$this->redis->get('crmservice:admin'.$key_admin);
            $crmdata=json_decode($crmdata,true);
    
            $dbscoredb=M('score_record',$crmdata['pre_table']);
            $find=$dbscoredb->where(array('scorecode'=>$params['scorecode']))->find();
            if (null==$find){
                $msg['code']=104;
            }else {
                $db=M('api',$crmdata['pre_table']);
                $api=$db->where(array('api_type'=>8))->select();
                if (null != $api && 1==count($api)){
                    $status=$this->add_score($api[0],$crmdata,$api[0]['request_type'],$params['scoreno'],$params['cardno'],$params['scorecode'],$params['membername']);
                    if (true==$status){
                        $dbscore=M('score_record',$crmdata['pre_table']);
                        $data['cardno']=$params['cardno'];
                        $data['scorenumber']=$params['scoreno'];
                        $data['why']=$params['why'];
                        $data['cutadd']=2;
                        $data['scorecode']=$this->redis->get($params['cardno'].':scorenumber:'.'add');
                        $data['store']=$store?$store:'';
                        $add=$dbscore->add($data);
                        $msg['code']=200;
                    }else{
                        $msg['code']=104;
                    }
                }else{
                    $msg['code']=3000;
                }
                /**事物结束*/
            }
    
    
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    
    /**
     * @deprecated 用户积分详细列表
     */
    public function scorelist(){
    
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
     * 解绑
     */
    public function UnBind(){}
}

?>