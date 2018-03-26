<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class ActivityTypeModel extends MSDaoBase{
    const  TABLENAME = 'integral_type';
    const  SYSTEM_0 = 0;//积分商城
    const  SYSTEM_1 = 1;//活动券
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME); 
    }
    
    /**
     * 获取活动信息
     *
     * @param int $admin_id
     * @param int $system
     * @param int $disable
     * @return
     */
    public function getAll($admin_id, $system, $disable)
    {
        $arr['admin_id'] = $admin_id;
        $arr['system'] = $system;
        
        if($disable != 2)//2是查全部
        {
            $arr['disable'] = $disable;
        }
        
        $arr = $this->db->where($arr)->select();
    
        return $arr;
    }
    
    /**
     * 按id获取活动信息
     *
     * @param int $id
     * @return
     */
    public function getById($id, $system)
    {
        $arr['id'] = $id;
        $arr['system'] = $system;
        
        $arr = $this->db->where($arr)->find();
    
        return $arr;
    }
    
    /**
     * 按name获取活动信息
     *
     * @param int $name
     * @return
     */
    public function getByName($admin_id, $name, $system)
    {
        $arr['admin_id'] = $admin_id;
        $arr['type_name'] = $name;
        $arr['system'] = $system;
    
        $arr = $this->db->where($arr)->find();
    
        return $arr;
    }
    
    /**
     * 插入一条活动券信息
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
     * 更新一条活动券信息
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
    
    /**
     * 删除一条活动券信息
     *
     * @param int $id
     * @return
     */
    public function delById($id)
    {
        $this->db->where(array('id'=>$id))->delete();
    
        return $id;
    }
}
?>