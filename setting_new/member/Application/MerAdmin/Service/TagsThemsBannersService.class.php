<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\TagsThemsBannerModel;

class TagsThemsBannersService{
    
    /** 
     * Tags对象
     *
     * @var MerAdmin\Model\TagsThemsBannerModel
     */
    public $tags_thems_banner_model;
    
    public function __construct()
    {
        $this->tags_thems_banner_model = Singleton::getModel('MerAdmin\\Model\\TagsThemsBannerModel');
    }
    
    /**
     * 获取主题banners信息
     *
     * @param int $admin_id
     * @param int $themsId
     * @return
     */
    public function getAll($admin_id, $themsId = 0)
    {
        $arr = $this->tags_thems_banner_model->getAll($admin_id, $themsId);
        
        return $arr;
    }
    
}
?>