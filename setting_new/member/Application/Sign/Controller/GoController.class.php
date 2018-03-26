<?php
namespace Sign\Controller;


/**
 * 用户签到接口部分，签到不用openid或uid做唯一性判断，平台的签到按卡号判断
 * @author ut
 *
 */
class GoController extends CommonController
{
    
    
    
    /**
     * @deprecated    判断当前用户唯一标识&今天是否签到
     */
    public function check_signed(){
        $params['key_admin']=I('key_admin');
        $params['uid']=I('uid');//用户唯一标识符
        $msg=$this->commonerrorcode;
        if (in_array('',$params)){
            $msg['code']=100;
        }else{
            $admininfo=$this->getMerchant($params['key_admin']);
            $memberinfo=$this->FindMember($params['uid'], $admininfo['pre_table']);
            $issign=$this->CheckisSign($memberinfo['cardno'], $admininfo['id']);
            if (null == $issign){//没有签到
                $msg['code']=1046;
            }else{//已经签到
                $msg['code']=1045;
                $msg['data']=$issign;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    /**
     * @deprecated    签到动作接口
     */
    public function do_sign(){
        $params['key_admin']=I('key_admin');
        $params['uid']=I('uid');//用户唯一标识符
        $msg=$this->commonerrorcode;
        if (in_array('',$params)){
            $msg['code']=100;
        }else{
            $params['cardno']=I('cardno');
            $admininfo=$this->getMerchant($params['key_admin']);//查询商户信息
            $memberinfo=$this->FindMember($params['uid'], $admininfo['pre_table'],$params['cardno']);//查询用户信息
            $issign=$this->CheckisSign($memberinfo['cardno'], $admininfo['id']);//查询“今天”有没有签到
            if (null == $issign){//如果今天没有签到，则签到送积分
                $dblast=M('last_history','sign_');
                $dbhistory=M('history','sign_');
                $find=$dblast->where(array('cardno'=>$memberinfo['cardno'],'adminid'=>$admininfo['id']))->find();//查询此用户“之前”有没有签过到
                
                $dblast->startTrans();//因为要操作两张表，故添加事务
                $dbhistory->startTrans();//因为要操作两张表，故添加事务
                
                if (null == $find){//如果之前没有签到，则添加操作
                    $datalast['cardno']=$memberinfo['cardno'];
                    $datalast['lastdate']=date('Y-m-d');
                    $datalast['totalday']=1;
                    $datalast['adminid']=$admininfo['id'];
                    $savedata=$dblast->add($datalast);
                }else{//如果之前有数据，则修改操作
                    if ($find['lastdate'] == date("Y-m-d",strtotime("-1 day"))){//如果是昨天
                        $datalast['lastdate']=date('Y-m-d');
                        $datalast['totalday']=$find['totalday']+1;
                    }else{
                        $datalast['lastdate']=date('Y-m-d');
                        $datalast['totalday']=1;
                    }
                    $savedata=$dblast->where(array('cardno'=>$memberinfo['cardno']))->save($datalast);
                }
                if ($savedata !== false){//如果记录成功，则记录本次签到日志记录
                    $score=$this->GetSignScore($params['key_admin'], $admininfo['pre_table']);//获取积分
                    
                    $data['cardno']=$memberinfo['cardno'];
                    $data['signdate']=date('Y-m-d');
                    $data['scores']=$score;
                    $data['adminid']=$admininfo['id'];
                    
                    $add=$dbhistory->add($data);
                    
                    if ($add){//如果签到日志表成功，则请求积分增加接口，调用积分接口放在最后，防止积分添加成功，但操作数据库失败，还要调用积分扣除接口
                        //调用积分增加接口，增加积分
                        $addscore['key_admin']=$params['key_admin'];
                        $addscore['cardno']=$memberinfo['cardno'];
                        $addscore['scoreno']=$score;
                        $addscore['why']='签到送积分';
                        $addscore['scorecode']=date('Y-m-d');
                        $addscore['membername']=$memberinfo['usermember'];
                        $addscore['sign_key']=$admininfo['signkey'];
                        $addscore['unionid']=I('unionid');
                        $addscore['sign']=sign($addscore);
                        $url=C('DOMAIN').'/CrmService/OutputApi/Index/addintegral';//'https://mem.rtmap.com/CrmService/OutputApi/Index/addintegral';
                        unset($addscore['sign_key']);
                        $return=curl_https($url,$addscore,array(),60);
                        if (is_json($return)){
                            $return=json_decode($return,true);
                            if (200 == $return['code']){
                                $dblast->commit();
                                $dbhistory->commit();
                                $msg['code']=200;
                                $msg['data']=array('score'=>$score);
                            }else{
                                $dblast->rollback();
                                $dbhistory->rollback();
                                $msg['code']=104;
                                $msg['data']=4;
                            }
                        }else{
                            $dblast->rollback();
                            $dbhistory->rollback();
                            $msg['code']=104;
                            $msg['data']=3;
                        }
                    }else {
                        $dblast->rollback();
                        $dbhistory->rollback();
                        $msg['code']=104;
                        $msg['data']=2;
                    }
                }else{
                    $dblast->rollback();
                    $msg['code']=104;
                    $msg['data']=1;
                }
            }else{
                $msg['code']=1045;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    /**
     * @deprecated    签到历史接口
     */
    public function sign_history(){
        $params['uid']= I('uid');
        $params['key_admin']=I('key_admin');
        $msg=$this->commonerrorcode;
        if (in_array('', $params)){
            $msg['code']=100;
        }else{
            if (isset($_GET['page']) || isset($_POST['page'])){
                $page=I('page');
            }else{
                $page=1;
            }
            if (isset($_GET['lines']) || isset($_POST['lines'])){
                $lines=I('lines');
            }else {
                $lines=10;
            }
            $ddraw=M('history','sign_');
            $page= !empty($page) ? $page : 1;
            $rows= !empty($lines) ? $lines : 10;
            $p= ($page - 1) * $rows;
            $admininfo=$this->getMerchant($params['key_admin']);//商户信息
            $memberinfo=$this->FindMember($params['uid'], $admininfo['pre_table']);//用户信息
            
            $c= $ddraw->where(array('cardno'=>$memberinfo['cardno']))->count('id');
            $list= $ddraw->field('id,adminid,cardno',true)->where(array('cardno'=>$memberinfo['cardno']))->order('signdate desc')->limit ( $p, $rows )->select();
            if (null!=$list){
                $msg['code']=200;
                $msg['data']=array('cardno'=>$memberinfo['cardno'], 'total'=>(int)$c, 'data'=>$list);
            }else {
                $msg['code']=102;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * @deprecated  获取本次签到获取的积分
     * @param unknown $key_admin
     */
    private function GetSignScore($key_admin, $pre_table)
    {
        $score_plan=$this->GetOneAmindefault($pre_table, $key_admin, 'scorenum');
        if (false != $score_plan){//如果商户有自定义积分
            $scorep=json_decode($score_plan['function_name'], true);
            if (false != $scorep){//如果json解析成功
                $score=randomFloat($scorep[0], $scorep[1], 1);
            }else {
                $score=rand(1,10);
            }
        }else{
            $score=rand(1,10);
        }
        return $score;
    }

    /*
     *获取会员签到开关设置(1:打开，2:关闭)
     */
    public function GetSignSetting()
    {
        $admininfo=$this->getMerchant($this->ukey);
        $isenable=$this->GetOneAmindefault($admininfo['pre_table'],$this->ukey, 'isshowsignbutton');
        $isenable = (!empty($isenable['function_name']) && $isenable['function_name']==2)?2:1;
        returnjson(array('code'=>200,'data'=>array('isenable'=>$isenable)), $this->returnstyle, $this->callback);
    }
    
    
    
    
    
}