<?php
namespace Sign\Controller;


/**
 * 用户签到接口部分，签到不用openid或uid做唯一性判断，平台的签到按卡号判断
 * @author ut
 *
 */
class YcoinController extends CommonController
{
    
    
    /**
     * @deprecated获取签到配置
     */
    public function getSignSetting(){
        $params['key_admin']=I('key_admin');
        $msg=$this->commonerrorcode;
        if (in_array('',$params)){
            $msg['code']=100;
        }else{
			$admininfo=$this->getMerchant($params['key_admin']);
			//获取配置
			$db = M('default',$admininfo['pre_table']);
			$Info = $db->where(array('customer_name'=>'signimg'))->find();
			$setting = !empty($Info['function_name'])?json_decode($Info['function_name'],true):(object)array();
			$msg['code']=200;
			$msg['data']=$setting;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * @deprecated    判断当前用户唯一标识&今天是否签到
     */
    public function check_signed(){
        $params['key_admin']=I('key_admin');
        $params['openid']=I('openid');//用户唯一标识符
        $msg=$this->commonerrorcode;
        if (in_array('',$params)){
            $msg['code']=100;
        }else{
			$admininfo=$this->getMerchant($params['key_admin']);
			$db = M('coin_changelog',$admininfo['pre_table']);
			$curdate  = date('Y-m-d',time());
			$curdate  = $curdate." 00:00:00";
			$Info = $db->where(array('openid'=>$params['openid'],'mark'=>'sign','createtime'=>array('egt',$curdate)))->find();
            if (empty($Info)){//没有签到
                $msg['code']=1046;
            }else{//已经签到
                $msg['code']=1045;
                $msg['data']=$issign;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    /**
     * @deprecated    签到动作接口
     */
    public function do_sign(){
        $params=I('param.');
		$msg=$this->commonerrorcode;
		$this->emptyCheck($params,array('key_admin','nickname','openid','headimg'));
		$admininfo=$this->getMerchant($params['key_admin']);//查询商户信息
		//判断用户是否签到
		$db = M('coin_changelog',$admininfo['pre_table']);
		$curdate  = date('Y-m-d',time());
		$curdate  = $curdate." 00:00:00";
		$Info = $db->where(array('openid'=>$params['openid'],'mark'=>'sign','createtime'=>array('egt',$curdate)))->find();
		if($Info){
			$msg['code']=11; $msg['msg']="您已经签到过了！";
			echo returnjson($msg,$this->returnstyle,$this->callback);exit;
		}
		//注册
		$subParams = $params;$subParams['event'] = 'sign';
		$subParams['sign'] = $this->getSign($subParams,$admininfo);
		$url = C('DOMAIN')."/ClientApi/Inside/addYcoinMem";
		$result=curl_https($url, $subParams, array('Accept-Charset: utf-8'), 600, true);
		$result = json_decode($result,true);
		if($result['code']==200){
			//积分扣减
			//$this->emptyCheck($params,array('openid','title','remarks','mark'));
			$subParams = array('key_admin'=>$params['key_admin'],'openid'=>$params['openid'],'title'=>'签到赠送','remarks'=>'签到系统赠送','mark'=>'sign');
			$subParams['sign'] = $this->getSign($subParams,$admininfo);
			$url = C('DOMAIN')."/ClientApi/Inside/ycoinChangeLog";
			$result1=curl_https($url, $subParams, array('Accept-Charset: utf-8'), 600, true);
			$result1 = json_decode($result1,true);
		}else{
			echo returnjson(array('code'=>12,'msg'=>"签到失败！"),$this->returnstyle,$this->callback);
		}
		$msg['code'] = 200;
        echo returnjson($msg,$this->returnstyle,$this->callback);
	}

	protected function emptyCheck($params,$key_arr) {
		foreach($key_arr as $v){
			if(!isset($params[$v])){
				$msg['code']=1051;
				echo returnjson($msg,$this->returnstyle,$this->callback);exit;
			}   
		}   
	}

	protected function getSign($subParams,$admininfo) {
        $subParams['sign_key']=$admininfo['signkey'];
		$sign = sign($subParams);
		return $sign;
	}

    
    
    
    
}
