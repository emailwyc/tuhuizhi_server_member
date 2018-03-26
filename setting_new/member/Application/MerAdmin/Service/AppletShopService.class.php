<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\ActivityModel;

//小程序商户
class AppletShopService{
    
    /** 
     * AppletShop对象
     *
     * @var MerAdmin\Model\AppletShopModel
     */
    public $applet_shop_model;//小程序商户model
    
    public function __construct()
    {
        $this->applet_shop_model = Singleton::getModel('MerAdmin\\Model\\AppletShopModel');
    }
    
    /**
     * 获取小程序商户信息
     *
     * @param int $shopId
     * @return
     */
    public function getAll($adminId, $status = 0)
    {
        $arr = $this->applet_shop_model->getAll($adminId, $status);
    
        return $arr;
    }
    
    /**
     * 根据名字获取小程序商户信息
     *
     * @param int $name
     * @return
     */
    public function getByName($name)
    {
        $arr = $this->applet_shop_model->getAllByName($name);
    
        return $arr;
    }
    
    /**
     * 获取一条小程序商户信息
     *
     * @param int $shopId
     * @return
     */
    public function getOnce($shopId)
    {
        $arr = $this->applet_shop_model->getOnce($shopId);
    
        return $arr;
    }
    
    /**
     * 插入一条小程序商户信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->applet_shop_model->add($insert);
    
        return $lastid;
    }
    
    /**
     * 更新一条小程序商户信息
     *
     * @param int $id
     * @param arr $arr
     * @return
     */
    public function updateById($id, $arr)
    {
        $this->applet_shop_model->updateById($id, $arr);
    
        return $id;
    }
}
?>