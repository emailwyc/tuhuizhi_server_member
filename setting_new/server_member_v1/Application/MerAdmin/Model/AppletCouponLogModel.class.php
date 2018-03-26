<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class AppletCouponLogModel extends MSDaoBase{
    const  TABLENAME = 'applet_coupon_log';
    
    const  TYPE_0 = 0;//发券
    const  TYPE_1 = 1;//核销
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 根据adminid获取小程序券日志信息
     *
     * @param int $adminid
     * @param int $shopid
     * @return
     */
    public function getAll($adminid, $shopid)
    {
        $arr['adminid'] = $adminid;
        $arr['shopId'] = $shopid;
        
        $res = $this->db->where($arr)->select();
    
        return $res;
    }
    
    /**
     * 根据条件获取领取数量
     *
     * @param arr    $param
     * @return
     */
    public function getNum($param)
    {
        $res = $this->db->where($param)->count();
    
        return $res;
    }
    
    /**
     * 插入一条小程序券日志信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->db->add($insert);
    
        return $lastid;
    }
    
}
?>