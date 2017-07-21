<?php
namespace AlipayService\Controller;
Vendor('Alipay.AopSdk');
use Think\Controller;
use Common\Controller\RedisController as A;
/**
 * 蚂蚁花呗接口处理类
 * @author soone
 * @date 2016-12-6
 */
class AntsController extends AlipayCommonController
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
     * 获取某个用户蚂蚁信用是否满足准入条件
     * @param array
     * @return mixed
     */
    public function creditScoreBrief() {
        $this->emptyCheck($this->params,array('userid','score'));
        $transactionId = $this->params['userid'].$this->params['score'].date('Ymd');
        $request = new \ZhimaCreditScoreBriefGetRequest ();
        $request->setBizContent(
        	json_encode(
        		array(
        			"transaction_id"=>$transactionId,
					"product_code"=>"w1010100000000002733",
					"cert_type"=>'ALIPAY_USER_ID',
					"cert_no"=>$this->params['userid'],
					"admittance_score"=>$this->params['score']
				)
			)
		);
        $result = $this->aop->execute ( $request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode;
        if(!empty($resultCode)&&$resultCode->code==10000){
            $resArr = (array)$result->$responseNode;
            $db = M('mem', $this->adminInfo['pre_table']);
            $creditScore = $this->params['score'].$resArr['is_admittance'];
            $db->where(array('userid' => $this->params['userid']))->save(array('credit_score' => $creditScore));
            returnjson(array("code"=>200,"data"=>$resArr),$this->returnstyle,$this->callback);exit;
        } else {
            returnjson(array("code"=>104),$this->returnstyle,$this->callback);exit;
        }
    }

    /**
	 * 蚂蚁花呗 会员卡查询;
	 * @param array
     * @return mixed
	 */
	public function getCardInfo() {
		//check params
		$this->emptyCheck($this->params,array('target_card_no'));
		//调用会员卡接口
		$request = new \AlipayMarketingCardQueryRequest ();
		$bizContent = array(
			'target_card_no' => $this->params['target_card_no'],
			'target_card_no_type' => 'BIZ_CARD',
		);
		$request->setBizContent(json_encode($bizContent));
		$result = $this->aop->execute ( $request); 
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
			//整合数据返回
			$data=array();
			$msg=array('code'=>200,'data'=>$data);
		} else {
			$msg=array('code'=>104,'data'=>"Alipay Api fail;code:".$resultCode);
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 模板logo图片上传接口
	 *
	 * @param array
     * @return mixed
	 */
	public function uploadTemLogo() {
		//check params
		$this->emptyCheck($this->params,array('target_card_no'));
		//logo图片上传接口
		$request = new AlipayOfflineMaterialImageUploadRequest ();
		$request->setImageType("jpg或mp4");
		$request->setImageName("海底捞");
		$request->setImageContent("@"."本地文件路径");
		$request->setImagePid("2088021822217233");
		$result = $aop->execute ( $request); 
		 
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;	
		if(!empty($resultCode)&&$resultCode == 10000){
			//整合数据返回
			$data=array();
			$msg=array('code'=>200,'data'=>$data);
		} else {
			$msg=array('code'=>104,'data'=>"Alipay Api fail;code:".$resultCode);
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

    
    
    
}

?>
