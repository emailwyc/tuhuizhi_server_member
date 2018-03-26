<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class AppletShopModel extends MSDaoBase{
    const  TABLENAME = 'applet_shop';
    
    const  STATUS_0 = 0;//上架
    const  STATUS_1 = 1;//下架
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 根据adminid获取小程序商户信息
     *
     * @param int $adminId
     * @return
     */
    public function getAll($adminId, $status)
    {
        $arr['adminid'] = $adminId;
        $arr['status'] = $status;
        
        $arr = $this->db->where($arr)->order('sort DESC')->select();

        return $arr;
    }
    
    /**
     * 根据name获取小程序商户信息
     *
     * @param string $name
     * @return
     */
    public function getAllByName($name)
    {
        $arr['shopName'] = array('LIKE', $name.'%');
        
        $arr = $this->db->where($arr)->order('sort DESC')->select();
    
        return $arr;
    }
    
    /**
     * 根据shopId获取小程序商户信息
     *
     * @param int $shopId
     * @return
     */
    public function getOnce($shopId)
    {
        $arr = $this->db->where(array('shopId' => $shopId))->find();
        
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
        $lastid = $this->db->add($insert);
    
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
        $this->db->where(array('id'=>$id))->save($arr);
    
        return $id;
    }
}
?>