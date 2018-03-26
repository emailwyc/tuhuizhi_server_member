<?php
namespace MerAdmin\Model;
use Think\Model;

class AroundModel extends Model{
 //可加入字段-验证规则-参数绑定等，数据分表请使用高级模型
    //缓存规则前缀(模块：模型：key+),且字母小写
    protected $tableName = 'around';
//     protected $tablePrefix = '';
    //protected $_validate = array();可以定义字段验证规则
    public function _initialize(){
        parent::_initialize();
    }
    
    /**    分类列表    **/
    public function getDataList($status,$order){

        $arr['status'] = $status;
        
        $arr = $this->where($arr)->order($order)->select();
        
        return $arr;
        
    }
    
    /**    分类总数    **/
    public function getDataCount($status){
        
        $arr['status'] = $status;
        
        $arr = $this->where($arr)->count();
        
        return $arr;
        
    }
    
    /**    分类添加    **/
    public function getDataInster($data){
    
        $arr = $this->add($data);
    
        return $arr;
    
    }
    
    /**    单条分类    **/
    public function getDataOnce($where){
    
        $arr = $this->where($where)->find();
    
        return $arr;
    
    }
    
    /**    分类删除    **/
    public function getDataOnceDel($where){
    
        $arr = $this->where($where)->delete();
    
        return $arr;
    
    }
    
    /**    排序操作    **/
    public function getSortAction($sort){
        
       $arr = $this->where(array('sort'=>array('GT',$sort)))->setDec('sort',1);
       return $arr;
    }
    
    /**    排序操作    **/
    public function getSortActionTop($sort){
    
        $arr = $this->where(array('sort'=>array('LT',$sort)))->setInc('sort',1);
        return $arr;
    }
    
    /**    上移下移    **/
    public function setSort($up , $down){
    
        $arr['up'] = $this->where(array('id'=>$up))->setDec('sort',1);
        
        $arr['down'] = $this->where(array('id'=>$down))->setInc('sort',1);
        
        return $arr;
    }
    
    /**    分类修改    **/
    public function setDataSave($id,$data){
        
        $arr = $this->where(array('id'=>$id))->save($data);
        
        return $arr;
    }
    
}
?>