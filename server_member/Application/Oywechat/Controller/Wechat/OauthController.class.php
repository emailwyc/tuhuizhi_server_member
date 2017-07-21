<?php
namespace Oywechat\Controller\Wechat;
use Oywechat\Controller\Wechat\WechatcommonController;
use Common\Controller\RedisController;
use Oywechat\Controller\Thirdwechat\EventsController;

class OauthController extends WechatcommonController{
    // TODO - Insert your code here
    
    /**
     * @desc  获取code,是获取access_token的前提
     * @param unknown $jumpurl    跳转到前端的url
     * @param unknown $appid      第三方公众号appid
     */
    private function get_code($jumpurl,$appid,$scope,$state) {
        //如果没有获取到code则组合url，header请求微信接口
        $url=$this->codeurl;
        $hosttype=!is_https()?'https':'https';//判断当前域名是否是https链接
        //该死的base64_encode 等号
//         $jumpurl=base64_encode($jumpurl);
//         $strnum=substr_count($jumpurl,'=');
// //         echo $jumpurl.'<br>';
//         if ($strnum >0){
//             $jumpurl=substr_replace($jumpurl,'',stripos($jumpurl,'='),$strnum);
//         }

        $jumpurl=base_encode($jumpurl);
        $backurl=$hosttype.'://'.$_SERVER['HTTP_HOST'].U('/Oywechat/Wechat/Oauth/getuserinfo/jumpurl/'.$jumpurl.'/scope/'.$scope,'','');
        $url=str_replace('[APPID]',$appid,$url);//公众号appid
        $url=str_replace('[REDIRECT_URI]',$backurl,$url);
        $url=str_replace('[SCOPE]',$scope,$url);
        $url=str_replace('[STATE]',$state,$url);
        $url=str_replace('[component_appid]',$this->appId,$url);//服务方appid
        header('Location:'.$url);
    }
    
    
    
    /**
     * @desc  获取access_token
     * @param unknown $appid
     * @param unknown $code
     * @param unknown $component_access_token
     */
    private function get_access_token($appid,$code){
        $events=new EventsController();
        $component_access_token=$events->component_access_token();
        $rediss = new RedisController();
        $redis=$rediss->connectredis();
        $url=$this->access_token_url;
        $url=str_replace('[APPID]',$appid,$url);//公众号的appid
        $url=str_replace('[CODE]',$code,$url);
        $url=str_replace('[COMPONENT_APPID]',$this->appId,$url);//服务开发方的appid
        $url=str_replace('[COMPONENT_ACCESS_TOKEN]',$component_access_token,$url);
        //$return=file_get_contents($url,true);
        $return=curl_https($url);
        if (is_json($return)){
            $array=json_decode($return,true);
            if (array_key_exists('access_token',$array)){
                $redis->set('wechat:mp:'.$appid.$array['openid'].'access_token',$array['access_token'],$array['expires_in']);//设置access_token和有效期
                $redis->set('wechat:mp:'.$appid.$array['openid'].'refresh_token',$array['refresh_token'],86400);//设置此公众号的此openid的刷新token
                return $array['openid'];//返回openid
            }else{
                return false;
            }
        }else{
            return false;
        }
        
        //$redis->get('wechat:mp:access_token:'.$appid.'isdo',1);//一定要设置此公众号已经请求过access_token，从第二次开始刷新获取access_token，而不是再重新获取
        
    }
    
    
    /**
     * @desc  当公众号access_token失效时，刷新重新获取
     * @param unknown $appid
     */
    private function refresh_token($appid,$code,$component_access_token){
        
    }
    
    
    /**
     * 获取用户信息，只有是snsapi_userinfo方式才可以调用
     * @param unknown $appid
     * @param unknown $openid
     */
    private function getuserinfos($appid,$openid){
        $rediss = new RedisController();
        $redis=$rediss->connectredis();
        $url=$this->get_userinfo_url;
        $url=str_replace('[ACCESS_TOKEN]',$redis->get('wechat:mp:'.$appid.$openid.'access_token'),$url);
        $url=str_replace('[OPENID]',$openid,$url);
        $return=curl_https($url,array(), array(), 30, false,'GET');
        if (is_json($return)){
            $array=json_decode($return,true);
            if (array_key_exists('openid',$array)){
                return $array;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /**
     * @desc  url访问此方法，获取用户信息
     */
    public function  getuserinfo(){
        //同一个公众号在不同的第三方开发平台上unied是否一致
        $jumpurl=I('jumpurl');
        //$appid=I('appid');
        //根据授权类型接收appid
        if ('' != $_GET['key_admin'] && !$_GET['code']){//根据key_admin获取appid，先读redis，如果没有，则读库
            $keyadmin=$_GET['key_admin'];
            $rediss = new RedisController();
            $redis=$rediss->connectredis();
            $url=$jumpurl;
            $url=urldecode($url);
            if (!stripos($url,'key_admin')){
                $params=I();
                unset($params['jumpurl']);
                unset($params['key_admin']);
                unset($params['scope']);
                $params['key_admin']=$keyadmin;
                $params=http_build_query($params);
                $params=urlencode($params);
                $jumpurl= stripos($url,'?')?$jumpurl.'%26'.$params : $jumpurl.'%3f'.$params;//组合jumpurl
            }
            
            
            $appid=$redis->get('wechat:'.$keyadmin.':appid');
            if ('' == $appid){
                $dbadmin=M('admin','total_');
                $find=$dbadmin->where(array('ukey'=>$keyadmin))->find();
                if (null != $find){
                    $appid=$find['wechat_appid'];
                    $redis->set('wechat:'.$keyadmin.':appid',$find['wechat_appid']);//设置appid缓存，防止每次都读库
                    $redis->set('wechat:'.$keyadmin.':admin',json_encode($find));
                }else{//库里面没有这个key_admin对应的商户
                    $this->assign('errorcode',4004)->display('getuserinfo_error');die;
                }
            }
        }elseif ('' != $_GET['appid'] && !$_GET['code']){
            $keyadmin='';
            $appid=$_GET['appid'];
        }elseif ('' == $_GET['appid'] && '' == $_GET['key_admin'] && !$_GET['code']) {
            $this->assign('errorcode',4005)->display('getuserinfo_error');die;//没有获取到appid或key_admin
        }
        
        if (!$_GET['code'] && $_GET['scope']){
            checkdevice();//记录访问的设备类型
            if ('snsapi_base'==$_GET['scope'] || 'snsapi_userinfo'==$_GET['scope']){
                $this->get_code($jumpurl,$appid,$_GET['scope'],'aaa');//其实这里的scope参数要判断一下
            }else{
                $this->assign('errorcode',4001);//scope格式错误
                $this->display('getuserinfo_error');die;
            }
        }else{
            $code=$_GET['code'];
            $appid=$_GET['appid'];
            $scope=$_GET['scope'];
//             $state=$_GET['state'];
            $jumpurl=base_decode($_GET['jumpurl']);
            //这一步实际上返回的是openid，access_token和其它变量保存在redis里面
            $access_token=$this->get_access_token($appid, $code);
            $jumpurl=htmlspecialchars_decode(urldecode($jumpurl));
            
            if ($access_token!=false){
                if ($scope=='snsapi_base'){
                    if (false==strpos($jumpurl,'?')){
                        header('Location:'.$jumpurl.'?openid='.$access_token);//跳转到指定url
                    }else{
                        header('Location:'.$jumpurl.'&openid='.$access_token);//跳转到指定url
                    }
                }else{
                    $return=$this->getuserinfos($appid,$access_token);
                    if ($return!=false){
                        $unionid='';
                        if (array_key_exists('unionid',$return)){
                            $unionid='&unionid='.$return['unionid'];
                        }
                        
                        if (false==strpos($jumpurl,'?')){
                            header('Location:'.$jumpurl.'?openid='.$return['openid'].'&nickname='.$return['nickname'].'&headimgurl='.$return['headimgurl'].'&sex='.$return['sex'].$unionid);//跳转到指定url
                        }else{
                            header('Location:'.$jumpurl.'&openid='.$return['openid'].'&nickname='.$return['nickname'].'&headimgurl='.$return['headimgurl'].'&sex='.$return['sex'].$unionid);//跳转到指定url
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
    
    
    
    public function check_access_token($appid,$openid) {
        
    }
}

?>