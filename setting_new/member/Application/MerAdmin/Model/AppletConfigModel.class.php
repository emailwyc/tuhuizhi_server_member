<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class AppletConfigModel extends MSDaoBase{
    const  TABLENAME = 'applet_config';
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 根据Id获取小程序配置信息
     *
     * @param int $adminid
     * @return
     */
    public function getOnce($adminid)
    {
        $arr = $this->db->where(array('adminid'=>$adminid))->find();
    
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
        $lastid = $this->db->add($insert);
    
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
        $this->db->where(array('id'=>$id))->save($arr);
    
        return $id;
    }
}
?>