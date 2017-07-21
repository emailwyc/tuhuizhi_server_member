<?php
namespace AlipayService\Controller;
Vendor('Alipay.AopSdk');
use Think\Controller;
use Common\Controller\ErrorcodeController;
use Common\Controller\RedisController;
/**
 * 支付宝会员卡接口处理类
 * @author soone
 * @date 2017-6-23
 */
class CardTemplateController extends AlipayCommonController
{
    public $aop;
    public function _initialize(){
        parent::_initialize();
        //配置接口sdk相关
        $this->aop = new \AopClient();
        $this->aop->gatewayUrl = C('ALIPAY_SET_LIST.GATEWAYURL');
        $this->aop->appId = $this->adminInfo['alipay_appid'];//动态配置appid
        $this->aop->rsaPrivateKey = $this->adminInfo['alipay_raskey_path'];//动态配置私钥串存储路径
        $this->aop->alipayrsaPublicKey = $this->adminInfo['alipay_pubkey'];//动态配置
        $this->aop->apiVersion = C('ALIPAY_SET_LIST.APIVERSION');
        $this->aop->signType = 'RSA';
        $this->aop->postCharset = C('ALIPAY_SET_LIST.POSTCHARSET');
        $this->aop->format=C('ALIPAY_SET_LIST.FORMAT');
    }

    /**
     * 获取模板信息
     * @param array
     * @return mixed
     */
    public function getTemplateInfo() {
        $this->emptyCheck($this->params,array('tplid'));
        $request = new \AlipayMarketingCardTemplateQueryRequest();
        $request->setBizContent(
            json_encode(array(
                'template_id'=>$this->params['tplid']
            ))
            );
        $result = $this->aop->execute ( $request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = (array)$result->$responseNode;
        echo "<pre>";
        print_r($resultCode);exit;
    }

    /**
     * 修改模板信息
     * @param array
     * @return mixed
     */
    public function editTemplateInfo() {
        $this->emptyCheck($this->params,array('tplid'));
        $request = new \AlipayMarketingCardTemplateModifyRequest ();
        $request->setBizContent(
            '{
"request_id":'.date('YmdHis') . rand(10000,99999).',
"card_type":"OUT_MEMBER_CARD",
"biz_no_suffix_len":"10",
"biz_no_prefix":"",
"write_off_type":"qrcode",
"template_style_info":{
"card_show_name":"花呗联名会员卡",
"logo_id":"KbnCIrpUSieswrxdisV1JwAAACMAAQED",
"color":"RGB(25,168,203)",
"background_id":"fe2PLuynRO2ql43Oy_7X8QAAACMAAQED",
"bg_color":"RGB(25,168,203)",
"feature_descriptions":[
"使用花呗卡可享受免费分期"
]
},
"column_info_list":[
{
"code":"BENEFIT_INFO",
"title":"会员卡号",
"operate_type":"staticinfo",
"value":"$CertNo$"
},
{
"code":"BENEFIT_INFO",
"more_info":{
"title":"会员权益",
"url":"http://fw.joycity.mobi/alipro_v2/index.php?action=user&option=equity",
"params":"{}",
"descs":[]
},
"title":"会员权益",
"operate_type":"openWeb",
"value":""
},
{
"code":"BENEFIT_INFO",
"more_info":{
"title":"会员详情",
"url":"https://https://render.alipay.com/p/f/hbcard/detail.html",
"params":"{}",
"descs":[]
},
"title":"会员详情",
"operate_type":"openWeb",
"value":""
}
],
"field_rule_list":[
{
"field_name":"Level",
"rule_name":"ASSIGN_FROM_REQUEST",
"rule_value":"Level"
}
],
"service_label_list":[
"HUABEI_FUWU"
]
}'

    );
        $result = $aop->execute ( $request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode;
        echo "<pre>";
        print_r($resultCode);exit;
    }


    /**
     * 获取模板信息
     * @param array
     * @return mixed
     */
    public function delMemberCard() {
        $request = new \AlipayMarketingCardDeleteRequest();
        $request->setBizContent(
            json_encode(array(
                'out_serial_no'=>"20170623112211121213",
                'target_card_no'=>"0031795451",
                'target_card_no_type'=>"BIZ_CARD",
                'reason_code'=>"CANCEL",
            ))
        );
        $result = $this->aop->execute ( $request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = (array)$result->$responseNode;
        echo "<pre>";
        print_r($resultCode);exit;
    }
    /**
     * 获取模板信息
     * @param array
     * @return mixed
     */
    public function getMemberInfoByMobile() {
        $user_mobile = '15020021701';
        $ReqDate = date("Ymd");
        $ReqTime = date("His");
        $sign = md5("GetMemberInfo".$ReqDate.$ReqTime.$user_mobile."201606011440");
        $w = 'http://10.5.1.52:62341/CRMApi.ashx?Method=GetMemberInfo&User=XDAPP&Sign='.$sign.'&ReqDate='.$ReqDate.'&ReqTime='.$ReqTime;
        echo $w;exit;
        echo "<pre>";
        print_r($resultCode);exit;
    }

    /**
     * 获取模板信息
     * @param array
     * @return mixed
     */
    public function soone() {
        $request = new \AlipayOfflineMaterialImageUploadRequest ();
        $request->setImageType("jpg");
        $request->setImageName("dayuechengtest");
        $request->setImageContent("@"."/home/soone/webwxgetmsgimg.jpg");
        $result = $this->aop->execute ( $request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode;
        echo "<pre>";
        print_r($resultCode);exit;
    }



}

?>
