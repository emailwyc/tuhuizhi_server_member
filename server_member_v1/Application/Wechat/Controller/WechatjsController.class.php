<?php
namespace Wechat\Controller;
use Think\Controller;
use Org\Util\String;
/**
 * 老的接口，获取微信配置以后必须改
 * @author ut
 *
 */

class WechatjsController extends Controller{
    public $returnstyle;
    public $callback;
    public function __construct(){
        header ( "Content-Type:text/html;charset=utf-8" );
        //判断请求是以什么方式发起的
        if (I('get.callback','','htmlspecialchars')){
            $this->callback=I('get.callback','','htmlspecialchars');
            $this->returnstyle=false;
        }else{
            $this->callback='';
            $this->returnstyle=true;
        }
    }
    // TODO - Insert your code here
    
    //js部分
    public function getjsapiticket($access_token) {
        $url="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
        return curl_https($url);
    }
    
    public function setzhengshu($jsapi_ticket,$noncestr,$timestamp,$url) {
        $str='jsapi_ticket='.$jsapi_ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;
        return  sha1($str);
    }
    
    public function getsignature() {
        $url=I('get.url');
        if (empty($url)){
            echo returnjson(array('msg'=>'未获取到url','status'=>false),$this->returnstyle,$this->callback);exit();
        }
        $url =urldecode($url);
        
        if (!isset($_GET['build']) || empty($_GET['build'])||$_GET['build']=='undefined' || $_GET['build']=='DEFAULT'){
            $build='DEFAULT';
        }else {
            $build='O'.I('get.build');
        }
        $db=M('ticket','total_');
        $find=$db->where(array('id'=>1))->find();
        $cha=timediff($find['starttime'], time());
        if ($cha['hour']>=2){//如果小时数大于等于2重新获取titcket
            //获取access_token
            $accessurl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".C($build.'.APPID')."&secret=".C($build.'.SECRET');
            $access_token=curl_https($accessurl);
            if (is_json($access_token)){
                $access=json_decode($access_token,true);
                $jsapi_ticket=$this->getjsapiticket($access['access_token']);
                
            
                if (is_json($jsapi_ticket)){
                    $thisuserarr=json_decode($jsapi_ticket,true);
                    if ($thisuserarr['errcode']==0){
                        $data['id']=1;
                        $data['jsapi_ticket']=$thisuserarr['ticket'];
                        $data['starttime']=time();
                        $add=$db->save($data);
                        $ticket=$thisuserarr['ticket'];
                        $string=new String();
                        $timestamp=time();
                        $str=$string->randString(20,3);
                        $sh1=$this->setzhengshu($thisuserarr['ticket'],$str,$timestamp,$url);
                        $return=array('appId'=>C($build.'.APPID'),'timestamp'=>$timestamp,'nonceStr'=>$str,'signature'=>$sh1,'status'=>true,'url'=>$url);
                    }else {
                        $return=array('msg'=>'失败','status'=>false);
                    }
                }else {
                    $return=array('msg'=>'请求失败','status'=>false);
                }
            }else {
                $return=array('msg'=>'请求失败','status'=>false);
            }
        }else {
            $ticket=$find['jsapi_ticket'];
            $string=new String();
            $timestamp=time();
            $str=$string->randString(20,3);
            $sh1=$this->setzhengshu($ticket,$str,$timestamp,$url);
            $return=array('appId'=>C($build.'.APPID'),'timestamp'=>$timestamp,'nonceStr'=>$str,'signature'=>$sh1,'status'=>true,'url'=>$url);
        }
        
        echo returnjson($return,$this->returnstyle,$this->callback);
        


        
    }
}

?>