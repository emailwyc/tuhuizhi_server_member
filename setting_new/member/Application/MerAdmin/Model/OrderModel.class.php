<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class OrderModel extends MSDaoBase{
    const  TABLENAME = 'total_order';
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取订单信息
     *
     * @return
     */
    public function getAll()
    {
     
        $arr = $this->db->where('')->select();
    
        return $arr;
    }
    
    /**
     * 获取一条订单信息
     *
     * @param int $id
     * @return
     */
    public function getOnce($orderNo)
    {
        $arr['orderno'] = $orderNo;
        
        $arr = $this->db->where($arr)->find();
        
        return $arr;
    }
    
    /**
     * 插入一条订单信息
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
     * 更新一条订单信息
     *
     * @param int $orderNo
     * @param arr $arr
     * @return
     */
    public function updateById($orderNo, $arr)
    {
        $this->db->where(array('orderno'=>$orderNo))->save($arr);
    
        return $id;
    }
}
?>