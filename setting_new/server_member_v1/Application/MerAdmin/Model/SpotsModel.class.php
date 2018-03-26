<?php
namespace MerAdmin\Model;
use Think\Model;

class SpotsModel extends Model{
 //可加入字段-验证规则-参数绑定等，数据分表请使用高级模型
    //缓存规则前缀(模块：模型：key+),且字母小写
    protected $tableName = 'spots';
//     protected $tablePrefix = '';
    //protected $_validate = array();可以定义字段验证规则
    public function _initialize(){
        parent::_initialize();
    }

    /**    搜索景点    **/
    public function getSpotsData($where,$name,$sort,$startlines,$lines){
        
        if($name){
            $where['sitename'] = array('like','%'.$name.'%');
            $where['_logic'] = 'and';
        }
        
        $arr = $this->where($where)->order($sort)->limit($startlines,$lines)->select();
        
        return $arr;
    }
    
    /**    景点总数    **/
    public function getSpotsDataCount($where,$name = ''){
    
        if($name){
            $where['sitename'] = array('like','%'.$name.'%');
            $where['_logic'] = 'and';
        }
        
        $num = $this->where($where)->count();
    
        return $num;
    }
    
    /**    景点添加    **/
    public function getSpotsDataAdd($data){
    
        $res = $this->add($data);
    
        return $res;
    }
    
    /**    单条景点    **/
    public function getSpotsDataOnce($where){
    
        $data = $this->where($where)->find();
    
        return $data;
    }
    
    /**    景点删除    **/
    public function getSpotsDataDel($id){
    
        $res = $this->where(array('id'=>$id))->delete();
    
        return $res;
    }
 
    /**    排序操作    **/
    public function getSpotsSortAction($sort,$around_id){
    
        $where['around_id'] = array('eq',$around_id);
        $where['sort'] = array('gt',$sort);
        $where['_logic'] = 'and';
        $res = $this->where($where)->setDec('sort',1);
    
        return $res;
    }
    
    /**    景点置顶    **/
    public function getSpotsSortActionTop($sort,$around_id){
    
        $where['around_id'] = array('eq',$around_id);
        $where['sort'] = array('lt',$sort);
        $where['_logic'] = 'and';
        $res = $this->where($where)->setInc('sort',1);
    
        return $res;
    }
    
    /**    景点修改    **/
    public function setSpotsDataOnceSave($id , $data){
        
        $res = $this->where(array('id'=>$id))->save($data);
        
        return $res;
    }
    
    /**    上移下移    **/
    public function setSpotsSort($up,$down){
        
        $arr['up'] = $this->where(array('id'=>$up))->setDec('sort',1);

        $arr['down'] = $this->where(array('id'=>$down))->setInc('sort',1);

        return $arr;
    }
    
}
?>