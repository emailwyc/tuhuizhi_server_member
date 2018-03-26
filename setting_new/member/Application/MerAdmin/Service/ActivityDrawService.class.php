<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\ActivityDrawModel;

class ActivityDrawService{
    
    /**
     * ActivityDraw对象
     *
     * @var MerAdmin\Model\ActivityDrawModel 
     */
    public $activityDraw_model;//活动配置model
    
    public function __construct()
    {
        $this->activityDraw_model = Singleton::getModel('MerAdmin\\Model\\ActivityDrawModel');
    }
    
    /**
     * 获取活动信息
     *
     * @param int $admin_id
     * @param int $activity_id
     * @param int $buildid
     * @return
     */
    public function getAll($admin_id, $activity_id, $buildid = '')
    {
        $arr = $this->activityDraw_model->getAll($admin_id, $activity_id, $buildid);
        
        return $arr;
    }
    
    /**
     * 获取一条活动信息
     *
     * @param int $admin_id
     * @param int $buildid
     * @return
     */
    public function getOnce($admin_id, $buildid = '')
    {
        $arr = $this->activityDraw_model->getOnce($admin_id, $buildid);
    
        return $arr;
    }
    
    /**
     * 根据活动id获取一条活动信息
     *
     * @param int $actity
     * @return
     */
    public function getByActity($actity)
    { 
        $arr = $this->activityDraw_model->getByActity($actity);
    
        return $arr;
    }
    
    /**
     * 按唯一id获取一条活动信息
     *
     * @param int $id
     * @return
     */
    public function getById($id)
    {
        $arr = $this->activityDraw_model->getById($id);
    
        return $arr;
    }
    
    /**
     * 按唯一id删除
     *
     * @param int $id
     * @return
     */
    public function delById($id)
    {
        $arr = $this->activityDraw_model->delById($id);
    
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
        $lastid = $this->activityDraw_model->add($insert);
    
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
        $this->activityDraw_model->updateById($id, $arr);
    
        return $id;
    }
}
?>