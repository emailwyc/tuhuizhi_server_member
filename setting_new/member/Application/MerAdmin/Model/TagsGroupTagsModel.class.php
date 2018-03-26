<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class TagsGroupTagsModel extends MSDaoBase{
    const  TABLENAME = 'total_tags_groups_tags';
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取分组标签信息
     *
     * @param int $admin_id
     * @param int $groupid
     * @return
     */
    public function getAll($admin_id, $groupid)
    {
        $arr['admin_id'] = $admin_id;
        $arr['groupid'] = $groupid;
        
        $arr = $this->db->where($arr)->select();
    
        return $arr;
    }
    
}
?>