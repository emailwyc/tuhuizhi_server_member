<?php
namespace ISV\Controller\Alipay;
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
        /*配置－获取app_token*/
        /*配置-setting*/
		$this->setting = array(
			'appid'    => C('ALIPAY_SET_LIST.ZHT_APPID'),
			'ras_path' => C('ALIPAY_SET_LIST.ZHT_PRIKEY'),
			'pub_key'  => C('ALIPAY_SET_LIST.ZHT_PUBKEY'),
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

    protected function getAppAuthToken($appid){
        $db = M('alipay_oauth', 'total_');
        $Info = $db->where(array('`appid`' => $appid))->find();
        if(empty($Info)){
            returnjson(array("code"=>1082,'msg'=>'该商户还没有授权，请先授权后再使用吧！'),$this->returnstyle,$this->callback);
        }
        $curtime = time()+86400;
        if($curtime>$Info['expires']){
            //刷新token
            $request = new \AlipayOpenAuthTokenAppRequest();
            $request->setBizContent(
                json_encode(array(
                    'grant_type'=>"refresh_token",
                    'refresh_token'=>$Info['refresh_token'],
                ))
            );
            $result = $this->aop->execute ($request);
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode;
            if(!empty($resultCode)&&$resultCode->code==10000){
                $resArr = (array)$result->$responseNode;
                //保存app_token
                $expires = time()+$resArr['expires_in'];
                $reexpires = time()+$resArr['re_expires_in'];
                $upArr = array(
                    'userid'=>$resArr['user_id'],
                    'appid'=>$resArr['auth_app_id'],
                    'auth_token'=>$resArr['app_auth_token'],
                    'refresh_token'=>$resArr['app_refresh_token'],
                    'expires'=>$expires,
                    'reexpires'=>$reexpires
                );
                $db->where(array('id'=>$Info['id']))->save($upArr);
                return $resArr['app_auth_token'];
            }else{
                returnjson(array("code"=>1082,'msg'=>"刷新令牌失败！"),$this->returnstyle,$this->callback);
            }
        }else{
            return $Info['auth_token'];
        }
    }



    
    
    
}

?>
