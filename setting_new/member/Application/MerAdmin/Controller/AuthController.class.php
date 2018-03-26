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
        $this->changjk_baseurl = C('CJKDOMAIN');
		$admin_arr=$this->getMerchant($this->ukey);
		$this->admin_arr = $admin_arr;
		//判断商户是否具有权限
		
		$ret=$this->Auth_Admin($admin_arr['id']);
	    if(!$ret){
            echo returnjson(array('code'=>5002,'msg'=>'权限不足'),$this->returnstyle,$this->callback);exit();
        }
        session_start();
        if(empty($admin_arr['changjk_id']) || $_SESSION['MerAdmin_Login'] == 1) {
            $time = time();
            if (!$_SESSION['MerAdmin_Login'] || ($time - $_SESSION['MerAdmin_Login_time']) > 1800) {
                echo returnjson(array('code' => 502), $this->returnstyle, $this->callback);
                exit();
            }
            //判断用户是否登录超时
            $get = $this->redis->get($this->ukey . 'MerAdmin');
            if (empty($get)) {
                echo returnjson(array('code' => 502), $this->returnstyle, $this->callback);
                exit();
            }
            $this->redis->expire($this->ukey . 'MerAdmin', 1800);
            $_SESSION['MerAdmin_Login'] = 1;
            $_SESSION['MerAdmin_Login_time'] = $time;
        }else{
            $token = $this->redis->get('changjingke:login:'.$admin_arr['ukey']);
            if(empty($token)){
                returnjson(array('code'=>502),$this->returnstyle,$this->callback);
            }
            $url = $this->changjk_baseurl."/rts-mgr-web/user/info";
            $cjkInfo = http($url,array('v'=>"1.0.0",'token'=>$token));
            $cjkInfo = json_decode($cjkInfo,true);
            if(empty($cjkInfo['data']['id'])){
                returnjson(array("code"=>502),$this->returnstyle,$this->callback);exit();
            }

        }
        
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