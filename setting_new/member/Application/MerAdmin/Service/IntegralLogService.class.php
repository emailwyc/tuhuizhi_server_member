<?php
namespace MerAdmin\Service;
use Common\core\Singleton;

class IntegralLogService{
    
    /**
     * Activity对象
     *
     * @var MerAdmin\Model\IntegralLogModel 
     */
    public $integral_log;//活动奖品model
    
    public function __construct($pre_table)
    {
        $this->integral_log = Singleton::getModel('MerAdmin\\Model\\IntegralLogModel', $pre_table);
    }
    
    /**
     * 获取日志信息
     * @return
     */
    public function getAll()
    {
        $arr = $this->integral_log->getAll();
        
        return $arr;
    }
    
    /**
     * 获取一条日志信息
     *
     * @param int $act_id
     * @param int $admin_id
     * @param int $buildid
     * @return
     */
    public function getOnce($act_id, $admin_id = '', $buildid = '')
    {
        $arr = $this->integral_log->getById($act_id, $admin_id, $buildid);
    
        return $arr;
    }
    
    /**
     * 插入一条日志信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->integral_log->add($insert);
    
        return $lastid;
    }
    
}
?>