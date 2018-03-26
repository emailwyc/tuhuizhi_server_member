<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class ActivityDrawModel extends MSDaoBase{
    const  TABLENAME = 'integral_activity_draw';
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME); 
    }
    
    /**
     * 获取活动信息
     *
     * @param int $admin_id
     * @param int $activity_id
     * @param int $buildid
     * @return
     */
    public function getAll($admin_id, $activity_id, $buildid)
    {
        $arr['admin_id'] = $admin_id;
        
        if($activity_id != '')
        {
            $arr['activity'] = $activity_id;
        }
        if($buildid != '')
        {
            $arr['buildid'] = $buildid;
        }
        
        $arr = $this->db->where($arr)->select();
        
        return $arr;
    }
    
    /**
     * 获取一条活动信息
     *
     * @param int $admin_id
     * @param int $buildid
     * @return
     */
    public function getOnce($admin_id, $buildid)
    {
        $arr['admin_id'] = $admin_id;
        
        if($buildid != '')
        {
            $arr['buildid'] = $buildid;
        }
    
        $arr = $this->db->where($arr)->find();
    
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
        $arr['activity'] = $actity;
        
        $arr = $this->db->where($arr)->find();
    
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
        $arr['id'] = $id;
    
        $arr = $this->db->where($arr)->find();
    
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
        $arr['id'] = $id;
    
        $res = $this->db->where($arr)->delete();
    
        return $res;
    }
    
    /**
     * 插入一条活动券信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->db->add($insert);
    
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
        $this->db->where(array('id'=>$id))->save($arr);
    
        return $id;
    }
}
?>