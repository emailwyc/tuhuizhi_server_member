<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use Common\Controller\RedisController;

//订单Service
class OrderService{
     /**
     * OrderModel对象
     *
     * @var MerAdmin\Model\OrderModel
     */
    public $order_model;
    
    public function __construct($pre_table)
    {
        $this->order_model = Singleton::getModel('MerAdmin\\Model\\OrderModel',$pre_table);//订单model
    }
    
    /**
     * 获取订单信息
     * @return
     */
    public function getAll()
    {
        $arr = $this->order_model->getAll();
    
        return $arr;
    }
    
    /**
     * 按订单好获取一条订单信息
     *
     * @param int $orderNo
     * @return
     */
    public function getOnceByOrderNo($orderNo)
    {
        $arr = $this->order_model->getOnce($orderNo);
    
        return $arr;
    }
    
    /**
     * 插入一条订单信息
     *
     * @param int $amount
     * @param int $openid
     * @param int $outTradeNo
     * @return int
     */
    public function add($amount, $openid, $outTradeNo, $adminid, $outsource_orderno, $payType)
    {
        $insert['orderno'] = $outTradeNo;
        $insert['openid'] = $openid;
        $insert['userid'] = '';
        $insert['total_fee'] = $amount;
        $insert['status'] = 0;
        $insert['createtime'] = time();
        $insert['pay_time'] = 0;
        $insert['admin_id'] = $adminid;
        $insert['paytype'] = $payType;
        $insert['outsource_orderno'] = $outsource_orderno;
        
        $lastid = $this->order_model->add($insert);
    
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
        $this->order_model->updateById($orderNo, $arr);
    
        return $id;
    }
    
}

?>