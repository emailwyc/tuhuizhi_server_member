<?php
namespace CrmService\Controller\Crmapi;

use CrmService\Controller\CommonController;
class CrmController extends CommonController{
    // TODO - Insert your code here
    public function _initialize(){
        parent::_initialize();
    }
    
    
    /**
     * @desc    请求crm系统，获取会员信息
     * @param unknown $url
     * @param unknown $card
     * @param unknown $header
     * @param $request_param_type  请求方式，get，post等等
     * @param unknown $type 请求方式，http，webservice等等
     */
    protected function getuserinfo_card($crmapiinfo,$crmdata,$type,$card){
        //http方式请求
        if ('webservice-http'==$type){
            $xml=$crmapiinfo['request_data'];//要请求的xml
            $api_request=json_decode($crmapiinfo['api_request'],true);
            $db=M('default',$crmdata['pre_table']);
            $sel=$db->select();
            //dump($sel);
            $toarray_member='';
            foreach ($sel as $k => $v){//商户常量配置信息
                foreach ($api_request as $key => $val){//请求接口对应的key
                    if ($key==$v['customer_name']){
                        $xml=str_replace($val,$v['function_name'],$xml);
                    }
                }
                if ('toarray_member'==$v['customer_name']){
                  $toarray_member=$v['function_name'];  
                }
            }
            $xml=str_replace('__card__',$card,$xml);
            try {
                $return=http($crmapiinfo['api_url'], $xml,$crmapiinfo['request_param_type'],json_decode($crmapiinfo['header'],true),true);
            } catch (Exception $e) {
                echo returnjson($data);
            }
            
            
            //如果有自定义解析函数
            if (''!=$toarray_member){
                $array=call_user_func($toarray_member,$return);
            }
            //dump($array);
        }elseif ('http'==$type){//http请求方式
            
        }elseif ('webservice'==$type){//webservice方式请求
            
        }
        return $array;//$array是返回特定的key-val数组格式
    }
    
    
    
    /**
     * @desc    请求crm系统，获取会员信息
     * @param unknown $url
     * @param unknown $card
     * @param unknown $header
     * @param $request_param_type  请求方式，get，post等等
     * @param unknown $type 请求方式，http，webservice等等
     */
    protected function getuserinfo_mobile($crmapiinfo,$crmdata,$type,$mobile){
        //http方式请求
        if ('webservice-http'==$type){
            $xml=$crmapiinfo['request_data'];//要请求的xml
            $api_request=json_decode($crmapiinfo['api_request'],true);
            $db=M('default',$crmdata['pre_table']);
            $sel=$db->select();
            //dump($sel);
            $toarray_member='';
            foreach ($sel as $k => $v){//商户常量配置信息
                foreach ($api_request as $key => $val){//请求接口对应的key
                    if ($key==$v['customer_name']){
                        $xml=str_replace($val,$v['function_name'],$xml);
                    }
                }
                if ('toarray_member'==$v['customer_name']){
                    $toarray_member=$v['function_name'];
                }
            }
            $xml=str_replace('__mobile__',$mobile,$xml);
            //dump($xml);die;
            try {
                $return=http($crmapiinfo['api_url'], $xml,$crmapiinfo['request_param_type'],json_decode($crmapiinfo['header'],true),true);
            } catch (Exception $e) {
                echo returnjson($data);
            }
    
            //如果有自定义解析函数
            if (''!=$toarray_member){
                $array=call_user_func($toarray_member,$return);
            }
            //dump($array);
        }elseif ('http'==$type){//http请求方式
    
        }elseif ('webservice'==$type){//webservice方式请求
    
        }
        return $array;//$array是返回特定的key-val数组格式
    }
    
    
    
    
    
    
    /**
     * @desc    请求crm系统，创建会员信息
     * @param unknown $url
     * @param unknown $card
     * @param unknown $header
     * @param $request_param_type  请求方式，get，post等等
     * @param unknown $type 请求方式，http，webservice等等
     */
    protected function create_member($crmapiinfo,$crmdata,$type,$params){
        //http方式请求
        if ('webservice-http'==$type){
            $xml=$crmapiinfo['request_data'];//要请求的xml
            $api_request=json_decode($crmapiinfo['api_request'],true);//dump($api_request);
            $db=M('default',$crmdata['pre_table']);
            $sel=$db->select();//echo $xml;
            //dump($sel);
            $createcardfun='';
            $createcallbackfun='';
            foreach ($sel as $k => $v){//商户常量配置信息
                foreach ($api_request as $key => $val){//请求接口对应的key
                    //dump($v);
                    if ('sex'==$v['customer_name']){
                        $sexarr=json_decode($v['function_name'],true);//dump($sexarr);
                        $sex=$sexarr[$params['sex']];//echo $sex;
                        $xml=str_replace('__sex__',$sex,$xml);
                        //$params['sex']=$sex;
                    }
                    if ($key==$v['customer_name']){
                        $xml=str_replace($val,$v['function_name'],$xml);
                    }
                }
                if ('createcard'==$v['customer_name']){
                    $createcardfun=$v['function_name'];
                }if ('createcallbackfun'==$v['customer_name']){
                    $createcallbackfun=$v['function_name'];
                }
                
            }
            //把剩下的字符串替换掉
            $xmlarray=call_user_func($createcardfun,$xml,$crmapiinfo,$params,$crmdata,$sel);//注意，$xml是一个数组
            $xml=$xmlarray['xml'];
            try {
                $return=http($crmapiinfo['api_url'], $xml,$crmapiinfo['request_param_type'],json_decode($crmapiinfo['header'],true),true);
            } catch (Exception $e) {
                echo returnjson('');
            }
            //如果有自定义解析函数
            if (''!=$createcallbackfun){
                $status=call_user_func($createcallbackfun,$return);
                if (true==$status){
                    $member=array(
                        'mobile'=>$params['mobile'],
                    );
                    if (1<count($xmlarray)){
                        $member=$xmlarray;
                        unset($member['xml']);
                    }
                }
            }
        }elseif ('http'==$type){//http请求方式
    
        }elseif ('webservice'==$type){//webservice方式请求
    
        }
        return $member;//$array是返回特定的key-val数组格式
    }
    /*<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><processdataResponse xmlns="http://tempurl.org"><processdataResult>1</processdataResult><outputpara>0000000210	80000001	www	201	01	N	06	11-JUN-14	10-JUN-15	1	330327197601011410	01-JAN-76	40	4								193	0	200	0</outputpara><rtn>1</rtn><errormsg /></processdataResponse></soap:Body></soap:Envelope>*/
    
    
    
    
    
    
    /**
     * @desc    请求crm系统，创建会员信息
     * @param unknown $url
     * @param unknown $card
     * @param unknown $header
     * @param $request_param_type  请求方式，get，post等等
     * @param unknown $type 请求方式，http，webservice等等
     */
    protected function edit_member($crmapiinfo,$crmdata,$type,$params){
        //http方式请求
        if ('webservice-http'==$type){
            $xml=$crmapiinfo['request_data'];//要请求的xml
            $api_request=json_decode($crmapiinfo['api_request'],true);//dump($api_request);
            $db=M('default',$crmdata['pre_table']);
            $sel=$db->select();
            $createcardfun='';
            $createcallbackfun='';
            $sexarr='';
            foreach ($sel as $k => $v){//商户常量配置信息
                foreach ($api_request as $key => $val){//请求接口对应的key
                    if ($key==$v['customer_name']){
                        $xml=str_replace($val,$v['function_name'],$xml);
                    }
                    if ('sex'==$v['customer_name']){
                        $sexarr=json_decode($v['function_name'],true);
                    }
                    
                }
                if ('editmemberfun'==$v['customer_name']){
                    $createcardfun=$v['function_name'];
                }if ('createcallbackfun'==$v['customer_name']){
                    $createcallbackfun=$v['function_name'];
                }
    
            }
            if ($sexarr!=''){
                $sex=$sexarr[$params['sex']];
                $xml=str_replace('__sex__',$sex,$xml);//dump($xml);die;
            }
            //把剩下的字符串替换掉
            $xmlarray=call_user_func($createcardfun,$xml,$crmapiinfo,$params,$crmdata,$sel);//注意，$xml是一个数组
            $xml=$xmlarray['xml'];
            try {
                $return=http($crmapiinfo['api_url'], $xml,$crmapiinfo['request_param_type'],json_decode($crmapiinfo['header'],true),true);
            } catch (Exception $e) {
                echo returnjson('');
            }
            //如果有自定义解析函数
            if (''!=$createcallbackfun){
                $status=call_user_func($createcallbackfun,$return);
                if (true==$status){
                    $member=array(
                        'mobile'=>$params['mobile'],
                    );
                    if (1<count($xmlarray)){
                        $member=$xmlarray;
                        unset($member['xml']);
                    }
                }
            }
        }elseif ('http'==$type){//http请求方式
    
        }elseif ('webservice'==$type){//webservice方式请求
    
        }
        return $member;//$array是返回特定的key-val数组格式
    }
    
    
    
    
    
    
    
    /**
     * @desc   扣除积分
     * @param unknown $score
     * @param unknown $card
     */
    protected function cut_score($api,$admininfo,$type,$score,$card,$membername){
        //http方式请求
        if ('webservice-http'==$type){
            $xml=$api['request_data'];//要请求的xml
            $api_request=json_decode($api['api_request'],true);//dump($api_request);
            $db=M('default',$admininfo['pre_table']);
            $sel=$db->select();//echo $xml;
            //dump($sel);
            $putcontent='';
            $cutscorebackfun='';
            foreach ($sel as $k => $v){//商户常量配置信息
                foreach ($api_request as $key => $val){//请求接口对应的key
                    if ($key==$v['customer_name']){
                        $xml=str_replace($val,$v['function_name'],$xml);
                    }
                }
                if ($v['customer_name']=='userinfoinputparacutscore'){
                    $putcontent=$v['function_name'];
                }
                if ($v['customer_name']=='cutscorebackfun'){
                    $cutscorebackfun=$v['function_name'];
                }
            }
            //dump($xml);
            //dump($putcontent);
            //把剩下的字符串替换掉
            $xml=call_user_func($putcontent,$xml,$card,$score,$membername,$putcontent);
            //dump($xml);die;
            //dump($xml);//die;
            try {
                $return=http($api['api_url'], $xml,$api['request_param_type'],json_decode($api['header'],true),true);
            } catch (Exception $e) {
                echo returnjson('');
            }
            //如果有自定义解析函数
            if (''!=$cutscorebackfun){
                $status=call_user_func($cutscorebackfun,$return);
            }else{//公共解析函数
                
            }
        }elseif ('http'==$type){//http请求方式
        
        }elseif ('webservice'==$type){//webservice方式请求
        
        }
        return $status;//$array是返回特定的key-val数组格式
    }
    
    
    
    /**
     * @desc   扣除积分
     * @param unknown $score
     * @param unknown $card
     */
    protected function add_score($api,$admininfo,$type,$score,$card,$scorecode,$membername){
        //http方式请求
        if ('webservice-http'==$type){
            $xml=$api['request_data'];//要请求的xml
            $api_request=json_decode($api['api_request'],true);//dump($api_request);
            $db=M('default',$admininfo['pre_table']);
            $sel=$db->select();//echo $xml;
            //dump($sel);
            $putcontent='';
            $cutscorebackfun='';
            foreach ($sel as $k => $v){//商户常量配置信息
                foreach ($api_request as $key => $val){//请求接口对应的key
                    if ($key==$v['customer_name']){
                        $xml=str_replace($val,$v['function_name'],$xml);
                    }
                }
                if ($v['customer_name']=='userinfoinputparaaddscore'){
                    $putcontent=$v['function_name'];
                }
                if ($v['customer_name']=='cutscorebackfun'){
                    $cutscorebackfun=$v['function_name'];
                }
            }
//             dump($putcontent);
//             dump($xml);
            //把剩下的字符串替换掉
            $xml=call_user_func($putcontent,$xml,$card,$score,$scorecode,$putcontent,$membername);
            
//             dump($xml);//die;
            try {
                $return=http($api['api_url'], $xml,$api['request_param_type'],json_decode($api['header'],true),true);
            } catch (Exception $e) {
                echo returnjson('');
            }
//             dump($return);
            //如果有自定义解析函数
            if (''!=$cutscorebackfun){
                $status=call_user_func($cutscorebackfun,$return);
            }else{//公共解析函数
    
            }
        }elseif ('http'==$type){//http请求方式
    
        }elseif ('webservice'==$type){//webservice方式请求
    
        }
        return $status;//$array是返回特定的key-val数组格式
    }
    
    /**
     * 解绑
     */
    public function UnBind(){}
    
}

?>