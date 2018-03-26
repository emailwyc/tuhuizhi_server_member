<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\ActivityTypeModel;

class ActivityTypeService{
    
    /**
     * ActivityType对象
     *
     * @var MerAdmin\Model\ActivityTypeModel 
     */
    public $activity_type_model;//优惠券分类model
    
    public function __construct()
    {
        $this->activity_type_model = Singleton::getModel('MerAdmin\\Model\\ActivityTypeModel');
    }
    
    /**
     * 获取活动信息
     *
     * @param int $admin_id
     * @param int $system
     * @param int $disable
     * @return
     */
    public function getAll($admin_id, $system = ActivityTypeModel::SYSTEM_1, $disable = 0)
    {
        $arr = $this->activity_type_model->getAll($admin_id, $system, $disable);
        
        return $arr;
    }
    
    /**
     * 按id获取活动信息
     *
     * @param int $id
     * @return
     */
    public function getById($id, $system = ActivityTypeModel::SYSTEM_1)
    {
        $arr = $this->activity_type_model->getById($id, $system);
    
        return $arr;
    }
    
    /**
     * 按name获取活动信息
     *
     * @param int $id
     * @return
     */
    public function getByName($admin_id, $name, $system = ActivityTypeModel::SYSTEM_1)
    {
        $arr = $this->activity_type_model->getByName($admin_id, $name, $system);
    
        return $arr;
    }
    
    /**
     * 插入一条活动券信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->activity_type_model->add($insert);
    
        return $lastid;
    }
    
    /**
     * 更新一条活动券信息
     *
     * @param int $id
     * @param arr $arr
     * @return
     */
    public function updateById($id, $arr)
    {
        $this->activity_type_model->updateById($id, $arr);
    
        return $id;
    }
    
    /**
     * 删除一条活动券信息
     *
     * @param int $id
     * @return
     */
    public function delById($id)
    {
        $this->activity_type_model->delById($id);
    
        return $id;
    }
}
?>