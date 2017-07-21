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
	    $db=M('catalog','total_');
	    $res=$db->where(array('status'=>array('eq',1)))->select();
	    foreach($res as $k=>$v){
	        $arr[]=$v['id'];
	        $res[$k]['ver']='';
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
	
	
	
}