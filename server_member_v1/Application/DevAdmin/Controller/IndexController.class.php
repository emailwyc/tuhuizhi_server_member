<?php
namespace DevAdmin\Controller;
use Think\Controller;
use Common\Controller\CommonController;

class IndexController extends CommonController {
	public function _initialize(){
		parent::__initialize();
	}
    /**
	 * 登录
	 * @param $name $pwd
     * @return mixed
	 */
	public function login(){
		
		$name=I("name");
		$pwd=I("pwd");
		if(empty($name) || empty($pwd)){
			$msg=array('code'=>1030);	
		}else{
			$db=M('admin','develope_');
			$res=$db->where(array('name'=>$name))->find();
			if(empty($res)){
				$msg=array('code'=>2000);
			}else{
				if($res['password']!=md5($pwd)){
					$msg=array('code'=>500);
				}else{
					unset($res['password']);
					session($res['ukey'],$res['signkey']);
					$res_data=array('ukey'=>$res['ukey']);
					$msg=array('code'=>200,'data'=>$res_data);
				}
			}
		}	
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	

	public function out(){
		session($this->ukey,null);
		echo returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();
	}
	
	/**
	 * 获取所有功能和对应的版本信息
	 */
	public function get_cat_ner_list(){
	    $params['type_id'] = I('type_id');
	    $db=M('catalog','total_');
	    $where['status'] = array('eq',1);
	    if($params['type_id']!=''){
	        $where['type_id'] = array('eq',$params['type_id']);
	    }
	    $res=$db->where($where)->select();
	    foreach($res as $k=>$v){
	        $arr[]=$v['id'];
// 	        $res[$k]['ver']='';
	    }
	    $str=implode(',', $arr);
	    if(empty($arr)){
	        echo returnjson(array('code'=>102),$this->returnstyle,$this->callback);exit();
	    }
	    $ver_db=M('version','total_');
	    $where['type_id']=array('in',$str);
	    $where['status']=array('eq',1);
	    $where['_logic']='and';
	    $ver_arr=$ver_db->where($where)->select();
	
	    foreach($res as $k=>$v){
	        foreach($ver_arr as $key=>$val){
	            if($v['id'] == $val['type_id']){
	                $res[$k]['ver'][]=$val;
	            }
	        }
	    }
	
	    $msg['code']=200;
	    $msg['data']=$res;
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	/**
	 *  获取我的收藏跳转地址
	 */
	public function  MycollectionUrl(){
	    $params['type_id'] = I('type_id');
	    $params['column_id'] = I('column_id');
	    
	    if(in_array('', $params)){
	        returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
	    }
	    $adminInfo = $this->getMerchant($this->ukey);
	    
	    //获取绑定版本ID
	    $db=M('version_url','total_');
	    $where['adminid']=array('eq',$adminInfo['id']);
	    $where['type_id']=array('eq',$params['type_id']);
	    $where['_logic']='and';
	    $arr=$db->where($where)->find();
	    if(!$arr){
	        returnjson(array('code'=>102,'data'=>'版本ID有误'),$this->returnstyle,$this->callback);exit();
	    }
	    $vers_column_db=M('version_column','total_');
	    $where['catalog_id']=array('eq',$params['type_id']);
	    $where['version_id']=array('eq',$arr['version_id']);
	    $where['column_id']=array('eq',$params['column_id']);
	    $data = $vers_column_db->where($where)->find();

	    if(!$data || $data['url'] == ''){
	        returnjson(array('code'=>102,'data'=>'未获取跳转地址'),$this->returnstyle,$this->callback);exit();
	    }
	    
	    $data['url']=str_replace('{key_admin}',$this->ukey , $data['url']);
	    returnjson(array('code'=>200,'data'=>$data),$this->returnstyle,$this->callback);exit();
	} 
	
	
	
	
	//拉去所有栏目数据
	public function get_cloumn(){
	    set_time_limit(0);
	    
	    $db = M('admin_auth','total_');
	    
	    $auth_db = M('auth_admin','total_');
	    
	    $auth_arr = $auth_db->select();
	    
	    foreach($auth_arr as $k=>$v){
	        
	        $auth = json_decode($v['check_auth'],true);
	        
	        foreach($auth as $key=>$val){
	            
	            $data['admin_id'] = $v['admin_id'];
	            $data['auth_id'] = $val['id'];
	            $data['show_status'] = $val['column_html']==''?2:1;
	            $data['is_often'] = 2;
	            
	            $res = $db->add($data);
	            
	            if($res){
	                echo "成功";
	            }else{
	                echo "失败!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!";
	            }
	            
	        }
	        
	    }
	}
	
	
	//拉去所有常用栏目
	public function get_often_cloumn(){
	    
	    $auth_db = M('auth_often','total_');
	    
// 	    $auth_db_admin = M('auth_admin','total_');
	    
	    $db = M('admin_auth','total_');
	    
// 	    $admin_auth = $auth_db_admin->field('auth_id,admin_id')->find();
	    
	    $auth_arr = $auth_db->select();
	    
	    foreach($auth_arr as $k=>$v){
	        
	        $auth = json_decode($v['often_column'],true);
	        
	        foreach($auth as $key=>$val){
	            
	            $auth_often = $db->where(array('admin_id'=>$v['admin_id'],'auth_id'=>$val['id']))->find();
	            if($auth_often){
	                
	                $res = $db->where(array('admin_id'=>$v['admin_id'],'auth_id'=>$val['id']))->save(array('is_often'=>1));
	                
	                if($res){
	                    echo "success";
	                }else{
	                    echo "啊啊啊啊啊啊啊";
	                }
	                
	            }else{
	                echo "失败了呀兄弟~~~~~~~~~~~~~~~~~~~~~`";
	                
	            }
	            
	        }
	    }
	}
	
	
	//拉取子账号栏目
	public function get_child_auth(){
	    $db = M('admin_child','total_');
	    
	    $arr = $db->select();
	    
	    
        foreach($arr as $k=>$v){
            
            $column = json_decode($v['column'],true);
            
            foreach($column as $key=>$val){
               
                $auth_id[]= $val['id']; 
                
            }
            $auth_str = implode(',', $auth_id);
            $res = $db->where(array('id'=>$v['id']))->save(array('auth_id'=>$auth_str));
            
            if(!$res){
                echo "失败了呀兄弟~~~~~~~~~~";
            }else{
                echo "yes";
            }
            unset($auth_id);
        }
	}
	
}