<?php
namespace MerAdmin\Service;
use Common\core\Singleton;

//访客log
class VisitorLogService{
    
    /**
     * Complaint对象
     *
     * @var MerAdmin\Model\VisitorLogModel
     */
    public $visitor_log_model;//访客logmodel
    
    public function __construct()
    {
        $this->visitor_log_model = Singleton::getModel('MerAdmin\\Model\\VisitorLogModel');
    }
    
    /**
     * 获取访客信息
     *
     * @return
     */
    public function getAll($adminid, $start = 0, $offset = 0)
    {
        $arr = $this->visitor_log_model->getAll($adminid, $start, $offset);
        
        return $arr;
    }
    
    /**
     * 根据openid获取访客信息
     *
     * @return
     */
    public function getByopenid($openid)
    {
        $arr = $this->visitor_log_model->getByopenid($openid);
    
        return $arr;
    }
    
    /**
     * 根据visitorId获取访客信息
     *
     * @return
     */
    public function getByVisitorid($visitorId, $status)
    {
        $arr = $this->visitor_log_model->getByVisitorid($visitorId, $status);
    
        return $arr;
    }
    
    /**
     * 获取访客信息数量
     *
     * @return
     */
    public function getCount()
    {
        $arr = $this->visitor_log_model->getCount();
    
        return $arr;
    }
    
    /**
     * 插入一条访客log信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->visitor_log_model->add($insert);
    
        return $lastid;
    }
}
?>