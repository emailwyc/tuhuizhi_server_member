<?php
namespace HandsFreeShopping\Controller;
use Common\Controller\ErrorcodeController;
class ClientCommonController extends ErrorcodeController
{
	public $setting;
	public $params;

	public function _initialize(){
		parent::_initialize();
		//查询商户配置--start--
		$key_admin=I('key_admin');
		if(!$key_admin){
            $content = file_get_contents("php://input");
            $par_arr = json_decode($content, true);
            $this->confirmPayAttach = json_decode(urldecode($par_arr['attach']), true);
            if(is_array($this->confirmPayAttach)){
                $key_admin = $this->confirmPayAttach['key_admin'];
            }else{
                $msg['code']=1001;
                echo returnjson($msg,$this->returnstyle,$this->callback);exit;
			}
		}
        if (empty($key_admin) && empty($app_id)){
			$msg['code']=1001;
			echo returnjson($msg,$this->returnstyle,$this->callback);exit;
		}
		if(!empty($key_admin)){
			$this->setting = $this->getMerchant($key_admin);
		}
		//查询商户配置--end--0
		
		//签名校验--start--
		$this->params = I('param.');
		if (!is_array($this->params)){
	        $code=1051;
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
