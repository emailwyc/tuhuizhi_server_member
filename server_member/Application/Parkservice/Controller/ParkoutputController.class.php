<?php
namespace Parkservice\Controller;

use Common\Controller\ErrorcodeController;

class ParkoutputController extends ErrorcodeController
{
    // TODO - Insert your code here
    /**
     * 查询接口，返回的应该是一个车辆列表，也必须是
     */
    public function searchcar() {
        $carno=trim(I('carno'));
        $key_admin=I('key_admin');
        $othersign=I('sign');
        $page=I('page')?I('page'):1;
        $lines=I('lines')?I('lines'):10;
        $msg=$this->commonerrorcode;
        if (null==$carno || null==$key_admin || null==$othersign || null==$page || null==$lines){
            $msg['code']=100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit;
        }elseif (1 > strlen($carno)){
            $msg['code']=1100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }else{
            $admininto=$this->getMerchant($key_admin);
            //验证签名
            $params['key_admin']=$key_admin;
            $params['carno']=$carno;
            $params['sign_key']=$admininto['signkey'];
            $sign=sign($params);//echo $sign;
            $params['page']=$page;
            $params['lines']=$lines;
            if ($sign==$othersign){
                $admindefault=$this->GetdAmindefault($admininto['pre_table'], $key_admin);
                //$admin=array_column($admindefault, customer_name);
                $classname=null;
                foreach ($admindefault as $key => $val){
                    if ('parkclassname'==$val['customer_name']){
                        $classname=$val['function_name'];
                        break;
                    }
                }
                //如果有自己的停车类名
                if (null != $classname){
                    $obj = new $classname();
                    $result=$obj->searchcar($carno,$admininto['signkey'],$key_admin,$page,$lines);
                    if (is_array($result)){//如果是json，必然是正确的数据，类文件里面必须处理正确
                        //添加所需积分数，$array['data']['IntValue'] =
                        if(!isset($result['data']['IntValue'])){
                            $scoreInfo = $this->GetOneAmindefault($admininto['pre_table'],$key_admin,"score");
                            $result['data']['IntValue'] = @$scoreInfo['function_name'];
                        }
                        $msg['code']=200;
                        $msg['data']=$result;
                        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
                    }elseif (is_string($result)){
                        $msg['code']=$result;
                        echo returnjson($msg,$this->returnstyle,$this->callback);
                    }else {
                        $msg['code']=104;
                        echo returnjson($msg,$this->returnstyle,$this->callback);
                    }
                }else{//没有就用公用的类，暂时没有公用的类
                
                }
            }else{
                $msg['code']=1002;//签名错误
                $msg['data']='error';
                echo returnjson($msg,$this->returnstyle,$this->callback); 
            }
        }
    }
    
    
    
    
    public function choosemycar() {
        $carno=trim(I('carno'));
        $key_admin=I('key_admin');
        $othersign=I('sign');
        $orderNo = I('orderNo');
        $msg=$this->commonerrorcode;
        if (null==$carno || null==$key_admin || null==$othersign){
            $msg['code']=100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit;
        }elseif (4 > strlen($carno)){
            $msg['code']=1100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }else{
            $admininto=$this->getMerchant($key_admin);
            //验证签名
            $params['key_admin']=$key_admin;
            $params['carno']=$carno;
            $params['sign_key']=$admininto['signkey'];
            $sign=sign($params);
            if ($sign==$othersign){
                $admindefault=$this->GetdAmindefault($admininto['pre_table'], $key_admin);
                //$admin=array_column($admindefault, customer_name);
                $classname=null;
                foreach ($admindefault as $key => $val){
                    if ('parkclassname'==$val['customer_name']){
                        $classname=$val['function_name'];
                        break;
                    }
                }
                //如果有自己的停车类名
                if (null != $classname){
                    $obj = new $classname();
                    $result=$obj->choosemycar($carno,$admininto['signkey'],$key_admin, $orderNo);
                    if (is_array($result)){//如果是json，必然是正确的数据，类文件里面必须处理正确
                        echo returnjson($result,$this->returnstyle,$this->callback);exit();
                    }else {
                        $msg['code']=104;
                        echo returnjson($msg,$this->returnstyle,$this->callback);
                    }
                }else{//没有就用公用的类，暂时没有公用的类
        
                }
            }else{
                $msg['code']=1002;//签名错误
                $msg['data']='error';
                echo returnjson($msg,$this->returnstyle,$this->callback);
            }
        }
    }
    
    
    
    /**
     * 获取车场剩余车位
     */
    public function get_left_park(){
        $key_admin=I('key_admin');
        $othersign=I('sign');
        $msg=$this->commonerrorcode;
        if (null==$key_admin || null==$othersign){
            $msg['code']=100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit;
        }else{
            $admininto=$this->getMerchant($key_admin);
            //验证签名
            $params['key_admin']=$key_admin;
            $params['sign_key']=$admininto['signkey'];
            $sign=sign($params);
            if ($sign==$othersign){
                $admindefault=$this->GetdAmindefault($admininto['pre_table'], $key_admin);
                //$admin=array_column($admindefault, customer_name);
                $classname=null;
                foreach ($admindefault as $key => $val){
                    if ('parkclassname'==$val['customer_name']){
                        $classname=$val['function_name'];
                        break;
                    }
                }
                //如果有自己的停车类名
                if (null != $classname){
                    $obj = new $classname();
                    $result=$obj->getleftpark($admininto['signkey'],$key_admin);
                    if (is_array($result)){//如果是json，必然是正确的数据，类文件里面必须处理正确
                        $msg['code']=200;
                        $msg['data']=$result;
                        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
                    }elseif (is_string($result)){
                        $msg['code']=$result;
                        echo returnjson($msg,$this->returnstyle,$this->callback);
                    }else {
                        $msg['code']=104;
                        echo returnjson($msg,$this->returnstyle,$this->callback);
                    }
                }else{//没有就用公用的类，暂时没有公用的类
        
                }
            }else{
                $msg['code']=1002;//签名错误
                $msg['data']='error';
                echo returnjson($msg,$this->returnstyle,$this->callback);
            }
        }
    }
    
    
    
    
    /**
     * 支付状态更改
     */
    public function pay(){
        $carno=trim(I('carno'));
        $paytype=I('paytype');
        $key_admin=I('key_admin');
        $othersign=I('sign');
        $msg=$this->commonerrorcode;
        $orderNo = I('orderNo');
        $amount = I('amount');
        $discount = I('discount');
        if (null==$carno || null==$key_admin || null==$othersign || null == $paytype){
            $msg['code']=100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit;
        }elseif (4 > strlen($carno)){
            $msg['code']=1100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }else{
            $admininto=$this->getMerchant($key_admin);
            //验证签名
            $params['key_admin']=$key_admin;
            $params['carno']=$carno;
            $params['paytype']=$paytype;
            $params['sign_key']=$admininto['signkey'];
            $sign=sign($params);//echo $sign;
            if ($sign==$othersign){
                $admindefault=$this->GetdAmindefault($admininto['pre_table'], $key_admin);
                //$admin=array_column($admindefault, customer_name);
                $classname=null;
                foreach ($admindefault as $key => $val){
                    if ('parkclassname'==$val['customer_name']){
                        $classname=$val['function_name'];
                        break;
                    }
                }
                //如果有自己的停车类名
                if (null != $classname){
                    $obj = new $classname();
                    $result=$obj->paystatus($carno,$admininto['signkey'],$paytype, $key_admin, $orderNo, $amount, $discount);
                    file_put_contents('log.txt', $amount);
                    if (is_array($result)){//如果是json，必然是正确的数据，类文件里面必须处理正确
                        $msg['code']=200;
                        $msg['data']=$result;
                        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
                    }elseif (is_string($result)){
                        $msg['code']=$result;
                        echo returnjson($msg,$this->returnstyle,$this->callback);
                    }else {
                        $msg['code']=104;
                        echo returnjson($msg,$this->returnstyle,$this->callback);
                    }
                }else{//没有就用公用的类，暂时没有公用的类
        
                }
            }else{
                $msg['code']=1002;//签名错误
                $msg['data']='error';
                echo returnjson($msg,$this->returnstyle,$this->callback);
            }
        }
    }
    
    
    
    
    /**
     * 车场车位详细状态
     */
    public function getparkstatus(){
        $key_admin=I('key_admin');
        $build=I('build');
        $floor=I('floor');
        $othersign=I('sign');
        $msg=$this->commonerrorcode;
        if (null==$build || null==$key_admin || null==$othersign || null == $floor){
            $msg['code']=100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit;
        }else{
            $admininto=$this->getMerchant($key_admin);
            $builddb=M('buildid','total_');
            $find=$builddb->where(array('buildid'=>$build,'adminid'=>$admininto['id']))->find();
            if (null == $find){
                $msg['code']=1047;
                echo returnjson($msg,$this->returnstyle,$this->callback);
            }else{
                //验证签名
                $params['key_admin']=$key_admin;
                $params['build']=$build;
                $params['floor']=$floor;
                $params['sign_key']=$admininto['signkey'];
                $sign=sign($params);//echo $sign;
                if ($sign==$othersign){
                    $admindefault=$this->GetdAmindefault($admininto['pre_table'], $key_admin);
                    //$admin=array_column($admindefault, customer_name);
                    $classname=null;
                    foreach ($admindefault as $key => $val){
                        if ('parkclassname'==$val['customer_name']){
                            $classname=$val['function_name'];
                            break;
                        }
                    }
                    //如果有自己的停车类名
                    if (null != $classname){
                        $obj = new $classname();
                        $result=$obj->getparkstatus($build,$floor,$admininto['signkey'],$key_admin,$admininto);
                        if (is_array($result)){//如果是数组，必然是正确的数据，类文件里面必须处理正确
                            $msg['code']=200;
                            $msg['data']=$result;
                            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
                        }elseif (is_string($result)){
                            $msg['code']=$result;
                            echo returnjson($msg,$this->returnstyle,$this->callback);
                        }else {
                            $msg['code']=104;
                            echo returnjson($msg,$this->returnstyle,$this->callback);
                        }
                    }else{//没有就用公用的类，暂时没有公用的类
                
                    }
                }else{
                    $msg['code']=1002;//签名错误
                    $msg['data']='error';
                    echo returnjson($msg,$this->returnstyle,$this->callback);
                }
            }
        }
    }
}

?>