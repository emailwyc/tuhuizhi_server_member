<?php
namespace AlipayService\Controller;
Vendor('Alipay.AopSdk');
use Think\Controller;
use Common\Controller\ErrorcodeController;
use Common\Controller\RedisController;
/**
 * 支付宝会员卡接口处理类
 * @author soone
 * @date 2017-6-16
 */
class MarketCardController extends AlipayCommonController
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
     * 获取用户卡信息接口
     * @param array
     * @return mixed
     */
    public function getUserCardInfo() {
        $this->emptyCheck($this->params,array('cardno','userid'));
        $request = new \AlipayMarketingCardQueryRequest ();
        $request->setBizContent(
            json_encode(array(
                "target_card_no"=>$this->params['cardno'],
                "target_card_no_type"=>'BIZ_CARD',
                "card_user_info"=>array(
                    'user_uni_id'=>$this->params['userid'],
                    'user_uni_id_type'=>"UID"
                )
            ))
            );
        $result = $this->aop->execute ($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode;
        if(!empty($resultCode)&&$resultCode->code==10000){
            $resArr = (array)$result->$responseNode;
            returnjson(array("code"=>200,"data"=>$resArr),$this->returnstyle,$this->callback);exit;
        } else {
            returnjson(array("code"=>104),$this->returnstyle,$this->callback);exit;
        }
    }



    public function openUserCard() {
        $this->emptyCheck($this->params,array('cardno','userid',"tplid"));
        $accessToken = $this->redis->get('alipay:'.$this->params['userid'].':access_token:userid');
        if(empty($accessToken)){
            $url = C('DOMAIN') . '/AlipayService/Oauth/getUserInfo';
            $data = array('key_admin'=>$this->adminInfo['ukey'],'jumpurl'=>"http://www.rtmap.com",'scope'=>'auth_user,auth_ecard','state'=>'system');
            http($url, $data, 'post');
            $accessToken = $this->redis->get('alipay:'.$this->params['userid'].':access_token:userid');
            if(empty($accessToken)){
                returnjson(array("code"=>11,'msg'=>"授权失败！"));exit;
            }
        }
        if(!empty($accessToken)){
            $accessToken = json_decode($accessToken,true);
        }
        $request = new \AlipayMarketingCardOpenRequest ();
        $request->setBizContent(
            json_encode(array(
                "out_serial_no"=>date('YmdHis'),
                "card_template_id"=>$this->params['tplid'],
                "card_user_info"=>array(
                    "user_uni_id"=>$this->params['userid'],
                    "user_uni_id_type"=>"UID",
                ),
                "card_ext_info"=>array(
                    "biz_card_no"=>$this->params['cardno'],
                    "external_card_no"=>$this->params['cardno'],
                    "open_date"=>date('Y-m-d H:i:s'),
                    "valid_date"=>date('Y-m-d H:i:s', time()+10*365*24*3600),
                    "level"=>"VIP",
                    "point"=>"",
                    "balance"=>"",
                )
            ))
            );
        writeOperationLog(array('marketcard alipay' => $accessToken), 'marketCard');
        $result = $this->aop->execute ( $request , $accessToken['access_token']);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode;
        writeOperationLog(array('marketcard alipay' => (array)$result), 'marketCard');
        if(!empty($resultCode)&&$resultCode->code==10000){
            //缓存结果并返回；
            $resArr = (array)$result->$responseNode;
            returnjson(array("code"=>200,"data"=>$resArr),$this->returnstyle,$this->callback);exit;
        } else {
            returnjson(array("code"=>104),$this->returnstyle,$this->callback);exit;
        }
    }

}

?>
