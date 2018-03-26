<?php
namespace House\Model;
use Common\Model\CommonModel;
use Think\Model;
class KeyAdminBuildModel extends CommonModel{
    protected $tableName = 'key_admin_build';
    protected $tablePrefix = '';
    protected $connection = 'DB_CONFIG2';
    protected $fields=array('id','key_admin','build_id','build_name','prefix');
    protected $pk     = 'id';

    public function _initialize(){
        parent::__initialize();
    }

    /**
     * 根据openid获取用户信息
     * @param int $key_admin,$openid
     * @return
     */
    public function getBuildInfo($key_admin,$buildid){
        $key = "house:keyadminbuild:$buildid";
        $m_info = $this->redis->get($key);
        if ($m_info) {
            return json_decode($m_info, true);
        } else {
            $re = $this->field(array('build_name'))->where(array('build_id'=>$buildid))->find();
            if ($re){
                $this->redis->set($key, json_encode($re),array('ex'=>3600));//10分钟
            }
            return $re;
        }
    }

}
?>