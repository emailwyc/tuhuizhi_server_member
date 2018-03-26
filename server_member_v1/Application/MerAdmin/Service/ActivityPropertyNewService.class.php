<?php
namespace MerAdmin\Service;
use Common\core\Singleton;

class ActivityPropertyNewService{
    
    /**
     * Activity对象
     *
     * @var MerAdmin\Model\ActivityPropertyNewModel 
     */
    public $activity_property_new_model;//活动奖品model
    
    public function __construct()
    {
        $this->activity_property_new_model = Singleton::getModel('MerAdmin\\Model\\ActivityPropertyNewModel');
    }
    
    /**
     * 批量更新数量
     * @param string $pid
     * @return
     */
    public function updateIssue($pid)
    {
        //更新数量
        $propertys = $this->getAll('',  '',  '',  '', $pid);
        if(!empty($propertys))
        {
            foreach ($propertys as $k => $v)
            {
                $v['issue']++;
                $this->updateById($v['id'], $v);
            }
        }
    }
    
    /**
     * 获取活动信息
     * @param string $activity
     * @param int    $type_id
     * @param int    $vip_area
     * @param int    $buildid
     * @return
     */
    public function getAll($activity = '', $type_id = '', $vip_area = '', $buildid = '', $pid = '')
    {
        $arr = $this->activity_property_new_model->getAll($activity, $type_id, $vip_area, $buildid, $pid);
        
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
        $arr = $this->activity_property_new_model->getById($act_id, $admin_id, $buildid);
    
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
        $arr = $this->activity_property_new_model->joinIntegralType($act_id, $admin_id, $buildid, $join);
    
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
        $lastid = $this->activity_property_new_model->add($insert);
    
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
        $this->activity_property_new_model->updateById($id, $arr);
    
        return $id;
    }
}
?>