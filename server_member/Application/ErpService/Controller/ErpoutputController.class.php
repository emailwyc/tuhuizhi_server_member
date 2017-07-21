<?php
namespace ErpService\Controller;

use Common\Controller\ErrorcodeController;

class ErpoutputController extends ErrorcodeController
{
    // TODO - Insert your code here
    /**
     * Erp中转
     */
    public function _initialize() {
        parent::_initialize();
        $params=I('param.');
        //验证一些必要参数
        $key_admin=I('key_admin');
        if ('' == $key_admin){
            $msg['code']=1001;
            returnjson($msg,$this->returnstyle,$this->callback);exit;
        }
        /*$signkey=I('sign_key');
        if ($signkey != ''){
            returnjson(array('code'=>104,'data'=>'bad'),$this->returnstyle, $this->callback);exit;
        }*/
        
        $erpdata=$this->getMerchant($key_admin);//获取商户信息
        //判断签名认证

        $msg=$this->commonerrorcode;
        $othersign=$params['sign'];
        unset($params['key_admin']);
        unset($params['sign']);
        if (false == $this->sign($key_admin, $params, $othersign)){
            returnjson(array('code'=>1002), $this->returnstyle, $this->callback);
        }
        $params['key_admin']=$key_admin;
        
        $classname=$this->GetOneAmindefault($erpdata['pre_table'], $key_admin, 'erpclassname');

        $erpserviceclass=$classname['function_name'];
        $action=ACTION_NAME;
        if ('' != $erpserviceclass && 'java' != $erpserviceclass){
            $obj = new $erpserviceclass();//dump($obj);
            $a=$obj->$action();
            exit;
        }elseif('java' == $erpserviceclass){
            //$action=strtolower($action);
            //暂无java对接
        }
    }
    
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