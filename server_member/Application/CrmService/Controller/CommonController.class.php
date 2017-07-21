<?php
namespace CrmService\Controller;

use Common\Controller\ErrorcodeController;
use Common\Controller\RedisController;
class CommonController extends ErrorcodeController{
    // TODO - Insert your code here
    //protected  $redis;
    public function _initialize(){
        parent::__initialize();
        $re=new RedisController();
        $this->redis=$re->connectredis();
    }
    
    
    
    
    
    /**
     * @desc    根据接收参数签名，判断签名是否成功
     * @param unknown $key_admin
     * @param array $params
     * @param unknown $othersign
     */
    protected  function sign($key_admin,array $params,$othersign){
        $db=M('admin','total_');
        $sel=$db->where(array('ukey'=>$key_admin))->select();
        if (null == $sel){
            return false;
        }else {
            $params['key_admin']=$key_admin;
            $params['sign_key']=$sel[0]['signkey'];
            $sign=sign($params);
            //echo $sign;//die;
            if ($sign==$othersign){
                $this->redis->set('crmservice:admin'.$key_admin,json_encode($sel[0]) );
                return true;
            }else{
                return false;
            }
        }
    }
    
    
    
    
    
    
}

?>