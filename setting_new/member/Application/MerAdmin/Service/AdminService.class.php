<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\ActivityModel;

//场馆信息
class AdminService{
    
    /**
     * Admin对象
     *
     * @var MerAdmin\Model\AdminModel
     */
    public $admin_model;//场馆信息model
    
    public function __construct()
    {
        $this->admin_model = Singleton::getModel('MerAdmin\\Model\\AdminModel');
    }
    
    /**
     * 获取场馆信息
     *
     * @return
     */
    public function getAll()
    {
        $arr = $this->admin_model->getAll();
        
        return $arr;
    }
    
    /**
     * 获取一条场馆信息
     *
     * @param int $admin_id
     * @param int $type
     * @param int $buildid
     * @param int $system
     * @return
     */
    public function getByUkey($ukey)
    {
        $arr = $this->admin_model->getByUkey($ukey);
    
        return $arr;
    }
    
    /**
     * 插入一条场馆信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->admin_model->add($insert);
    
        return $lastid;
    }
    
    /**
     * 更新一条场馆信息
     *
     * @param int $id
     * @param arr $arr
     * @return
     */
    public function updateById($id, $arr)
    {
        $this->admin_model->updateById($id, $arr);
    
        return $id;
    }
}
?>