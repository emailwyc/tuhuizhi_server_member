<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class TagsThemsTagsModel extends MSDaoBase{
    const  TABLENAME = 'total_tags_thems_tags';
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取主题标签信息
     *
     * @param int $admin_id
     * @param int $themsId
     * @return
     */
    public function getAll($admin_id, $themsId)
    {
        $arr['admin_id'] = $admin_id;
        if($themsId != 0)
        {
            $arr['themsid'] = $themsId;
        }
        
        $arr = $this->db->where($arr)->select();
    
        return $arr;
    }
    
}
?>