<?php
/**
 * Created by EditPlus.
 * User: zhanghang
 * Date: 6/24/16
 * Time: 10:10 AM
 */

namespace MerAdmin\Controller;

class ParkingCarController extends AuthController
{
	
	public $key_admin;
	public $admin_arr;
	public $status;
	
	public function _initialize(){
		parent::_initialize();
		//查询商户信息
		$this->admin_arr=$this->getMerchant($this->ukey);
			
		$this->key_admin=$this->ukey;
	}
	
	
	/**
	 * 获取停车寻车地址
	 */
	public function parking_find(){
	    $default_data=$this->GetOneAmindefault($this->admin_arr['pre_table'],$this->key_admin,'parkingfindcar');
	    if($default_data){
	        $msg['code']=200;
	        $msg['data']=$default_data;
	    }else{
	        $msg['code']=102;
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	/**
	 * 配置停车寻车地址
	 */
	public function parking_save(){
	    $params['function_name']=I('url');
	    $db=M('default',$this->admin_arr['pre_table']);
	    if(in_array('', $params)){
	        $res=$db->where(array('customer_name'=>array('eq','parkingfindcar')))->delete();
	        if($res !== false){
	            $m_info = $this->redis->del('admin:default:one:parkingfindcar:' . $this->ukey);
	            $msg['code']=200;
	        }else{
	            $msg['code']=104;
	        }
	    }else{
	        $data=$db->where(array('customer_name'=>array('eq','parkingfindcar')))->find();
	        if($data){
	            $res=$db->where(array('customer_name'=>array('eq','parkingfindcar')))->save($params);
	            if($res !== false){
	                $m_info = $this->redis->del('admin:default:one:parkingfindcar:' . $this->ukey);
	                $msg['code']=200;
	            }else{
	                $msg['code']=104;
	            }
	        }else{
	            $params['customer_name']='parkingfindcar';
	            $params['description']="停车寻车";
                $res=$db->add($params);      
                if($res !== false){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
	        }
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
}
