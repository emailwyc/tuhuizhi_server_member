<?php
namespace Card\Controller;

use Think\Controller;
class CardController extends Controller{
    // TODO - Insert your code here
    
    public function mycard() {
        $http=''!=$_SERVER['REQUEST_SCHEME']?$_SERVER['REQUEST_SCHEME'].'://':'http://';
        $url=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];//当前的url
        $urls=substr($url,0,strpos($url,'/mycard'));//获取到controller
        $wxurl=substr($url,0,strpos($url,'/Card/Card/mycard'));//获取到controller
        
        $burl=I('get.url');
        if (empty($burl)){
            exit('空信息');
        }
        C('url_model',0);
        
        //$state=base64_encode($burl);//把传过来的url加进回跳url里面，防止丢失,微信返回state参数过长%>_<%，存cookie吧
        cookie('backurl',$burl);
        $jumpurl=$http.$urls.'/shocard';
        C('url_model',0);
        dump($_SERVER['REQUEST_SCHEME']);
        echo $jumpurl;die;
        header('Location: '.$http.$wxurl.'/Wechat/Weixin/getWxUserInfo?snsapi=snsapi_base&jumpurl='.$jumpurl);
        //$this->redirect('Wechat/Weixin/getWxUserInfo',array('snsapi'=>'snsapi_userinfo','jumpurl'=>$jumpurl),5,'跳转中');//跳转到微信接口获取openid
    }
    
    public function shocard() {
        $openid=I('get.openid');
        $backurl=str_replace('open_id_m',$openid,cookie('backurl'));
        header('Location:'.htmlspecialchars_decode($backurl));exit();
    }
    
    public function showmycard(){
//         $http=''!=$_SERVER['REQUEST_SCHEME']?$_SERVER['REQUEST_SCHEME'].'://':'http://';
//         $url=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];//当前的url
//         $urls=substr($url,0,strpos($url,'/mycard'));//获取到controller
//         $wxurl=substr($url,0,strpos($url,'/Card/Card/mycard'));//获取到controller
        
//         $burl=I('get.url');
//         if (empty($burl)){
//             exit('空信息');
//         }
//         C('url_model',0);
        
//         //$state=base64_encode($burl);//把传过来的url加进回跳url里面，防止丢失,微信返回state参数过长%>_<%，存cookie吧
//         cookie('backurl',$burl);
//         $jumpurl=$http.$urls.'/shocard';
//         C('url_model',0);
        //$openid=I('openid');
        $url=I('url');
        $url=str_replace('open_id_m',I('openid'),$url);
//         $backurl=$burl.'?op='.$openidstr;
// //         dump($url);
//           dump($_GET);
// //         echo $backurl;die;
// // echo $backurl;
// // substr('');
// die;
        header('Location:'.htmlspecialchars_decode($url));exit();
    }
}

?>