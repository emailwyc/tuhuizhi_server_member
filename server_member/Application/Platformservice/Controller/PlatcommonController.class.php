<?php
namespace Platformservice\Controller;

use Think\Controller;
use Common\Controller\RedisController;
class PlatcommonController extends Controller{
    // TODO - Insert your code here
    public $hostarr=array('10.170.216.152','10.44.201.18','101.200.229.5','123.56.109.26');
    protected $redis;
    public $returnstyle;
    public $callback;
    public function _initialize() {
        if (null==session("name") || null == session('pwd') || null == session('verify')){
            $this->redirect(MODULE_NAME.'/Login/index');
        }
        
        //判断请求是以什么方式发起的
        if (I('callback','','htmlspecialchars')){
            $this->callback=I('callback','','htmlspecialchars');
            $this->returnstyle=false;
        }else{
            $this->callback='';
            $this->returnstyle=true;
        }
        
        $re=new RedisController();
        $this->redis=$re->connectredis();
    }
}

?>