<?php
namespace MerAdmin\Model;
use Think\Model;

class PropertyModel extends Model{
 //可加入字段-验证规则-参数绑定等，数据分表请使用高级模型
    //缓存规则前缀(模块：模型：key+),且字母小写
    protected $tableName = 'property_notice';
//     protected $tablePrefix = '';
    //protected $_validate = array();可以定义字段验证规则
    public function _initialize(){
        parent::_initialize();
    }
    
    
    /**
     * 　获取最大排序值
     */
    public function getOnceTop($id,$sort = 2){
        
        $arr=$this->where(array('id'=>$id))->save(array('sort'=>$sort));
    
        if($arr){
            
            $res=$this->where(array('id'=>array('neq',$id)))->save(array('sort'=>1));
            
            if($res){
                
                return true;
                
            }else{
                
                return false;
                
            }
            
        }else{
            
            return false;
            
        }

    }
    
    
    /**
     * 　添加物业公告
     */
    public function adds($data){
        
        $res=$this->find();
        
        if(!$res){
            $data['sort']=2;
        }
        
        $res=$this->add($data);
        
        return $res;
    }
    
    
    /**
     * 　物业公告列表
     */
    public function getList($name,$lines,$page){
        
        if($name){
            $where['title']=array('like',array('%'.$name.'%'));
        }
        
        $where['status']=array('eq',1);//避免变量where为空

        $num=$this->where($where)->count();
        
        if($lines){
            $start=($page-1)*$lines;
            
            $arr['data']=$this->where($where)->order('sort desc,datetime desc')->limit($start,$lines)->select();
            
            foreach($arr['data'] as $key=>$val){
                $arr['data'][$key]['content']=html_entity_decode($val['content']);
            }
            
            $arr['count']=$num;
            $arr['num_page']=ceil($num/$lines);
            $arr['page']=$page;
            
        }else{
            
            $arr['data']=$this->where($where)->order('sort desc')->select();
            
        }

        return $arr;
    }
    
    
    /**
     * 　物业公告详情
     */
    public function getOnce($id){
    
        $arr=$this->where(array('id'=>$id))->select();
    
        $arr[0]['content']=html_entity_decode($arr[0]['content']);
        
        return $arr;
    }
    
    
    /**
     * 　修改物业公告
     */
    public function getOnceSave($id,$data){
    
        $arr=$this->where(array('id'=>$id))->save($data);
    
        return $arr;
    }
    
    
    /**
     * 　删除物业公告
     */
    public function getOnceDel($id){
    
        $data=$this->where(array('id'=>$id))->find();
        
        if($data['sort']==2){
            
            $top_data=$this->where(array('id'=>array('neq',$id)))->order('datetime desc')->find();
            
            if($top_data['sort']==1){
                $this->where(array('id'=>$top_data['id']))->save(array('sort'=>2));
            }
            
        }
        
        $arr=$this->where(array('id'=>$id))->delete();

        return $arr;
    }
}
?>