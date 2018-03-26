<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class TagsThemsBannerModel extends MSDaoBase{
    const  TABLENAME = 'total_tags_thems_banners';
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取主题banners信息
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