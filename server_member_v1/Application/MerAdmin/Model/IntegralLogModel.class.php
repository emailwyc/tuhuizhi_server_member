<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class IntegralLogModel extends MSDaoBase{
    const  TABLENAME = 'integral_log';
    
    public $db; 
    
    public function __construct($pre_table)
    {
        $this->db = M(self::TABLENAME, $pre_table);
    }
    
    /**
     * 获取日志信息
     *
     * @param int $admin_id
     * @return
     */
    public function getAll()
    {
        $arr = $this->db->where('')->select();
    
        return $arr;
    }
    
    /**
     * 按id获取日志信息
     *
     * @param int $id
     * @return
     */
    public function getById($act_id, $admin_id, $buildid)
    {
        $arr['pid'] = $act_id;
        if($admin_id != '')
        {
            $arr['admin_id'] = $admin_id;
        }
        if($buildid != '')
        {
            $arr['buildid'] = $buildid;
        }
        
        $arr = $this->db->where($arr)->find();
    
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
        $lastid = $this->db->add($insert);
    
        return $lastid;
    }
}
?>