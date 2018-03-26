<?php
namespace StoreAdmin\Controller;

use Common\Controller\JaleelController;

class AuthController extends JaleelController
{
    // TODO - Insert your code here
    public $params;
    public $admin_arr;
    public function _initialize(){
        parent::_initialize();
        $this->params = I('param.');
        $token_key = "StoreLogin:".$this->params['token'];
        $this->emptyCheck($this->params,array('token'));
		//判断商户是否具有权限(暂时没有)
        //判断用户是否登录超时
        $mobile=$this->redis->get($token_key);
        $this->mobile = $mobile;
        if(empty($mobile)){
            returnjson(array('code'=>502),$this->returnstyle,$this->callback);
        }else{
            $this->redis->expire($token_key,7200);
        }

    }
    
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