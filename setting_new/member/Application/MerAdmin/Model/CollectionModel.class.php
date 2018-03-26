<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class CollectionModel extends MSDaoBase{
    const  TABLENAME = 'total_collection';
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取信息
     *
     * @param int $admin_id
     * @param int $openid
     * @param int $activity_id
     * @return
     */
    public function getAll($admin_id, $openid, $activity_id)
    {
        $arr['admin_id'] = $admin_id;
        $arr['openid'] = $openid;
        
        if($activity_id)
        {
            $arr['activity_id'] = $activity_id;
        }
        
        $arr = $this->db->where($arr)->select();
    
        return $arr;
    }
    
    /**
     * 获取收藏数量
     *
     * @param int $admin_id
     * @param int $activity_id
     * @return
     */
    public function getNum($admin_id, $activity_id)
    {
        $arr['admin_id'] = $admin_id;
    
        if($activity_id)
        {
            $arr['activity_id'] = $activity_id;
        }
    
        $arr = $this->db->where($arr)->count();
    
        return $arr;
    }
    
    /**
     * 获取 一条收藏信息
     *
     * @param int $admin_id
     * @param int $openid
     * @param int $activity_id
     * @return
     */
    public function getOnce($admin_id, $openid, $activity_id)
    {
        $arr['admin_id'] = $admin_id;
        $arr['openid'] = $openid;
        
        if($activity_id)
        {
            $arr['activity_id'] = $activity_id;
        }
        
        $arr = $this->db->where($arr)->find();
    
        return $arr;
    }
    
    /**
     * 插入一条收藏信息
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
    
}
?>