<?php
namespace MerAdmin\Service;
use Common\core\Singleton;

class ActivityPropertyService{
    
    /**
     * Activity对象
     *
     * @var MerAdmin\Model\ActivityPropertyModel 
     */
    public $activity_property_model;//活动奖品model
    
    public function __construct()
    {
        $this->activity_property_model = Singleton::getModel('MerAdmin\\Model\\ActivityPropertyModel');
    }
    
    /**
     * 获取活动信息
     * @param string $activity
     * @param int    $type_id
     * @return
     */
    public function getAll($activity = '', $type_id = '')
    {
        $arr = $this->activity_property_model->getAll($activity, $type_id);
        
        return $arr;
    }
    
    /**
     * 获取一条活动信息
     *
     * @param int $act_id
     * @param int $admin_id
     * @param int $buildid
     * @return
     */
    public function getOnce($act_id, $admin_id = '', $buildid = '')
    {
        $arr = $this->activity_property_model->getById($act_id, $admin_id, $buildid);
    
        return $arr;
    }
    
    /**
     * 连表查询券所属分类以及券的一些信息
     *
     * @param int $act_id
     * @param int $admin_id
     * @param int $buildid
     * @param sting $join
     * @return
     */
    public function joinIntegralType($act_id, $admin_id = '', $buildid = '', $join = '')
    {
        $arr = $this->activity_property_model->joinIntegralType($act_id, $admin_id = '', $buildid = '', $join);
    
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
        $lastid = $this->activity_property_model->add($insert);
    
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
        $this->activity_property_model->updateById($id, $arr);
    
        return $id;
    }
}
?>