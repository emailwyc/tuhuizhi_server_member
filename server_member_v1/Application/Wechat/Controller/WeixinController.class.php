<?php
namespace Wechat\Controller;
use Think\Controller;
use Wechat\Controller\WeixinmodelController;
/**
 * 请求此接口时，增加一个参数snsapi，值为snsapi_base或snsapi_userinfo
 * @author ut
 * snsapi   请求方式
 *
 */
class WeixinController extends Controller {
    public $userInfo;
    public $wxId;
    public $snsapi;
    public $jumpurl;
    public $build;
    public $useradmin;
    public function __construct(){
        if (!IS_GET){
            exit('非正常操作') ;
        }
        if ($_GET['code'] && $_GET['mydata']){//如果是从微信跳转过来的,因为header跳转过来有code，则执行此段代码
            $str=base64_decode($_GET['mydata']);
            parse_str($str);
            $this->jumpurl=$jumpurl;
            $this->snsapi=$snsapi;
            //$this->build=cookie('build');
        }else {//如果是从接口处跳转过来的，则执行此段代码
            if (!isset($_GET['snsapi']) || !isset($_GET['jumpurl']) || !isset($_GET['key'])){
                exit('缺少参数');
            }else {
                if (empty($_GET['snsapi']) || empty($_GET['jumpurl']) || empty($_GET['key'])){
                    exit('缺少参数值');
                }else {
                    $this->snsapi= I('get.snsapi');
                    $this->jumpurl                = I('get.jumpurl');
                    //根据key查询商户id
                    $dbadmin=M('admin','total_');//后台商户表
                    $dbadminweixin=M('admin_weixin','total_');//商户微信配置表
                    $adminsel= $dbadmin->field('total_admin.id as uid,appid,secret,token,ukey')->join('total_admin_weixin ON  total_admin.id = total_admin_weixin.adminuid')->where(array('ukey'=>I('get.key')))->find();
                    session('useradmin',$adminsel);
                }
            }
        }
        //判断jumpurl是否存在http://，没有则默认添加http://
        $this->jumpurl=strstr($this->jumpurl, 'http://')?$this->jumpurl:'http://'.$this->jumpurl;
        
        if ('snsapi_base' !=$this->snsapi && 'snsapi_userinfo' != $this->snsapi){
            exit('参数值错误') ;//json_encode(array('msg'=>'访问参数错误','status'=>false));
        }
        //只要用户一访问此模块，就登录授权，获取用户信息
        $this->userInfo = $this->getWxUserInfo();
     }
     
    /**
    * 确保当前用户是在微信中打开，并且获取用户信息
    *
    * @param string $url 获取到微信授权临时票据（code）回调页面的URL
    */
    private function getWxUserInfo($url = '') {
        $useradmin=session('useradmin');
        //微信标记（自己创建的）
        $wxSign = cookie('wxSign');
        //获取授权临时票据（code）
        $code = $_GET['code'];
        $wexinmodel=new WeixinmodelController($useradmin);
        if (empty($code)) {
            if (empty($url)) {
                C('URL_MODEL',2);
                $data='snsapi='.$this->snsapi.'&jumpurl='.$this->jumpurl;
                $data=base64_encode($data);
                $url='http://'.$_SERVER['HTTP_HOST'].U('/Wechat/Weixin/getWxUserInfo',array('mydata'=>$data),'');
                //到WeixinmodelController.php里获取到微信授权请求URL,然后redirect请求url
                $bool='snsapi_base'==$this->snsapi?true:false;
                //
                $data='';
                $jumpurl=$wexinmodel->getOAuthUrl($url,$bool);
                //echo urldecode($jumpurl);die;
                header("Location:$jumpurl");
            }
        }else {
            /***************这里开始第二步：通过code获取access_token****************/
            $result = $wexinmodel->getOauthAccessToken($code);
            //如果传递的参数为snsapi_base，则返回openid
            if ('snsapi_base'==$this->snsapi){
                //如果发生错误
                if (!empty($result['errcode'])) {
                    $strs='?errcode='.$result['errcode'].'&status=false';
                    //根据请求方式返回信息
                    header('Location: '.$this->jumpurl.$strs);exit();
                }elseif (!empty($result['openid'])) {
                    $user=M('user','total_');
                    if ($user->where(array('openid'=>$result['openid']))->count() == 0){//添加新openid
                        $data['openid']=$result['openid'];
                        $add=$user->add($data);
                    }
                    cookie('build','');
                    header("Location: ".$this->jumpurl."?openid=".$result['openid'].'&ukey='.$useradmin['ukey'] );//把后台商户key传给前段
                    session('usernadmin','');//清除session
                    exit();
                    //到这一步就说明已经取到了access_token
                    //根据请求方式返回信息
                }
            }else {
                /*******************************下面所有的操作（一直到底）为snsapi_userinfo方式************************/
                //到这一步就说明已经取到了access_token
                $this->wxId = $result['openid'];
                $accessToken = $result['access_token'];
                $openId = $result['openid'];
                
                /*******************这里开始第三步：通过access_token调用接口，取出用户信息***********************/
                $this->userInfo = $wexinmodel->getuserinfos($openId, $accessToken);
                $userInfos=json_decode($this->userInfo,true);
                if (!empty($userInfos['errcode'])){//如果有错误码
                    $stra='?msg='.$userInfos['errcode'].'&status=false';
                }else if (!empty($userInfos['openid'])){//如果获取到openid
                    $user=M('user','total_');
                    if ($user->where(array('openid'=>$result['openid']))->count() == 0){//添加新openid
                        $data['openid']=$userInfos['openid'];
                        $data['uname']=$userInfos['nickname'];
                        $data['headimgurl']=$userInfos['headimgurl'];
                        $add=$user->add($data);
                    }
                    //判断当前openid是否关注当前公众号
                    $accessurl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$useradmin['appid']."&secret=".$useradmin['secret'];
                    $access_token=curl_https($accessurl);
                    if (is_json($access_token)){
                        $access=json_decode($access_token,true);
                        $thisuser=curl_https("https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access['access_token']}&openid={$userInfos['openid']}&lang=zh_CN");
                        if (is_json($thisuser)){
                            $thisuserarr=json_decode($thisuser,true);
                        }else {
                            $thisuserarr['subscribe']='0';//请求失败当做未关注处理
                        }
                    }else {
                        $thisuserarr['subscribe']='0';//请求失败当做未关注处理
                    }
                    $stra='?openid='.$userInfos['openid'].'&nickname='.$userInfos['nickname'].'&sex='.$userInfos['sex'].'&headimgurl='.$userInfos['headimgurl'].'&subscribe='.$thisuserarr['subscribe'].'&ukey='.$useradmin['ukey'];
                }
                header('Location:'.$this->jumpurl.$stra);
                session('useradmin','');//清除session
                exit();
            }
        }
    }
    
    
    
    
}
?>