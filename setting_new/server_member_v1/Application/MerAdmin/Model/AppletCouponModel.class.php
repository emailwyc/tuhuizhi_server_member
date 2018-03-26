<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class AppletCouponModel extends MSDaoBase{
    const  TABLENAME = 'applet_coupon';
    const  ONLINE_1 = 1;//上线
    const  ONLINE_2 = 2;//下线
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 根据adminid获取小程序券信息
     *
     * @param int $adminid
     * @param int $shopid
     * @param int $isDesc
     * @return
     */
    public function getAll($adminid, $shopid, $online)
    {
        $arr['adminid'] = $adminid;
        $arr['shopId'] = $shopid;
        
        if($online != 0)
        {
            $arr['online'] = $online;
        }
        
        $res = $this->db->where($arr)->order('sort DESC')->select();
    
        return $res;
    }
    
    /**
     * 根据$couponId获取小程序券信息
     *
     * @param int $couponId
     * @param int $couponActivityId
     * @return
     */
    public function getOnce($couponId, $couponActivityId)
    {
        $arr['couponId'] = $couponId;
        
        if($couponActivityId)
        {
            $arr['couponActivityId'] = $couponActivityId;
        }
        
        $data = $this->db->where($arr)->find();
        
        return $data;
    }
    
    /**
     * 插入一条小程序券信息
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
     * 更新一条小程序券信息
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
}
?>