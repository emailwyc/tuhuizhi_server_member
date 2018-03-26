<?php
namespace Qiandao\Controller;
use Think\Controller;

class CommonController extends Controller{
    public $returnstyle;
    public $callback;
    public function first(){
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
    
    
}

?>