<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\TagsModel;

class TagsService{
    
    /** 
     * Tags对象
     *
     * @var MerAdmin\Model\TagsModel
     */
    public $tags_model;
    
    public function __construct()
    {
        $this->tags_model = Singleton::getModel('MerAdmin\\Model\\TagsModel');
    }
    
    /**
     * 获取标签信息
     *
     * @param int $admin_id
     * @param int $isdel
     * @return
     */
    public function getAll($admin_id, $isdel = TagsModel::ISDEL_0)
    {
        $arr = $this->tags_model->getAll($admin_id, $isdel);
        
        return $arr;
    }
    
    /**
     * 获取一条标签信息
     *
     * @param int $admin_id
     * @param int $isdel
     * @return
     */
    public function getOnce($id)
    {
        $arr = $this->tags_model->getOnce($id);

        return $arr;
    }
    
    /**
     * 获取多条标签信息
     *
     * @param int $ids
     * @return
     */
    public function getIdIn($ids)
    {
        $arr = $this->tags_model->getIdIn($ids);
    
        return $arr;
    }
    
}
?>