<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\TagsThemsModel;

class TagsThemsService{
    
    /** 
     * Tags对象
     *
     * @var MerAdmin\Model\TagsThemsModel
     */
    public $tags_thems_model;
    
    public function __construct()
    {
        $this->tags_thems_model = Singleton::getModel('MerAdmin\\Model\\TagsThemsModel');
    }
    
    /**
     * 获取主题信息
     *
     * @param int $admin_id
     * @param int $isdel
     * @return
     */
    public function getAll($admin_id, $isdel = 0)
    {
        $arr = $this->tags_thems_model->getAll($admin_id, $isdel);
        
        return $arr;
    }
    
    /**
     * 获取一条主题信息
     *
     * @param int $admin_id
     * @param int $isdel
     * @return
     */
    public function getOnce($id)
    {
        $arr = $this->tags_thems_model->getOnce($id);

        return $arr;
    }
    
}
?>