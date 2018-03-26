<?php
namespace MerAdmin\Service;
use Common\core\Singleton;

class CouponService{
    
    /**
     * CouponModel对象
     *
     * @var MerAdmin\Model\CouponModel
     */
    public $coupon_model;//活动券model
    
    public function __construct()
    {
        $this->coupon_model = Singleton::getModel('MerAdmin\\Model\\CouponModel');//活动券model
    }
    
    /**
     * 根据Id获取互动券信息
     *
     * @param int $id
     * @return
     */
    public function getById($id)
    {
        $arr = $this->coupon_model->getById($id);
        
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
        $lastid = $this->coupon_model->add($insert);
        
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
        $this->coupon_model->updateById($id, $arr);
        
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
        $this->coupon_model->deleteById($id);
    
        return true;
    }
    
    /**
     * 获取活动券总量
     * @param string $where
     * @return int 
     */
    public function getCount($where)
    {
        $count = $this->coupon_model->getCount($where);
    
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
        $arr = $this->coupon_model->getAll($field, $where);
        
        return $arr;
    }
}
?>