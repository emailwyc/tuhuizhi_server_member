<?php
namespace ResourcesApi\Controller;
use Common\Controller\ErrorcodeController;
class ResCommonController extends ErrorcodeController
{
	public $setting;
	public $params;

	public function _initialize(){
		parent::_initialize();
		//查询商户配置--start--
		$key_admin=I('key_admin');
        if ('' == $key_admin){
			$msg['code']=1001;
			echo returnjson($msg,$this->returnstyle,$this->callback);exit;
        }
		$this->setting = $this->getMerchant($key_admin);
		//查询商户配置--end--
		
		//签名校验--start--
		$this->params = I('param.');
		if (!is_array($this->params)){
	        $code=1051;
		}
		if (in_array('', $this->params)){
			$msg['code']=100;
			echo returnjson($msg,$this->returnstyle,$this->callback);exit;
		}
		if($this->sign($this->params)==false){
			$msg['code']=1002;
			echo returnjson($msg,$this->returnstyle,$this->callback);exit;
		}
		//签名校验--end--
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
