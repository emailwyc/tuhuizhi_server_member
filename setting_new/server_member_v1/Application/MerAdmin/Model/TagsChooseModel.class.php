<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class TagsChooseModel extends MSDaoBase{
    const  TABLENAME = 'total_tags_choose';
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME);
    }
    
    /**
     * 获取一条标签信息
     *
     * @param int $id
     * @return
     */
    public function getOnce($adminid, $openid)
    {
        $arr['adminid'] = $adminid;
        $arr['openid'] = $openid;
    
        $arr = $this->db->where($arr)->find();
    
        return $arr;
    }
    
    /**
     * 插入一条标签信息
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
     * 更新一条标签信息
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