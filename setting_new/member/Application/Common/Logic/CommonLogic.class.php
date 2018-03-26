<?php
namespace Common\Logic;
use Common\Controller\SingleRedisController;
use common\MSDaoBase;

class CommonLogic{
    public $redis;
    public $returnstyle;
    public $callback;
    public function __construct(){
        $this->__initialize();
    }

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