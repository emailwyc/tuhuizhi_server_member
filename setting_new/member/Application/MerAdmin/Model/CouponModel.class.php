<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class CouponModel extends MSDaoBase{
    const  TABLENAME = 'total_coupon';
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 根据Id获取互动券信息
     *
     * @param int $id
     * @return
     */
    public function getById($id)
    {
        $arr = $this->db->where(array('id'=>$id))->find();
    
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
    
    /**
     * 更新一条活动券信息
     *
     * @param int $id
     * @param arr $arr
     * @return
     */
    public function deleteById($id)
    {
        $this->db->where(array('id'=>$id))->delete();
    
        return true;
    }
    
    /**
     * 获取活动券总量
     * @param string $where
     * @return int
     */
    public function getCount($where)
    {
        $count = $this->db->field("count(*) as count")->where($where)->select();
    
        return $count;
    }
    
    /**
     * 获取全部活动券
     * @param string $field
     * @param string $where
     * @return int
     */
    public function getAll($field, $where)
    {
        $arr = $this->db->field($field)->where($where)->order("`id` DESC")->select();//->limit($start,$offset)
    
        return $arr;
    }
}
?>