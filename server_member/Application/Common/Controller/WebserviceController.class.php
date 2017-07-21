<?php
namespace Common\Controller;

use Think\Controller;

/**
 * 1、$webservice= new WebserviceController('xidan_');
 * 2、 $client=$webservice->soapClient(url);
 * 3、$header=$webservice->soapHeader(........);
 * 3、$result= $webservice->sopaCall($client,array(array(xxxxxxxxxxxxxxxxxxxx)));三维数组
 * @author kaifeng
 *
 */
class WebserviceController extends Controller
{
    private $namespace;//认证的命名空间
    private $soapheader;//认证的头信息
    private $headersparams;//认证的参数
    private $isauth=false;//判断是否需要认证
    private $url;//请求的webservice地址
    private $params;//请求的参数
    private $result;//返回的结果
    private $pre_tab;
    private $function_name;
    
    /**
     * 构造函数，如若在实例化类时，传递了后两个参数，则认为此webservice需要认证
     * @param string $namespace 命名空间，可空
     * @param string $soapheader SoapHeader参数
     * @param array $params         认证参数，如用户名name=>'username'
     * @param string $pre_tab 
     */
    public function __construct( string $pre_tab, $namespace='', $soapheader='', array $params=array()) 
    {
        if ($soapheader != '' && $params != null){
            $this->namespace=$namespace;
            $this->soapheader=$soapheader;
            $this->headersparams=$params;
            $this->isauth=true;
        }
        $this->pre_tab=$pre_tab;//表前缀，用作记录日志（按商家记录）；
    }
    
    /**
     * webservice 认证方法，仅仅用作认证
     */
    private function soapHeader()
    {
        $header=new \SoapHeader($this->namespace, $this->soapheader, $this->headersparams);
        return $header;
    }
    
    /**
     * 实例化webservice SoapClient类，并根据实际添加认证
     * @param string $url
     * @param array $params
     */
    public function soapClient(string $url)
    {
        $client= new \SoapClient($url, array());
        //如果有认证，则调用认证方法
        if (true === $this->isauth){
            $headers=$this->soapHeader();
            $client->__setSoapHeaders($headers);
        }
        $this->url=$url;
        return $client;
    }
    
    /**
     * 调用server端函数
     * @param object $client  SoapClient
     * @param array $params 传递的参数
     */
    public function sopaCall(string $function_name,  $client, array $params, array $option=null, $headers=null, array $output_headers=array())
    {
        $result = $client->__soapCall($function_name,$params,$option);
        $this->function_name=$function_name;
        $this->result=$result;
        $this->params=$params;
        $this->log();
        return $result;
    }
    
    /**
     * 本类中仅用作记录日志
     */
    private function log()
    {
        $result=json_decode(json_encode($this->result),true);
        $log=array(
            'isauth'=>(string)$this->isauth,
            'namespace'=>$this->namespace,
            'soapheader'=>$this->soapheader,
            'headersparams'=>$this->headersparams,
            'url'=>$this->url,
            'function_name'=>$this->function_name,
            'params'=>$this->params,
            'result'=>$result
        );
        $file=$this->pre_tab;
        writeOperationLog($log,'webservice/'.$file);
    }
    
}

?>