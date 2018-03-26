<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\TagsGroupModel;

class TagsGroupService{
    
    /** 
     * TagsGroup对象
     *
     * @var MerAdmin\Model\TagsGroupModel
     */
    public $tags_group_model;
    
    public function __construct()
    {
        $this->tags_group_model = Singleton::getModel('MerAdmin\\Model\\TagsGroupModel');
    }
    
    /**
     * 获取分组信息
     *
     * @param int $admin_id
     * @param int $isdel
     * @return
     */
    public function getAll($admin_id, $isdel = TagsGroupModel::ISDEL_0)
    {
        $arr = $this->tags_group_model->getAll($admin_id, $isdel);
        
        return $arr;
    }
    
    /**
     * 获取一条分组信息
     *
     * @param int $admin_id
     * @param int $isdel
     * @return
     */
    public function getOnce($admin_id, $isdel = TagsGroupModel::ISDEL_0)
    {
        $arr = $this->tags_group_model->getOnce($admin_id, $isdel);

        return $arr;
    }
    
}
?>