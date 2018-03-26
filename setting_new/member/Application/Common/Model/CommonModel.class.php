<?php
namespace Common\Model;
use Think\Model;
use Common\Controller\SingleRedisController;

class CommonModel extends Model{
    public $redis;
    public $returnstyle;
    public $callback;
    public function __initialize(){
        if (I('callback','','htmlspecialchars')){
            $this->callback=I('callback','','htmlspecialchars');
            $this->returnstyle=false;
        }else{
            $this->callback='';
            $this->returnstyle=true;
        }
        $this->redis =SingleRedisController::getInstance();
    }
}
?>