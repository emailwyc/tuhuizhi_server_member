<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class TagsModel extends MSDaoBase{
    const  TABLENAME = 'total_tags';
    const  ISDEL_0 = 0;//显示
    const  ISDEL_1 = 1;//删除
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取标签信息
     *
     * @param int $admin_id
     * @param int $isdel
     * @return
     */
    public function getAll($admin_id, $isdel)
    {
        $arr['admin_id'] = $admin_id;
        $arr['isdel'] = $isdel;
        
        $arr = $this->db->where($arr)->select();
    
        return $arr;
    }
    
    /**
     * 获取一条标签信息
     *
     * @param int $id
     * @return
     */
    public function getOnce($id)
    {
        $arr['id'] = $id;
    
        $arr = $this->db->where($arr)->find();
    
        return $arr;
    }
    
    /**
     * 获取多条标签信息
     *
     * @param int $ids
     * @return
     */
    public function getIdIn($ids)
    {
        $arr['id']  = array('in', $ids);
    
        $arr = $this->db->where($arr)->select();
    
        return $arr;
    }
    
    
}
?>