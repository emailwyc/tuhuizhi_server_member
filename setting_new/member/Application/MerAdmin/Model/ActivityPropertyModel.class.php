<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class ActivityPropertyModel extends MSDaoBase{
    const  TABLENAME = 'integral_property';
    const  SYSTEM_0 = 0;//积分商城
    const  SYSTEM_1 = 1;//活动券
    const  STATUS_1 = 1;//下线
    const  STATUS_2 = 2;//上线
    const  DISCOUNT_1 = 1;//统一折扣
    const  DISCOUNT_2 = 2;//尊享折扣
    const  VIP_1 = 1;//普通专区
    const  VIP_2 = 2;//黑钻专区
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME); 
    }
    
    /**
     * 获取活动信息
     *
     * @param string $activity
     * @param int    $type_id
     * @return
     */
    public function getAll($activity, $type_id)
    {
        $arr = array();
        if($activity != '')
        {
            $arr['activity_id'] = $activity;
        }
        if($type_id != '')
        {
            $arr['type_id'] = $type_id;
        }
        
        $res = $this->db->where($arr)->select();
    
        return $res;
    }
    
    /**
     * 按id获取活动信息
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
     * 按id获取活动信息
     *
     * @param int $act_id
     * @param int $admin_id
     * @param int $buildid
     * @param sting $join
     * @return
     */
    public function joinIntegralType($act_id, $admin_id, $buildid, $join)
    {
        $arr['pid'] = $act_id;
        if($admin_id != '')
        {
            $arr['integral_property.admin_id'] = $admin_id;
        }
        if($buildid != '')
        {
            $arr['buildid'] = $buildid;
        }
        
        $arr = $this->db->join($join)->where($arr)->find();
    
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
        $lastid = $this->db->add($insert);
    
        return $lastid;
    }
    
    /**
     * 更新一条优惠券信息
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
    
    /**
     * 删除一条优惠券信息
     *
     * @param int $id
     * @param arr $arr
     * @return
     */
    public function delById($id, $arr)
    {
        $this->db->where(array('id'=>$id))->delete();
    
        return $id;
    }
}
?>