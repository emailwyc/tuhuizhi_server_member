<?php
namespace MerAdmin\Model;
use Think\Model;

class HydropowerModel extends Model{
 //可加入字段-验证规则-参数绑定等，数据分表请使用高级模型
    //缓存规则前缀(模块：模型：key+),且字母小写
    protected $tableName = 'hydropower';
//     protected $tablePrefix = '';
    //protected $_validate = array();可以定义字段验证规则
    public function _initialize(){
        parent::_initialize();
    }
    
    /**
     *　水电记录
     */
    public function getHydropowerListFloor($floor , $unit , $door,$lines,$page){
        if($floor){
            $where['floor']=array('eq',$floor);    
        }
        
        if ($unit){
            $where['unit']=array('eq',$unit);    
        }
        
        if ($door){
            $where['door']=array('eq',$door);
        }
        
        $where['status']=array('eq',1);//查询正常
        $where['is_category']=array('eq',1);//查询首页显示内容   
        
        $num=$this->where($where)->count();
        
        if($lines){
            
            $start=($page-1)*$lines;
            
            $data['data']=$this->where($where)->order('floor asc,unit asc,door asc')->limit($start,$lines)->select();
            
            $data['page']=$page;
            $data['num_page']=ceil($num/$lines);
            $data['num']=$num;
        }else{
            
            $data['data']=$this->where($where)->order('floor asc,unit asc,door asc')->select();
            
        }     
        
        return $data;
    }
    
    
    /**
     * 　水电详情
     */
    public function getHydropowerDoorOnce($floor , $unit , $door , $lines , $page){
        
        $where['floor']=array('eq',$floor);
        $where['unit']=array('eq',$unit);
        $where['door']=array('eq',$door);
        
        $num=$this->where($where)->count();
        if($num){
            
            $start=($page-1)*$lines;

            $arr=$this->where($where)->order('uptime desc')->limit($start,$lines)->select();
            
            $data['data']=$arr;
            $data['num']=$num;
            $data['num_page']=ceil($num/$lines);
            $data['page']=$page;
            return $data;
        }else{
            return false;
        }
    }
    
    
    /**
     * 　水电记录操作
     */
    public function setHydropowerDoorSave($floor , $unit , $door , $data){
        
        $where['floor']=array('eq',$floor);
        $where['unit']=array('eq',$unit);
        $where['door']=array('eq',$door);
        $where['is_category']=array('eq',1);
        
        $res=$this->where($where)->find();
        
        if($res){
            $etc=$this->where(array('id'=>$res['id']))->save(array('is_category'=>2));
            
            if(!$etc){
                return false;die;
            }
        }
        
        $ret=$this->add($data);
        
        return $ret;
    }
    
    
    /**
     * 　水电删除操作
     */
    public function setHydropowerDel($floor , $unit , $door){
        
        $where['floor']=array('eq',$floor);
        $where['unit']=array('eq',$unit);
        $where['door']=array('eq',$door);
        
        $ret=$this->where($where)->save(array('status'=>2));
        
        return $ret;
    }
    
    
    /**
     * 　水电最后更新日期
     */
    public function getHydropowerOnce($floor , $unit , $door){
    
        $where['floor']=array('eq',$floor);
        $where['unit']=array('eq',$unit);
        $where['door']=array('eq',$door);
        $where['status']=array('eq',1);//查询正常
        $where['is_category']=array('eq',1);//查询首页显示内容
        
        $ret=$this->where($where)->find();
    
        return $ret;
    }
    
}
?>