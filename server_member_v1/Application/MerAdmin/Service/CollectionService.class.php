<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\CollectionModel;

//王府中环收藏
class CollectionService{
    
    /** 
     * Collection对象
     *
     * @var MerAdmin\Model\CollectionModel
     */
    public $collection_model;
    
    public function __construct()
    {
        $this->collection_model = Singleton::getModel('MerAdmin\\Model\\CollectionModel');
    }
    
    /**
     * 获取收藏信息
     *
     * @param int $admin_id
     * @param int $openid
     * @param int $activity_id
     * @return
     */
    public function getAll($admin_id, $openid, $activity_id)
    {
        $arr = $this->collection_model->getAll($admin_id, $openid, $activity_id);
        
        return $arr;
    }
    
    /**
     * 获取收藏数量
     *
     * @param int $admin_id
     * @param int $openid
     * @return
     */
    public function getNum($admin_id, $activity_id)
    {
        $arr = $this->collection_model->getNum($admin_id, $activity_id);
    
        return $arr;
    }
    
    /**
     * 获取 一条收藏信息
     *
     * @param int $admin_id
     * @param int $openid
     * @param int $activity_id
     * @return
     */
    public function getOnce($admin_id, $openid, $activity_id)
    {
        $arr = $this->collection_model->getOnce($admin_id, $openid, $activity_id);
    
        return $arr;
    }
    
    /**
     * 插入一条收藏信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->collection_model->add($insert);
    
        return $lastid;
    }
    
    /**
     * 按唯一id删除
     *
     * @param int $id
     * @return
     */
    public function delById($id)
    {
        $arr = $this->collection_model->delById($id);
    
        return $arr;
    }
    
}
?>