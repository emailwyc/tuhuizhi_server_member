<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\ActivityModel;

//小程序券日志
class AppletCouponLogService{
    
    /** 
     * AppletCouponLogModel对象
     *
     * @var MerAdmin\Model\AppletCouponLogModel
     */
    public $applet_coupon_log_model;//小程序商户model
    
    public function __construct()
    {
        $this->applet_coupon_log_model = Singleton::getModel('MerAdmin\\Model\\AppletCouponLogModel');
    }
    
    /**
     * 获取小程序券日志信息
     *
     * @param int $adminid
     * @param int $shopid
     * @return
     */
    public function getAll($adminid, $shopid)
    {
        $arr = $this->applet_coupon_log_model->getAll($adminid, $shopid);
    
        return $arr;
    }
    
    /**
     * 根据条件获取领取数量
     *
     * @param int $param
     * @return
     */
    public function getNum($param)
    {
        $arr = $this->applet_coupon_log_model->getNum($param);
    
        return $arr;
    }
    
    
    /**
     * 插入一条小程序券日志信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->applet_coupon_log_model->add($insert);
    
        return $lastid;
    }
}
?>