<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class ComplaintModel extends MSDaoBase{
    const  TABLENAME = 'total_complaint';
    
    const TYPE_0 = 0;//1投诉
    const TYPE_1 = 1;//2建议
    const STATUS_0 = 0;//未处理
    const STATUS_1 = 1;//已处理
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取投诉信息
     *
     * @param int $admin_id
     * @return
     */
    public function getAll($admin_id, $type, $buildNum, $unit, $houseNum, $status, $start, $offset) 
    {
        $arr['total_complaint.adminid'] = $admin_id;
        
        if($type != null)
        {
            $arr['type'] = $type;
        }
        if($status != null)
        {
            $arr['status'] = $status;
        }
        
        if($buildNum != null)
        {
            $arr['buildNum'] = $buildNum;
        }
        if($unit != null)
        {
            $arr['unit'] = $unit;
        }
        if($houseNum != null)
        {
            $arr['houseNum'] = $houseNum;
        }
        
        $field = 'total_complaint.*,total_buildid.name as bnam';
        $join = 'total_buildid on total_buildid.buildid='.self::TABLENAME.'.buildNum';
        
        if($offset != 0)
        {
            $arr = $this->db->join($join)->field($field)->where($arr)->limit($start, $offset)->order('ctime DESC')->select();
        }
        else
        {
            $arr = $this->db->join($join)->field($field)->where($arr)->order('ctime DESC')->select();
        }
    
        return $arr;
    }
    
    /**
     * 获取一条投诉信息
     *
     * @param int $admin_id
     * @return
     */
    public function getOnce($id)
    {
        $arr['total_complaint.id'] = $id;
    
        $field = 'total_complaint.*,total_buildid.name as bnam';
        $join = 'total_buildid on total_buildid.buildid='.self::TABLENAME.'.buildNum';
        
        $arr = $this->db->join($join)->field($field)->where($arr)->find();
    
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
        $arr['openid'] = $openid;

        $arr = $this->db->where($arr)->order('ctime DESC')->select();
    
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
        $arr['userid'] = $userid;
    
        $arr = $this->db->where($arr)->order('ctime DESC')->select();
    
        return $arr;
    }
    
    /**
     * 获取投诉信息数量
     *
     * @param int $admin_id
     * @return
     */
    public function getCount($admin_id, $type, $buildNum, $unit, $houseNum, $status)
    {
        $arr['adminid'] = $admin_id;
    
        if($type != null)
        {
            $arr['type'] = $type;
        }
        if($status != null)
        {
            $arr['status'] = $status;
        }
        
        if($buildNum != null)
        {
            $arr['buildNum'] = $buildNum;
        }
        if($unit != null)
        {
            $arr['unit'] = $unit;
        }
        if($houseNum != null)
        {
            $arr['houseNum'] = $houseNum;
        }
    
        $count = $this->db->where($arr)->count();
        
        return $count;
    }
    
    /**
     * 插入一条投诉信息
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
     * 更新一条投诉信息
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