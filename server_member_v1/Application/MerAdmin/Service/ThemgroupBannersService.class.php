<?php
namespace MerAdmin\Service;
use Common\core\Singleton;

class ThemgroupBannersService{
    
    /** 
     * ThemgroupBanners对象
     *
     * @var MerAdmin\Model\ThemgroupBannersModel
     */
    public $themgroup_banners_model;//活动配置model
    
    public function __construct()
    {
        $this->themgroup_banners_model = Singleton::getModel('MerAdmin\\Model\\ThemgroupBannersModel');
    }
    
    /**
     * 获取banners信息
     *
     * @param int $admin_id
     * @return
     */
    public function getAll($admin_id)
    {
        $arr = $this->themgroup_banners_model->getAll($admin_id);
        
        return $arr;
    }
    
}
?>