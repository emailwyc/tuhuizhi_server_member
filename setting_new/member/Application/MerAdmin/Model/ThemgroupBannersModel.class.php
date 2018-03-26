<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class ThemgroupBannersModel extends MSDaoBase{
    const  TABLENAME = 'total_themgroupbanners';
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取信息
     *
     * @param int $admin_id
     * @return
     */
    public function getAll($admin_id)
    {
        $arr['adminid'] = $admin_id;
        
        $arr = $this->db->where($arr)->order('sort ASC')->select();
        foreach ($arr as $key => $value) {
            $arr[$key]['bannerredirect'] = htmlspecialchars_decode($value['bannerredirect']);
        }

        return $arr;
    }
    
}
?>