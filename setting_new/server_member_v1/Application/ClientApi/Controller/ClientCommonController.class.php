<?php
namespace ClientApi\Controller;
use Common\Controller\ErrorcodeController;
class ClientCommonController extends ErrorcodeController
{
	public $setting;
	public $params;

	public function _initialize(){
		parent::_initialize();
		//查询商户配置--start--
		$key_admin=I('key_admin');
		$app_id=I('app_id');
        if (empty($key_admin) && empty($app_id)){
			$msg['code']=1001;
			echo returnjson($msg,$this->returnstyle,$this->callback);exit;
		}
		if(!empty($key_admin)){
			$this->setting = $this->getMerchant($key_admin);
		}
		if(!empty($app_id)){
			$this->setting = $this->getMerchantByAppid($app_id);
		}
		//查询商户配置--end--0
		
		//签名校验--start--
		$this->params = I('param.');
		if (!is_array($this->params)){
	        $code=1051;
		}
		/*
		if (in_array('', $this->params)){
			$msg['code']=100;
			echo returnjson($msg,$this->returnstyle,$this->callback);exit;
		}
		if($this->sign($this->params)==false){
			$msg['code']=1002;
			echo returnjson($msg,$this->returnstyle,$this->callback);exit;
		}
		 */
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

    protected function getMerchantByAppid($key_admin) {
        if (!$key_admin) {
            returnjson(array('code'=>1001), $this->returnstyle, $this->callback);
        }
        $m_info = $this->redis->get('member:appid:' . $key_admin);
        if ($m_info) {
            //writeOperationLog(array('get merchant' => $m_info), 'jaleel_logs');
            return json_decode($m_info, true);
        } else {
            $merchant = M('total_admin');
            $re = $merchant->where(array('wechat_appid' => $key_admin))->find();

            if ($re) {
                //writeOperationLog(array('get merchant' => $re), 'jaleel_logs');
                $this->redis->set('member:appid:' . $key_admin, json_encode($re),array('ex'=>86400));//一天
            }else {
                $data['code']=1001;
                echo returnjson($data,$this->returnstyle,$this->callback);exit();
            }
            return $re;
        }
    }
	protected function checkSign1($params){
		if($params['app_id']){
			$params['sign_key']='ycoin';
		}else{
			$params['sign_key']=$this->setting['signkey'];
		}
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
    protected function sign1($params){
        $params['sign_key']=$this->setting['signkey'];
        unset($params['sign']);
        $sign=sign($params);
		return $sign;
    }

    
    
    
}

?>
