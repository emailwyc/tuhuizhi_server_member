<?php
namespace ISV\Controller\Alipay;
Vendor('Alipay.AopSdk');
use Think\Controller;
use Common\Controller\ErrorcodeController;
use Common\Controller\RedisController;
/**
 * 支付宝授权接口处理类
 */
class OauthController extends ErrorcodeController
{
	public $aop;
    protected $codeurl='https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id=[APPID]&scope=[SCOPE]&redirect_uri=[REDIRECT_URI]&state=[STATE]';

	public function _initialize(){
		parent::_initialize();
		//配置接口sdk相关
        $this->aop = new \AopClient();
        $this->aop->gatewayUrl = C('ALIPAY_SET_LIST.GATEWAYURL');
        $this->aop->appId = C('ALIPAY_SET_LIST.ZHT_APPID');//动态配置appid
        $this->aop->rsaPrivateKey = C('ALIPAY_SET_LIST.ZHT_PRIKEY');//动态配置私钥串存储路径
        $this->aop->alipayrsaPublicKey = C('ALIPAY_SET_LIST.ZHT_PUBKEY');//动态配置
        $this->aop->apiVersion = C('ALIPAY_SET_LIST.APIVERSION');
        $this->aop->signType = 'RSA2';
        $this->aop->postCharset = C('ALIPAY_SET_LIST.POSTCHARSET');
        $this->aop->format=C('ALIPAY_SET_LIST.FORMAT');
	}
	//ISV授权
	public function index(){
        $app_id = I('app_id');
        $app_auth_code = I('app_auth_code');
        if(empty($app_auth_code)){
            echo "未获取调app_auth_code!";exit;
        }
        $request = new \AlipayOpenAuthTokenAppRequest ();
        $request->setBizContent(
            json_encode(array(
                'grant_type'=>"authorization_code",
                'code'=>$app_auth_code,
            ))
        );
        $result = $this->aop->execute ($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode;
        if(!empty($resultCode)&&$resultCode->code==10000){
            $resArr = (array)$result->$responseNode;
            //保存app_token
            $db = M('alipay_oauth', 'total_');
            $Info = $db->where(array('`appid`' => $resArr['auth_app_id']))->find();
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
            if($Info){
                $db->where(array('id'=>$Info['id']))->save($upArr);
            }else{
                $db->add($upArr);
            }
            returnjson(array("code"=>200,"data"=>$resArr['auth_app_id'],'msg'=>"授权成功！"),$this->returnstyle,$this->callback);exit;
        } else {
            returnjson(array("code"=>104),$this->returnstyle,$this->callback);exit;
        }

    }

    private function get_code($jumpurl,$appid,$scope,$state) {
        //如果没有获取到code则组合url，header请求微信接口
        writeOperationLog(array('marketcard alipay' => $scope), 'marketCard');
        $url=$this->codeurl;
        $hosttype=is_https()?'http':'https';
        $jumpurl=base_encode($jumpurl);
        $backurl=$hosttype.'://'.$_SERVER['HTTP_HOST'].U('/AlipayService/Oauth/getUserInfo/jumpurl/'.$jumpurl.'/scope/'.$scope,'','');
        $url=str_replace('[APPID]',$appid,$url);//公众号appid
        $url=str_replace('[REDIRECT_URI]',urlencode($backurl),$url);
        $url=str_replace('[SCOPE]',$scope,$url);
        $url=str_replace('[STATE]',$state,$url);
        header('Location:'.$url);
    }


    /**
	 * 授权获取用户信息接口
	 * @param array
     * @return mixed
	 */
	public function getUserInfo() {
        $jumpurl=I('jumpurl');
        if ('' != $_GET['key_admin'] && !$_GET['auth_code']){
            $keyadmin=$_GET['key_admin'];
            $rediss = new RedisController();
            $redis=$rediss->connectredis();
            $url=$jumpurl;
            $url=urldecode($url);
            if (!stripos($url,'key_admin')){
                $params=I("param.");
                unset($params['jumpurl']);
                unset($params['key_admin']);
                unset($params['scope']);
                $params['key_admin']=$keyadmin;
                $params=http_build_query($params);
                $jumpurl= stripos($url,'?')?$jumpurl.'&'.$params : $jumpurl.'?'.$params;
                $jumpurl = urlencode($jumpurl);
            }
            $appid=$redis->get('alipay:'.$keyadmin.':appid');
            if ('' == $appid){
                $dbadmin=M('admin','total_');
                $find=$dbadmin->where(array('ukey'=>$keyadmin))->find();
                if (null != $find){
                    $appid=$find['alipay_appid'];
                    $redis->set('alipay:'.$keyadmin.':appid',$find['alipay_appid']);
                    $redis->set('alipay:'.$keyadmin.':admin',json_encode($find));
                }else{
                    $this->assign('errorcode',4004)->display('getuserinfo_error');die;
                }
            }
        }elseif ('' != $_GET['app_id'] && !$_GET['auth_code']){
            $keyadmin='';
            $appid=$_GET['app_id'];
        }elseif ('' == $_GET['app_id'] && '' == $_GET['key_admin'] && !$_GET['auth_code']) {
            $this->assign('errorcode',4005)->display('getuserinfo_error');die;//没有获取到appid或key_admin
        }

        if (!$_GET['auth_code'] && $_GET['scope']){
            $state = !empty($_GET['state'])?$_GET['state']:"aaa";
            $this->get_code($jumpurl,$appid,$_GET['scope'],$state);
        }else{
            $code=$_GET['auth_code'];
            $appid=$_GET['app_id'];
            $scope=$_GET['scope'];
            $state=$_GET['state'];
            $jumpurl=base_decode($_GET['jumpurl']);
            //根据appid获取app_auth_token
            $app_auth_token = $this->getAppAuthToken($appid);
            $access_token=$this->get_access_token($appid, $code,$app_auth_token);
            if($state=="system"){
                returnjson(array("code"=>200,"data"=>$access_token));exit;
            }
            $jumpurl=htmlspecialchars_decode(urldecode($jumpurl));

            if ($access_token!=false){
                if ($scope=='auth_base'){
                    //获取到userid，进行前端页面跳转（静默授权）
                }else{
                    $return=$this->getuserinfos($appid,$access_token['access_token'],$app_auth_token);
                    if ($return!=false){
                        //进行页面跳转；
                        $nickName = !empty($return['user_name'])?$return['user_name']:$return['nick_name'];
                        if (false==strpos($jumpurl,'?')){
                            header('Location:'.$jumpurl.'?userid='.$return['user_id'].'&mobile='.$return['mobile'].'&nickname='.$nickName.'&headimgurl='.$return['avatar'].'&sex='.$return['gender']);
                        }else{
                            header('Location:'.$jumpurl.'&userid='.$return['user_id'].'&mobile='.$return['mobile'].'&nickname='.$nickName.'&headimgurl='.$return['avatar'].'&sex='.$return['gender']);
                        }
                    }else{
                        $this->assign('errorcode',4002);
                        $this->display('getuserinfo_error');
                    }
                }
            }else{
                $this->assign('errorcode',4003);
                $this->display('getuserinfo_error');
            }
        }
	}

    /**
     * 获取用户信息，只有是auth_user方式才可以调用
     * @param unknown $appid,token;
     */
    private function getuserinfos($appid,$accessToken,$app_auth_token=null){
        $request = new \AlipayUserInfoShareRequest ();
        $result = $this->aop->execute ( $request , $accessToken,$app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode;
        if(!empty($resultCode)&&!empty($resultCode->user_id)){
            $resArr = (array)$result->$responseNode;
            return $resArr;
        } else {
            return false;
        }
    }

    private function get_access_token($appid,$code,$app_auth_token=null){
        $rediss = new RedisController();
        $redis=$rediss->connectredis();
        //先判断redis中是否有token信息，如果有则直接返回；
        /*
        $tmptoken = $redis->get('alipay:'.$appid.':access_token');
        if(!empty($tmptoken)){
            return json_decode($tmptoken,true);
        }
        */
	    //根据appid获取admin信息
        $admininfo=$redis->get('alipay:'.$appid.':appid_alipay');
        if (empty($admininfo)){
            $dbadmin=M('admin','total_');
            $find=$dbadmin->where(array('alipay_appid'=>$appid))->find();
            if (null != $find){
                $admininfo=$find;
                $redis->set('alipay:'.$appid.':appid_alipay',json_encode($find));
            }else{
                $this->assign('errorcode',4004)->display('getuserinfo_error');die;
            }
        }else{
            $admininfo = json_decode($admininfo,true);
        }
        //调用获取token接口
        $request = new \AlipaySystemOauthTokenRequest();
        $request->setGrantType("authorization_code");
        $request->setCode($code);
        $result = $this->aop->execute ($request,null,$app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode;
        if(!empty($resultCode)&&!empty($resultCode->user_id)){
            //缓存结果并返回；
            $resArr = (array)$result->$responseNode;
            $rediss = new RedisController();
            $redis=$rediss->connectredis();
            $redis->set('alipay:'.$resultCode->user_id.':access_token:userid',json_encode($resArr),$resArr['expires_in']);
            return $resArr;
        } else {
            $this->assign('errorcode',4004)->display('getuserinfo_error');die;
        }


    }

    private function getAppAuthToken($appid){
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
                    'grant_type'=>"refresh_token ",
                    'refresh_token'=>$Info['refresh_token'],
                ))
            );
            $result = $this->aop->execute ($request,null,$Info['auth_token']);
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
