<?php
namespace ClientApi\Controller;
use Think\Controller;
use Common\Controller\RedisController as A;

class IndexController extends ClientCommonController
{
    public function _initialize(){
        parent::_initialize();
        //$params = $this->params;
    }

    //扫码统计
    public function QRcodeStatistic() {
        $params = $this->params;
        $this->emptyCheck($params,array('qid'));
        $qid = (int)$params['qid'];
        //check params
        $db = M('qrcode_statistic', $this->setting['pre_table']);
        $db1 = M('qrcode', $this->setting['pre_table']);
        $curdate = date("Y-m-d",time());
        $where = array('qid'=>$qid,'date'=>$curdate);
        $arr = $db->field("id")->where($where)->find();
        $arr1 = $db1->where(array('id'=>$qid))->find();
        if(empty($arr)){
            $db->add(array('date'=>$curdate,'qid'=>$qid,'num'=>1));
        }else{
            $db->where($where)->setInc('num',1);
        }
        $db1->where(array('id'=>$qid))->setInc('visitnum',1);
        $msg = array('code'=>200,'data'=>$arr1);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    /**
     * 根据key_admin获取商户key_admin和子商户key_admin
     */
    public function getMerInfo(){
        $params['key_admin']=I('key_admin');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $admininfo = $this->getMerchant($params['key_admin']);
            $db = M('total_admin');
            if($admininfo['pid']==0){
                $where = array('pid'=>$admininfo['id']);
                $arr=$db->where($where)->find();
                $merinfo_child = empty($arr['ukey'])?"":$arr['ukey'];
                $result = array('merinfo'=>$admininfo['ukey'],'merinfo_child'=>$merinfo_child);
            }else{
                $where = array('id'=>$admininfo['pid']);
                $arr=$db->where($where)->find();
                $merinfo = empty($arr['ukey'])?"":$arr['ukey'];
                $result = array('merinfo'=>$merinfo,'merinfo_child'=>$admininfo['ukey']);
            }
            $msg['code']=200;
            $msg['data']=$result;
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }

}

?>
