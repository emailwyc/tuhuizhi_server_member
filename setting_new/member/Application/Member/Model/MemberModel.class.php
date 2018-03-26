<?php
namespace Member\Model;
use common\MSDaoBase;

class MemberModel extends MSDaoBase{
    const  TABLENAME = 'mem';
    public $db;
    
    public function __construct($pre_table)
    {
        $this->db = M(self::TABLENAME, $pre_table);
    }
    
    /**
     * 按条件获取全部成员
     * @param string $field
     * @param string $where
     * @return int
     */
    public function getAll($field, $where)
    {
        $arr = $this->db->field($field)->where($where)->order("`id` DESC")->select();
    
        return $arr;
    }
}
?>