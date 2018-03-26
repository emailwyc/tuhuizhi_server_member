<?php
namespace Platformservice\Controller;

use Think\Controller;
class LoginController extends Controller{
    private $msg=null;
    public $returnstyle;
    public $callback;
    public function _initialize() {
        $this->msg=array('code'=>'','data'=>'','msg'=>'');
        //判断请求是以什么方式发起的
        if (I('callback','','htmlspecialchars')){
            $this->callback=I('callback','','htmlspecialchars');
            $this->returnstyle=false;
        }else{
            $this->callback='';
            $this->returnstyle=true;
        }
        
    }
    // TODO - Insert your code here
    public function index() {
        if (null!=session("name") || null != session('pwd') || null != session('verify')){
            $this->redirect(MODULE_NAME.'/Index/index');
        }
        session(null);
        layout(false);
        
        $this->display();
    }
    public function verify_c(){
        $Verify = new \Think\Verify();
        $Verify->entry();
    }
    
    public function dologin(){
        $parms['name']=I('post.name');
        $parms['password']=I('post.password');
        $parms['verify']=I('verify');
        $msg=$this->msg;
        if (in_array('', $parms)){
            $msg['code']=100;
        }else{
            $ver=new \Think\Verify();
            $isverify=$ver->check($parms['verify']);
            if ('nanhaishizhongguode'==$parms['name'] && 'c0b814fffae869504d9de7f180bd035c'==md5($parms['password']) && true==$isverify){
                session(null);
                session('name',$parms['name']);
                session('pwd',$parms['password']);
                session('verify',$parms['verify']);
                $msg['code']=200;
                echo returnjson($msg, $this->returnstyle, $this->callback);
            }else{
                $msg['code']=104;
                echo returnjson($msg, $this->returnstyle, $this->callback);
            }
        }
    }
    
    
    
    public function logout(){
        session(null);
        $this->redirect(MODULE_NAME.'/Login/index');
    }
}

?>