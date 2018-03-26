<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class CustomerModel extends MSDaoBase{
    const  TABLENAME = 'total_customer_service';
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取客服信息
     *
     * @param int $admin_id
     * @return
     */
    public function getAll($admin_id)
    {
        $arr['admin_id'] = $admin_id;
        
        $arr = $this->db->where($arr)->select();
        
        return $arr;
    }
    
    /**
     * 获取一条客服信息
     *
     * @param int $admin_id
     * @return
     */
    public function getOnce($id)
    {
        $arr['id'] = $id;
        
        $arr = $this->db->where($arr)->find();
    
        return $arr;
    }
    
    /**
     * 插入一条客服信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->db->add($insert);
    
        return $lastid;
    }
    
    /**
     * 更新一条客服信息
     *
     * @param int $id
     * @return
     */
    public function delById($id)
    {
        $this->db->where(array('id'=>$id))->delete();
    
        return $id;
    }
}
?>