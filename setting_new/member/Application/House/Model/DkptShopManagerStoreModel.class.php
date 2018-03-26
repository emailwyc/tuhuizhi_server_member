<?php
namespace House\Model;
use Common\Model\CommonModel;
use Think\Model;
class DkptShopManagerStoreModel extends CommonModel{
    //可加入字段-验证规则-参数绑定等，数据分表请使用高级模型
    //缓存规则前缀(模块：模型：key+),且字母小写
    protected $tableName = 'dkpt_shop_manager_store';
    protected $tablePrefix = '';
    protected $connection = 'DB_CONFIG2';
    //字段定义可一定程度提高性能
    protected $fields=array('id','key_admin','user_id','build_id','floor','poi_no');
    protected $pk     = 'id';
    //protected $_validate = array();可以定义字段验证规则

    public function _initialize(){
        parent::__initialize();
    }

    /**
     * 根据openid获取用户信息
     * @param int $key_admin,$openid
     * @return
     */
    public function getUserInfoById($id,$isList=false){
        $key = "house:dkptshopmanagerstore:$id:$isList";
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