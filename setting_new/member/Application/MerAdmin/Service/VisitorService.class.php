<?php
namespace MerAdmin\Service;
use Common\core\Singleton;

//访客
class VisitorService{
    
    /**
     * Complaint对象
     *
     * @var MerAdmin\Model\VisitorModel
     */
    public $visitor_model;//访客model
    
    public function __construct()
    {
        $this->visitor_model = Singleton::getModel('MerAdmin\\Model\\VisitorModel');
    }
    
    /**
     * 获取访客信息
     *
     * @param int $admin_id
     * @return
     */
    public function getAll($admin_id, $buildNum = 0, $unit = 0, $houseNum = 0, $status = 0, $date = '', $start = 0, $offset = 0)
    {
        $arr = $this->visitor_model->getAll($admin_id, $buildNum, $unit, $houseNum, $status, $date, $start, $offset);
        
        return $arr;
    }
    
    /**
     * 获取访客信息数量
     *
     * @param int $admin_id
     * @return
     */
    public function getCount($admin_id, $buildNum = 0, $unit = 0, $houseNum = 0, $status = 0, $date = '')
    {
        $arr = $this->visitor_model->getCount($admin_id, $buildNum, $unit, $houseNum, $status, $date);
    
        return $arr;
    }
    
    /**
     * 获取一条访客信息
     *
     * @param int $admin_id
     * @return
     */
    public function getOnce($admin_id)
    {
        $arr = $this->visitor_model->getOnce($admin_id);
    
        return $arr;
    }
    
    /**
     * 按openid获取一条访客信息
     *
     * @param int $openid
     * @return
     */
    public function getByOpenid($openid)
    {
        $arr = $this->visitor_model->getByOpenid($openid);
    
        return $arr;
    }
    
    /**
     * 插入一条访客券信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->visitor_model->add($insert);
    
        return $lastid;
    }
    
    /**
     * 更新一条访客券信息
     *
     * @param int $id
     * @param arr $arr
     * @return
     */
    public function updateById($id, $arr)
    {
        $this->visitor_model->updateById($id, $arr);
    
        return $id;
    }
}
?>