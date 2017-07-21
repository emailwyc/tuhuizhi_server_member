<?php
namespace Oywechat\Controller\Thirdwechat;

//use Common\Controller\CommonController;
use Common\Controller\ErrorcodeController;
class ThirdwechatcommonController extends ErrorcodeController{
    // TODO - Insert your code here
    
    protected $token='oya_open_wechat';//第三方平台申请时填写的接收消息的校验token
    protected $encodingAesKey='2a2ba460c451ca4bc459ff6d9eae6a9dc451ca4bc45';//第三方平台申请时填写的接收消息的加解密symmetric_key
    protected $appId='wx5a529bc337fe1d6f';//公众号第三方平台的appid
    protected $appsecret='8bf25b1feac5345cc0775188eb23188d';//公众号第三方平台的appsecret
    
    protected $component_access_token_url='https://api.weixin.qq.com/cgi-bin/component/api_component_token';
    protected $pre_auth_code_url='https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=';
    
    protected $authorizer_url='https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=';
    protected $componentloginpage_url='https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=[component_appid]&pre_auth_code=[pre_auth_code]&redirect_uri=[redirect_uri]';//第三方公众号授权给我们
    
    protected $gettoken_url='https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=';
    protected $authorizer_refresh_token_url='https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=';
    protected $get_authorizer_info_url='https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=';
    protected $get_authorizer_option_url='https://api.weixin.qq.com/cgi-bin/component/ api_get_authorizer_option?component_access_token=';//7、获取授权方的选项设置信息
    protected $set_authorizer_option_url='https://api.weixin.qq.com/cgi-bin/component/ api_set_authorizer_option?component_access_token=';
    

    

    
    protected $option_names=array('location_report'=>1,'voice_recognize'=>1,'customer_service'=>1);
    public function _initialize(){
        //header("content-Type: text/html; charset=UTF-8");
        header ( "Content-Type:text/html;charset=utf-8" );
        parent::__initialize();
    }
}

?>