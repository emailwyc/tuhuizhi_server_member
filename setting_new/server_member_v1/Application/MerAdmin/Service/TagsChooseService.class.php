<?php
namespace MerAdmin\Service;
use Common\core\Singleton;

class TagsChooseService{
    
    /** 
     * TagsChoose对象
     *
     * @var MerAdmin\Model\TagsChooseModel
     */
    public $tags_choose_model;
    
    public function __construct()
    {
        $this->tags_choose_model = Singleton::getModel('MerAdmin\\Model\\TagsChooseModel');
    }
    
    /**
     * 获取一条标签信息
     *
     * @param int $adminid
     * @param int openid
     * @return
     */
    public function getOnce($adminid, $openid)
    {
        $arr = $this->tags_choose_model->getOnce($adminid, $openid);

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
        $lastid = $this->tags_choose_model->add($insert);
    
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
        $this->tags_choose_model->updateById($id, $arr);
    
        return $id;
    }
    
    
    
}
?>