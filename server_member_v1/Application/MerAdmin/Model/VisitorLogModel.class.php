<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class VisitorLogModel extends MSDaoBase{
    const  TABLENAME = 'total_visitor_log';
    
    const  STATUS_0 = 0;//认证失败
    const  STATUS_1 = 1;//认证成功
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取访客信息
     *
     * @return
     */
    public function getAll($adminid, $start, $offset)
    {
        $arr['adminid'] = $adminid;
        
        if($offset != 0)
        {
            $arr = $this->db->where($arr)->limit($start, $offset)->order('ctime DESC')->select();
        }
        else
        {
            $arr = $this->db->where($arr)->order('ctime DESC')->select();
        }
        
        return $arr;
    }
    
    /**
     * 根据openid获取访客信息
     *
     * @return
     */
    public function getByopenid($openid)
    {
        $arr['openid'] = $openid;
    
        $arr = $this->db->where($arr)->select();
    
        return $arr;
    }
    
    /**
     * 根据visitorId获取访客信息
     *
     * @return
     */
    public function getByVisitorid($visitorId, $status)
    {
        $arr['visitorid'] = $visitorId;
        $arr['status'] = $status;
    
        $arr = $this->db->where($arr)->select();
    
        return $arr;
    }
    
    /**
     * 获取访客信息数量
     *
     * @return
     */
    public function getCount()
    {
        
        $count = $this->db->where('')->count();
        
        return $count;
    }
    
    /**
     * 插入一条访客信息
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