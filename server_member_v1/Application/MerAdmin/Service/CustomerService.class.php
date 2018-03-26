<?php
namespace MerAdmin\Service;
use Common\core\Singleton;

//客服信息
class CustomerService{
    
    /**
     * Customer对象
     *
     * @var MerAdmin\Model\CustomerModel
     */
    public $customer_model;//投诉model
    
    public function __construct()
    {
        $this->customer_model = Singleton::getModel('MerAdmin\\Model\\CustomerModel');
    }
    
    /**
     * 获取客服信息
     *
     * @return
     */
    public function getAll($adminid)
    {
        $arr = $this->customer_model->getAll($adminid);
        
        return $arr;
    }
    
    /**
     * 获取一条客服信息
     *
     * @param int $id
     * @return
     */
    public function getOnce($id)
    {
        $arr = $this->customer_model->getOnce($id);
    
        return $arr;
    }
    
    /**
     * 插入一条客服信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->customer_model->add($insert);
    
        return $lastid;
    }
    
    /**
     * 删除一条客服信息
     *
     * @param int $id
     * @return
     */
    public function delById($id)
    {
        $this->customer_model->delById($id);
    
        return $id;
    }
}
?>