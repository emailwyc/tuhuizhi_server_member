<?php
namespace House\Model;
use Common\Model\CommonModel;
use Think\Model;
class DkptAuditorFloorModel extends CommonModel{
    protected $tableName = 'dkpt_auditor_floor';
    protected $tablePrefix = '';
    protected $connection = 'DB_CONFIG2';
    protected $fields=array('id','key_admin','user_id','build_id','floor');
    protected $pk     = 'id';

    public function _initialize(){
        parent::__initialize();
    }

    /**
     * 根据openid获取用户信息
     * @param int $key_admin,$openid
     * @return
     */
    public function getUserInfoById($id,$isList=false){
        $key = "house:dkptauditorfloor:$id:$isList";
        $m_info = $this->redis->get($key);
        if ($m_info) {
            return json_decode($m_info, true);
        } else {
            if($isList){
                $re = $this->where(array('user_id'=>$id))->select();
            }else{
                $re = $this->where(array('user_id'=>$id))->find();
            }
            if ($re){
                $this->redis->set($key, json_encode($re),array('ex'=>600));//10分钟
            }
            return $re;
        }
    }

}
?>