<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\ActivityModel;

class AppletConfigService{
    
    /** 
     * AppletConfig对象
     *
     * @var MerAdmin\Model\AppletConfigModel
     */
    public $applet_config_model;//小程序配置配置model
    
    public function __construct()
    {
        $this->applet_config_model = Singleton::getModel('MerAdmin\\Model\\AppletConfigModel');
    }
    
    /**
     * 获取一条小程序配置信息
     *
     * @param int $admin_id
     * @return
     */
    public function getOnce($admin_id)
    {
        $arr = $this->applet_config_model->getOnce($admin_id);
    
        return $arr;
    }
    
    /**
     * 插入一条小程序配置信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->applet_config_model->add($insert);
    
        return $lastid;
    }
    
    /**
     * 更新一条小程序配置信息
     *
     * @param int $id
     * @param arr $arr
     * @return
     */
    public function updateById($id, $arr)
    {
        $this->applet_config_model->updateById($id, $arr);
    
        return $id;
    }
}
?>