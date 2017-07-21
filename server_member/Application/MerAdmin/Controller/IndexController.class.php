<?php
/**
 * Created by EditPlus.
 * User: zhanghang
 * Date: 6/24/16
 * Time: 10:10 AM
 */


namespace MerAdmin\Controller;

use Common\Controller\CommonController;

class IndexController extends CommonController {
	public function _initialize(){
		parent::__initialize();
	}
    /**
	 * 登录
	 * @param $name 
	 * @param $pwd
     * @return mixed
	 */
	public function login(){
		$name=I("name");
		$pwd=I("pwd");
		if(empty($name) || empty($pwd)){
			$msg=array('code'=>1030);	
		}else{
			//查询商户信息
			$db=M('admin','total_');
			$res=$db->where(array('name'=>$name))->find();
			$isChild = false;
			if(empty($res)){
                $db2=M('admin_child','total_');
                $res=$db2->where(array('name'=>$name))->find();
                $isChild = true;
            }
			if(empty($res)){
				$msg=array('code'=>2000);
			}else{
				//验证密码
				if($res['password']!=md5($pwd)){
                    returnjson(array('code'=>500),$this->returnstyle,$this->callback);exit;
				}
                $childAccId = !$isChild?"":$res['id'];
				if($isChild){
				    $res = $db->where(array('id'=>$res['admin_id']))->find();
                    if(empty($res)){ returnjson(array('code'=>500),$this->returnstyle,$this->callback);exit;}
                }
                //存入session
                unset($res['password']);
                //session($res['ukey'].'zhihuitu',$res['signkey']);
                //session(array('admin_name'=>$res['ukey'],'expire'=>60));
                $this->redis->set($res['ukey'].'MerAdmin'.$childAccId, $res['signkey']);//一小时
                $this->redis->expire($res['ukey'].'MerAdmin'.$childAccId,1800);
                $res_data=array('ukey'=>$res['ukey'],'name'=>$res['describe'],'childid'=>$childAccId);
                $msg=array('code'=>200,'data'=>$res_data);
			}
		}	
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	//退出接口
	public function out(){
		$this->redis->del($this->ukey.'MerAdmin');//一小时
		echo returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();
		
	}


	/**
	 * 获取商户栏目列表
	 * @param $key_admin
     * @return mixed
	 */
	public function jurisdiction_list(){
        $childid=I('childid');
        $reskey = $childid=="ismaster"?"":$childid;
		$admin_arr=$this->getMerchant($this->ukey);
		if(empty($admin_arr)){
			echo returnjson(array('code'=>1001),$this->returnstyle,$this->callback);exit();
		}

		$get=$this->redis->get($this->ukey.'MerAdmin'.$reskey);

		if(empty($get)){
			echo returnjson(array('code'=>502),$this->returnstyle,$this->callback);exit();
		}

		$this->redis->expire($this->ukey.'MerAdmin'.$reskey,1800);
        if(empty($reskey)){
            $sel=$this->getAuthId($admin_arr['id']);
            $column = !empty($sel)?$sel['check_auth']:"";
        }else{
            $sel=M('admin_child','total_')->field('column,admin_id')->where(array('id'=>$reskey))->find();
            if(!empty($sel) && $sel['admin_id']!=$admin_arr['id']){
                returnjson(array('code'=>502),$this->returnstyle,$this->callback);
            }
            $column = !empty($sel)?$sel['column']:"";
        }

		if($column){
		    $msg['code']=200;
		    $msg['data']=json_decode($column,true);
		}else{
		    $msg['code']=102;
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	

	/**
	 * 修改密码
	 * @param $pwd $new_pwd $res_pwd
	 * @return mixed
	 */
	public function Modificar_pwd(){
	    //参数为空判断
	    $pwd=I('pwd');
	    $new_pwd=I('new_pwd');
	    if(empty($pwd) || empty($new_pwd)){
	        $msg=array('code'=>1030);
	    }else{
	        	
	        //判断原密码是否正确
	        $admin_arr=$this->getMerchant($this->ukey);
	        
	        if(md5($pwd) != $admin_arr['password']){
	            $msg=array('code'=>500);
	        }else{
	
	            //修改密码
	            $admin_db=M('admin','total_');
	            $re=$admin_db->where(array('id'=>$admin_arr['id']))->save(array('password'=>md5($new_pwd)));
	
	            if($re!==false){
	                //修改成功
	                $m_info = $this->redis->del('member:' . $this->ukey);
	                
	                $msg=array('code'=>200);
	            }else{
	                //修改失败
	                $msg=array('code'=>104);
	            }
	        }
	    }
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	//判断登陆状态
	public function login_status(){
	    $params['key_admin']=I('key_admin');
// 	    $sign=I('sign');
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	        $key_admin=$this->redis->get($this->ukey.'MerAdmin');
	        if(!$key_admin){
	            $msg['code']=502;
	        }else{
	            $this->redis->expire($this->ukey.'MerAdmin',1800);
	            $admin_arr=$this->getMerchant($this->ukey);
// 	            $params['sign_key']=md5(substr($params['key_admin'],-4)."zhihuitu");
// 	            //echo sign($params);
// 	            if($sign!=sign($params)){
// 	                $msg['code']=1002;
// 	            }else{
                    $msg['code']=200;
//                     $arr['pre_table']=$admin_arr['pre_table'];
                    $arr['describe']=$admin_arr['describe'];
                    $arr['sign_key']=$admin_arr['signkey'];
//                     $build_db=M('buildid','total_');
//                     $build_arr=$build_db->field('name,buildid')->where(array('adminid'=>$admin_arr['id']))->select();
//                     $arr['build_list']=$build_arr;

                    $msg['data']=$arr;
// 	            }
	        }
	    }
	    $write_arr['Shop_request']=$params;
	    $write_arr['Shop_reshpone']=$msg;
	    writeOperationLog($write_arr,'Shop');
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
}