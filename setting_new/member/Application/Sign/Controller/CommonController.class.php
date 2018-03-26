<?php
namespace Sign\Controller;

use Common\Controller\ErrorcodeController;
class CommonController extends ErrorcodeController
{
    // TODO - Insert your code here
    
    /**
     * 根据用户id和表前缀查询用户的信息
     * @param string $uid
     * @param string $pre_table
     */
    protected function FindMember(string $uid, string $pre_table,$cardno = '') {
        $db=M('mem',$pre_table);
        
        if($cardno != ''){
            $member=$db->where(array('cardno'=>$cardno))->find();
        }else{
            $member=$db->where(array('openid'=>$uid))->find();
        }
        
        if (null == $member){
            $msg=$this->commonerrorcode;
            $msg['code']=103;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit;
        }else {
            return $member;
        }
        
    }
    
    /**
     * 查询此cardno今天有没有签到
     * @param unknown $cardno
     * @param unknown $adminid
     */
    protected function CheckisSign($cardno, $adminid){
        //从最后一次签到表中查询
        $db=M('last_history','sign_');
        $find=$db->field('cardno,lastdate,totalday')->where(array('cardno'=>$cardno,'lastdate'=>date('Y-m-d'),'adminid'=>$adminid))->find();
        if (null != $find){
            $dbsign=M('history','sign_');
            $sign=$dbsign->where(array('signdate'=>date('Y-m-d'),'cardno'=>$cardno,'adminid'=>$adminid))->find();
            if (isset($sign['scores'])){
                 $find['scores']=$sign['scores'];
            }
        }
        return $find;
    }
}

?>