<?php
/**
 * Created by Vim
 * User: wangyc
 * Date: 12/20/16
 * Time: 13:00
 */

namespace DevAdmin\Controller;
use Think\Controller;
use Common\Controller\CommonController;
use Common\Controller\RedisController as A;

class DevcommonController extends CommonController
{
	public $key_admin;
	public $adminInfo;
	public $params;

	public function _initialize(){
		parent::__initialize();
		$this->params = I('param.');
		$tokens=I('ukey');
		if($tokens==''){
		    echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
		}
		
		//判断用户是否过期
		/*
		$get = $_SESSION[$tokens];
		if(empty($get)){
		    echo returnjson(array('code'=>502),$this->returnstyle,$this->callback);exit();
		}
		*/
		//得到商户信息	
		$this->adminInfo = $this->getMerchant($tokens);
		$this->key_admin = $tokens;

	}

	protected function emptyCheck($key_arr,$params) {
		$params = !empty($params)?$params:$this->params;
		$new_params = array();
		foreach($key_arr as $v){
			if(empty($params[$v])){
				$msg['code']=1051;
				echo returnjson($msg,$this->returnstyle,$this->callback);exit;
			}else{
				$new_params[$v] = $params[$v];
			}
		}   
		return $new_params;
	}

}
