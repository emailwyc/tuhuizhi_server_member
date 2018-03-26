<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class VisitorModel extends MSDaoBase{
    const  TABLENAME = 'total_visitor';
    
    const  STATUS_0 = 0;//等待来访
    const  STATUS_1 = 1;//已登记
    const  STATUS_2 = 2;//已过期
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取访客信息
     *
     * @param int $admin_id
     * @return
     */
    public function getAll($admin_id, $buildNum, $unit, $houseNum, $status, $date, $start, $offset)
    {
        $arr['admin_id'] = $admin_id;
        
        if($date != null)
        {
            $arr['date'] = $date;
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
        
        if($status == self::STATUS_0)
        {
            $arr['status'] = $status;
            $arr['date'] = array('EGT', date('Y-m-d',time()));
        }
        if($status == self::STATUS_1)
        {
            $arr['status'] = $status;
        }
        if($status == self::STATUS_2)
        {
            $arr['date'] = array('LT', date('Y-m-d',time()));
        }
        
        if($offset != null)
        {
            $arr = $this->db->where($arr)->limit($start, $offset)->order('ctime DESC')->select();
        }
        else
        {
            $arr = $this->db->where($arr)->limit($start, $offset)->order('ctime DESC')->select();
        }
        
        return $arr;
    }
    
    /**
     * 获取访客信息数量
     *
     * @param int $admin_id
     * @return
     */
    public function getCount($admin_id, $buildNum, $unit, $houseNum, $status, $date)
    {
        $arr['admin_id'] = $admin_id;
    
        if($date != null)
        {
            $arr['date'] = $date;
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
        
        if($status == self::STATUS_0)
        {
            $arr['status'] = $status;
            $arr['date'] = array('EGT', date('Y-m-d',time()));
        }
        if($status == self::STATUS_1)
        {
            $arr['status'] = $status;
        }
        if($status == self::STATUS_2)
        {
            $arr['date'] = array('LT', date('Y-m-d',time()));
        }
        
        $count = $this->db->where($arr)->count();
        
        return $count;
    }
    
    /**
     * 获取一条访客信息
     *
     * @param int $admin_id
     * @return
     */
    public function getOnce($id)
    {
        $arr['id'] = $id;
        
        $arr = $this->db->where($arr)->find();
    
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
        $arr['openid'] = $openid;
    
        $arr = $this->db->where($arr)->select();
    
        return $arr;
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
    
    /**
     * 更新一条访客信息
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