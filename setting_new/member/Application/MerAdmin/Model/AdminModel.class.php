<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class AdminModel extends MSDaoBase{
    const  TABLENAME = 'total_admin';
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取场馆信息
     *
     * @return
     */
    public function getAll()
    {
        $arr = $this->db->where('')->select();
    
        return $arr;
    }
    
    /**
     * 获取一条场馆信息
     *
     * @param int $ukey
     * @return
     */
    public function getByUkey($ukey)
    {
        $arr['ukey'] = $ukey;
        
        $arr = $this->db->where($arr)->find();
    
        return $arr;
    }
    
    /**
     * 插入一条场馆券信息
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
     * 更新一条场馆券信息
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