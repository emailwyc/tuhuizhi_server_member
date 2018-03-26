<?php
namespace Member\Service;
use Common\core\Singleton;
use Common\Controller\RedisController;

class MemberService{
    
    /**
     * MemberModel对象
     *
     * @var Member\Model\MemberModel
     */
    public $member_model;
    
    public function __construct($pre_table)
    {
        $this->member_model = Singleton::getModel('Member\\Model\\MemberModel',$pre_table);//成员model
    }
    
    /**
     * 获取全部openid非空的成员
     * @param string $field
     * @param string $where
     * @return int
     */
    public function getAllByOpenIdNotNull()
    {
        $arr = $this->member_model->getAll('*', array('openid'=>array('neq','')));
        
        return $arr;
    }
     
}

?>