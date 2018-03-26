<?php
namespace MerAdmin\Service;
use Common\core\Singleton;

//投诉建议
class ComplaintService{
    
    /**
     * Complaint对象
     *
     * @var MerAdmin\Model\ComplaintModel
     */
    public $complaint_model;//投诉model
    
    public function __construct()
    {
        $this->complaint_model = Singleton::getModel('MerAdmin\\Model\\ComplaintModel');
    }
    
    /**
     * 获取投诉信息
     *
     * @param int $admin_id
     * @return
     */
    public function getAll($admin_id, $type = 0, $buildNum = 0, $unit = 0, $houseNum = 0, $status = 0, $start = 0, $offset = 0)
    {
        $arr = $this->complaint_model->getAll($admin_id, $type, $buildNum, $unit, $houseNum, $status, $start, $offset);

        return $arr;
    }
    
    /**
     * 根据openid获取投诉信息
     *
     * @param int $openid
     * @return
     */
    public function getByOpenid($openid)
    {
        $arr = $this->complaint_model->getByOpenid($openid);
    
        return $arr;
    }
    
    /**
     * 根据userid获取投诉信息
     *
     * @param int $userid
     * @return
     */
    public function getByUserid($userid)
    {
        $arr = $this->complaint_model->getByUserid($userid);
    
        return $arr;
    }
    
    /**
     * 获取投诉信息数量
     *
     * @param int $admin_id
     * @return
     */
    public function getCount($admin_id, $type = 0, $buildNum = 0, $unit = 0, $houseNum = 0, $status = 0)
    {
        $arr = $this->complaint_model->getCount($admin_id, $type, $buildNum, $unit, $houseNum, $status);
    
        return $arr;
    }
    
    /**
     * 获取一条投诉信息
     *
     * @param int $admin_id
     * @return
     */
    public function getOnce($admin_id)
    {
        $arr = $this->complaint_model->getOnce($admin_id);
    
        return $arr;
    }
    
    /**
     * 插入一条投诉券信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->complaint_model->add($insert);
    
        return $lastid;
    }
    
    /**
     * 更新一条投诉券信息
     *
     * @param int $id
     * @param arr $arr
     * @return
     */
    public function updateById($id, $arr)
    {
        $this->complaint_model->updateById($id, $arr);
    
        return $id;
    }
}
?>