<?php
namespace Car\Controller;
use Common\Controller\ErrorcodeController;
/**
 * 打车部分需要用到的公共参数和其它配置等
 */
class CarcommonController extends ErrorcodeController{

    public $useradmin;
    public $apiurl;
    public $AppKey;
    public $AppSecret;
    public $key;
    public $TEST_CALL_PHONE;
    public $access_token;//授权认证，缓存半小时
    public $user_card;//用户会员卡号
    public $user_openid;//用户openid
    public $user_mobile;//用户手机号
    public $score;//用户积分数
    public $ratio_score=null;//设置的积分比率
    public $ratio_price=null;//设置的积分比率
	public $minscore;//设置的最小积分数
	public $admininfo;
    
    
    public function __initialize(){
		parent::_initialize();
		$this->apiurl=C('XIAOJU_API_URL');
	}

    //检测是否是会员，每一步都要执行，只有会员才可以打车
	public function checkvip($openid,$pre_table){
		$db=M('mem',$pre_table);
		//查询会员卡号
		$memInfo =$db->where(array('openid'=>$openid))->find();
		if(empty($memInfo)){ returnjson(array('code'=>2000),$this->returnstyle,$this->callback);exit(); }
		//查询会员信息
		$url3=C('DOMAIN').'/CrmService/OutputApi/Index/getuserinfobycard';//查询会员信息接口
		$sigs=sign(array('key_admin'=>$this->admininfo['ukey'],'card'=>$memInfo['cardno'],'sign_key'=>$this->admininfo['signkey']));
		$url3_arr=http($url3,array('key_admin'=>$this->admininfo['ukey'],'card'=>$memInfo['cardno'],'sign'=>$sigs));
		$arr=json_decode($url3_arr,true);
		if($arr['code'] != 200){
			return false;
		}else{
			$this->user_card=$arr['data']['cardno'];
			$this->user_openid=$memInfo['openid'];
			$this->user_mobile=$arr['data']['mobile'];
			$this->score=floor($arr['data']['score']);
			return true;
		}
	}

    /**  
     * 调用会员平台按手机号查询会员信息接口
     * @param $tel 会员手机号
     * @param $ukey 商户密钥
     * @param $sign_key 加密密钥
     * @return bool
     * @throws \Exception
     */
    protected function getMemberByTel($tel, $ukey, $sign_key, $openid) {
        if (!$tel) return false;
             
        $data['mobile'] = $tel;
        $data['key_admin'] = $ukey;
        $data['sign_key'] = $sign_key;
        $data['openid'] = $openid;
        $data['sign'] = sign($data);
        writeOperationLog(array('make sign' => 'mobile:' . $data['mobile'] . ' ,key_admin:' . $data['key_admin'] . ' ,sign_key:' . $data['sign_key'] . ' ,sign' . $data['sign']), 'jaleel_logs');
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/getuserinfobymobile';
        $curl_re = http($url, $data, 'post');
        writeOperationLog(array('get member by tel' => $curl_re), 'jaleel_logs');
        return json_decode($curl_re, true);
    }


}

?>
