<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\TagsGroupTagsModel;

class TagsGroupTagsService{
    
    /** 
     * TagsGroupTags对象
     *
     * @var MerAdmin\Model\TagsGroupTagsModel
     */
    public $tags_group_tags_model;
    
    public function __construct()
    {
        $this->tags_group_tags_model = Singleton::getModel('MerAdmin\\Model\\TagsGroupTagsModel');
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
        $arr = $this->tags_group_tags_model->getAll($admin_id, $groupid);
        
        return $arr;
    }
    
}
?>