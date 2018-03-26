<?php
namespace Common\Controller;

use Think\Controller;
class PublicController extends Controller{
    // TODO - Insert your code here
    
    
    
    /**
     * @desc  获取用户卡号
     * @param unknown $url
     * @param unknown $params
     * @param string $method
     * @param unknown $header
     */
    public function getusercard($url,$params,$method='GET',$header=array()){
        $result=http($url, $params,$method);
        return $result;
        
    }
    
    /**
     * @desc 获取用户会员信息
     * @param unknown $url
     * @param unknown $params
     * @param string $method
     * @param unknown $header
     */
    public function getuserinfos($url,$params,$method='GET',$header=array()) {
        
    }
}

?>