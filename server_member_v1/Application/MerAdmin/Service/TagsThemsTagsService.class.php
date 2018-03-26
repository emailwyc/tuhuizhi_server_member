<?php
namespace MerAdmin\Service;
use Common\core\Singleton;

class TagsThemsTagsService{
    
    /** 
     * TagsThemsTags对象
     *
     * @var MerAdmin\Model\TagsThemsTagsModel
     */
    public $tags_thems_tags_model;
    
    public function __construct()
    {
        $this->tags_thems_tags_model = Singleton::getModel('MerAdmin\\Model\\TagsThemsTagsModel');
    }
    
    /**
     * 获取主题标签信息
     *
     * @param int $admin_id
     * @param int $themsId
     * @return
     */
    public function getAll($admin_id, $themsId = 0)
    {
        $arr = $this->tags_thems_tags_model->getAll($admin_id, $themsId);
        
        return $arr;
    }
    
}
?>