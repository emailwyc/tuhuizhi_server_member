<?php
namespace AlipayService\Controller;
Vendor('Alipay.AopSdk');
use Common\Controller\ErrorcodeController;
class AlipayCommonController extends ErrorcodeController
{
	public $setting;
	public $params;
    public $adminInfo;
    protected $codeurl='https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id=[APPID]&scope=[SCOPE]&redirect_uri=[REDIRECT_URI]&state=[STATE]';

	public function _initialize(){
		parent::_initialize();
		//查询商户配置--start--
		$key_admin=I('key_admin');
        if ('' == $key_admin){
			$msg['code']=1001;
			echo returnjson($msg,$this->returnstyle,$this->callback);exit;
        }
		$crmdata=$this->getMerchant($key_admin);
		$this->setting = array(
			'appid'    => $crmdata['alipay_appid'],
			'ras_path' => $crmdata['alipay_raskey_path'],
			'pub_key'  => $crmdata['alipay_pubkey'],
			'signkey'  => $crmdata['signkey']
		);
        $this->adminInfo = $crmdata;
		//查询商户配置--end--

		$this->params = I('param.');
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
			if(empty($params[$v])){
				$msg['code']=1051;
				echo returnjson($msg,$this->returnstyle,$this->callback);exit;
			}   
		}   
	}


    
    
    
}

?>
