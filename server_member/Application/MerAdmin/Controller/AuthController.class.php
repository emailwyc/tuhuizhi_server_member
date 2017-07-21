<?php
namespace MerAdmin\Controller;

use Common\Controller\JaleelController;

class AuthController extends JaleelController
{
    // TODO - Insert your code here
    public $params;
    public $admin_arr;
    public function _initialize(){
        parent::_initialize();
		$admin_arr=$this->getMerchant($this->ukey);
		$this->admin_arr = $admin_arr;
		//判断商户是否具有权限
		$ret=$this->Auth_Admin($admin_arr['id']);
	    if(!$ret){
            echo returnjson(array('code'=>5002,'msg'=>'权限不足'),$this->returnstyle,$this->callback);exit();
        }   
            
        //判断用户是否登录超时
        $get=$this->redis->get($this->ukey.'MerAdmin');
            
        if(empty($get)){
            echo returnjson(array('code'=>502),$this->returnstyle,$this->callback);exit();
        }   

        $this->redis->expire($this->ukey.'MerAdmin',1800);
	


		$this->params = I('param.');
        //parent::_initialize();
    }
    
    
    /*public function login_status(){
        $params['key_admin']=I('key_admin');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $key=$params['key_admin']."zhihuitu";
            $key_admin=$_SESSION[$key];
            if(!$key_admin){
                $msg['code']=502;
            }else{
                $admin_arr=$this->getMerchant($this->ukey);
                $params['sign_key']=$admin_arr['signkey'];
                    if($this->Auth_Admin($admin_arr['id'])){
                        $msg['code']=5002;
                    }else{
                        $msg['code']=200;
                        unset($admin_arr['password']);
                        $msg['data']=$admin_arr;
                    }
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}*/


    /** 
     * @desc 根据接收参数签名，判断签名是否成功
     * @param unknown $key_admin
     * @param array $params
     * @param unknown $othersign
     */
    protected  function sign(array $params){
		$params['sign_key']=$this->setting['signkey'];
		$othersign = @$params['sign'];
		unset($params['sign']);
		$sign=sign($params);
		//print_r($sign);exit;
		if ($sign==$othersign){
			return true;
		}else{
			return false;
		}
	} 

	protected function emptyCheck($params,$key_arr) {
		foreach($key_arr as $v){
			if(!isset($params[$v])){
				$msg['code']=1051;
				echo returnjson($msg,$this->returnstyle,$this->callback);exit;
			}   
		}   
	}


	
}

?>