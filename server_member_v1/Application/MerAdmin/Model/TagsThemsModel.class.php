<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class TagsThemsModel extends MSDaoBase{
    const  TABLENAME = 'total_tags_thems';
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取主题信息
     *
     * @param int $admin_id
     * @param int $isdel
     * @return
     */
    public function getAll($admin_id, $isdel)
    {
        $arr['adminid'] = $admin_id;
        $arr['isdel'] = $isdel;
        
        $arr = $this->db->where($arr)->order('sort ASC')->select();
    
        return $arr;
    }
    
    /**
     * 获取一条主题信息
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
    
}
?>