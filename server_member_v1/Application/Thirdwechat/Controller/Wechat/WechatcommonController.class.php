<?php
namespace Thirdwechat\Controller\Wechat;


use Thirdwechat\Controller\Thirdwechat\ThirdwechatcommonController;
use Thirdwechat\Controller\Thirdwechat\EventsController;
use Common\Controller\RedisController;
use Common\Controller\ErrorcodeController;
class WechatcommonController extends ThirdwechatcommonController{
    // TODO - Insert your code here
    
    protected $codeurl='https://open.weixin.qq.com/connect/oauth2/authorize?appid=[APPID]&redirect_uri=[REDIRECT_URI]&response_type=code&scope=[SCOPE]&state=[STATE]&component_appid=[component_appid]#wechat_redirect';
    protected $access_token_url='https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=[APPID]&code=[CODE]&grant_type=authorization_code&component_appid=[COMPONENT_APPID]&component_access_token=[COMPONENT_ACCESS_TOKEN]';
    protected $get_userinfo_url='https://api.weixin.qq.com/sns/userinfo?access_token=[ACCESS_TOKEN]&openid=[OPENID]&lang=zh_CN';
    
    
    protected $create_menu_url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token=';
    protected $get_menu_url='https://api.weixin.qq.com/cgi-bin/menu/get?access_token=';
    
    
    //会员接口url
    protected $get_user_openid='https://api.weixin.qq.com/cgi-bin/user/get?access_token=[ACCESS_TOKEN]&next_openid=';
    protected $get_user_info_by_onenid='https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=';
    protected $get_user_info_one='https://api.weixin.qq.com/cgi-bin/user/info?access_token=[ACCESS_TOKEN]&openid=[OPENID]&lang=zh_CN';
    
    //js_sdk
    protected $get_jsapi_ticket='https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=[ACCESS_TOKEN]&type=jsapi';
    
    
    //微信会员卡
    protected $batch_get_card='https://api.weixin.qq.com/card/batchget?access_token=';
    protected $get_card_list='https://api.weixin.qq.com/card/user/getcardlist?access_token=';
    protected $get_membercard='https://api.weixin.qq.com/card/membercard/userinfo/get?access_token=';
    protected $get_card_type='https://api.weixin.qq.com/card/get?access_token=';
    
    //素材
    protected $batch_get_material='https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=';
    protected $batch_get_material_count='https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=';
    protected $add_news_url='https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=';
    protected $uploadimg='https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=';
    protected $add_material_url='https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=[ACCESS_TOKEN]&type=';
    protected $upload_news_url='https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=';
    protected $upload='http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=[ACCESS_TOKEN]&type=';
    protected $upload_video_media_id='https://file.api.weixin.qq.com/cgi-bin/media/uploadvideo?access_token=';
    
    
    
    //模板消息url
    protected $set_industry='https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token=';
    protected $get_industry='https://api.weixin.qq.com/cgi-bin/template/get_industry?access_token=';
    protected $add_template='https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token=';
    protected $get_all_private_template='https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=';
    protected $del_private_template='https://api.weixin.qq.com/cgi-bin/template/del_private_template?access_token=';
    protected $send_message_template='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=';
    
    
    //客服消息
    protected $send_service_message='https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=';
    
    //群发消息接口素材
    protected $send_all='https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=';
    protected $send_openid='https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=';


    //二维码，临时二维码和永久二维码接口地址在微信的接口文档中是一个,https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1443433542
    protected $qrcode = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=';

    /**********
     **小程序 **
    **********/
    protected $miniprogram_getopenid = 'https://api.weixin.qq.com/sns/component/jscode2session?appid=[APPID]&js_code=[JSCODE]&grant_type=authorization_code&component_appid=[COMPONENT_APPID]&component_access_token=[ACCESS_TOKEN]';
    public static $wechatMiniProgramDomain = 'https://api.weixin.qq.com/wxa/modify_domain?access_token=[TOKEN]';
    public static $wechatMiniProgramBind = 'https://api.weixin.qq.com/wxa/bind_tester?access_token=[TOKEN]';
    public static $wechatMiniProgramUnbind = 'https://api.weixin.qq.com/wxa/unbind_tester?access_token=[TOKEN]';
    public static $wechatMiniProgramQrcodeA = 'https://api.weixin.qq.com/wxa/getwxacode?access_token=[ACCESS_TOKEN]';
    public static $wechatMiniProgramQrcodeB = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=[ACCESS_TOKEN]';
    public static $wechatMiniProgramQrcodeC = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=[ACCESS_TOKEN]';
    public static $wechatMiniProgramtemplatelibrarylist = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/library/list?access_token=[ACCESS_TOKEN]';
    public static $wechatMiniProgramtemplatelibraryword = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/library/get?access_token=[ACCESS_TOKEN]';
    public static $wechatMiniProgramtemplatewxopenadd = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/add?access_token=[ACCESS_TOKEN]';
    public static $wechatMiniProgramtemplatewxopenlist = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/list?access_token=[ACCESS_TOKEN]';
    public static $wechatMiniProgramtemplatewxopendel = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/del?access_token=[ACCESS_TOKEN]';
    public static $wechatMiniProgramCodeCommit = 'https://api.weixin.qq.com/wxa/commit?access_token=[TOKEN]';
    public static $wechatMiniProgramExperienceQrcode = 'https://api.weixin.qq.com/wxa/get_qrcode?access_token=[TOKEN]';//获取体验小程序的体验二维码
    public static $wechatMiniProgramGetCategory = 'https://api.weixin.qq.com/wxa/get_category';//?access_token=[TOKEN]
    public static $wechatMiniProgramGetPage = 'https://api.weixin.qq.com/wxa/get_page';//?access_token=TOKEN'
    public static $wechatMiniProgramSubmitAudit = 'https://api.weixin.qq.com/wxa/submit_audit?access_token=[TOKEN]';
    public static $wechatMiniProgramGetAuditStatus = 'https://api.weixin.qq.com/wxa/get_auditstatus?access_token=[TOKEN]';
    public static $wechatMiniProgramGetLastAudioStatus = 'https://api.weixin.qq.com/wxa/get_latest_auditstatus';//?access_token=TOKEN
    public static $wechatMiniProgramRelease = 'https://api.weixin.qq.com/wxa/release?access_token=[TOKEN]';
    public static $wechatMiniProgramChangeVisitStatus = 'https://api.weixin.qq.com/wxa/change_visitstatus?access_token=[TOKEN]';
    


    protected $component_access_token;
    //protected $redis;
//     protected $myerrorcode;
    public function _initialize(){
        parent::_initialize();
        //header("content-Type: text/html; charset=UTF-8");
        header ( "Content-Type:text/html;charset=utf-8" );
        $events=new EventsController();
        $this->component_access_token=$events->component_access_token();
        $rediss=new RedisController();
        $this->redis=$rediss->connectredis();
        //$this->redis;
        
//         $errorclass=new ErrorcodeController();
//         $this->myerrorcode=$errorclass->commonerrorcode;
        
//         $common=new CommonController();
//         $this->returnstyle=$common->returnstyle;
//         $this->callback=$common->callback;
    }
    
    
    /**
     * @desc    查询后台商户信息
     * @param unknown $ke_admin
     */
    public function selectadmin($key_admin){
        $db=M('admin','total_');
        $find=$db->where(array('ukey'=>$key_admin))->find();
        if (null == $find){
            return false;
        }else{
            $this->redis->set('wechat:'.$key_admin.':admin',json_encode($find) );
            return $find;
        }
    }


    public static function getAuthorizerAccessToken($appid)
    {
        $events=new EventsController();
        $authorizer_access_token = $events->authorizer_access_token($appid);
        return $authorizer_access_token;
    }

    public static function getComponentAccessToken()
    {
        $events=new EventsController();
        $component_access_token = $events->component_access_token();
        return $component_access_token;
    }
}

?>