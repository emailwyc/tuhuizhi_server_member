<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class TagsGroupModel extends MSDaoBase{
    const  TABLENAME = 'total_tags_groups';
    const  ISDEL_0 = 0;//没删除
    const  ISDEL_1 = 1;//删除
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取分组信息
     *
     * @param int $admin_id
     * @param int $isdel
     * @return
     */
    public function getAll($admin_id, $isdel)
    {
        $arr['adminid'] = $admin_id;
        $arr['isdel'] = $isdel;
        
        $arr = $this->db->where($arr)->select();
    
        return $arr;
    }
    
    /**
     * 获取一条分组信息
     *
     * @param int $admin_id
     * @param int $isdel
     * @return
     */
    public function getOnce($admin_id, $isdel)
    {
        $arr['admin_id'] = $admin_id;
        $arr['isdel'] = $isdel;
    
        $arr = $this->db->where($arr)->find();
    
        return $arr;
    }
    
}
?>