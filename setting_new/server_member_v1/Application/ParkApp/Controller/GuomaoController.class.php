<?php
/**
 * 国贸定制停车寻车应用类
 */

namespace ParkApp\Controller;

use Common\Controller\JaleelController;
use Common\Controller\WebserviceController;

class GuomaoController extends JaleelController
{
    /**
     * 扫码停车接口
     */
	public function scancodeparking() {
		$params = I('param.');
		$mer_chant = $this->getMerchant($this->ukey);
		$this->emptyCheck($params,array('openid','position'));
		$obj = M('parklog', $mer_chant['pre_table']);
		$data = array('openid'=>$params['openid'],'position'=>$params['position']);
		$check = $obj->add($data);
		if($check){
			returnjson(array('code'=>200), $this->returnstyle, $this->callback);
		}else{
			returnjson(array('code'=>2,'msg'=>"入库失败"), $this->returnstyle, $this->callback);
		}
	}

    /**
     * 获取车辆上次停放位置
     */
	public function getposition() {
		$params = I('param.');
		$mer_chant = $this->getMerchant($this->ukey);
		$this->emptyCheck($params,array('openid'));
		$obj = M('parklog', $mer_chant['pre_table']);
		$info = $obj->where(array('openid' => $params['openid']))->order("id desc")->find();
		$info = !empty($info)?$info:(object)array();
		returnjson(array('code'=>200,'data'=>$info), $this->returnstyle, $this->callback);
	}

    /**
     * 获取停车场剩余车位
     */
	public function getRemainParking() {
		$params = I('param.');
		$mer_chant = $this->getMerchant($this->ukey);
        $url = 'http://210.12.123.235:888/parkingapi.svc?wsdl';
		$obj = new WebserviceController('guomao_');
		$client = $obj->soapClient($url);
		$ParkId = (int)$params['ParkId'];
		$re = $client->GetFreeSpaceNum(array('strJson'=>json_encode(array('ParkId'=>$ParkId))));
        $curl_re = $re->GetFreeSpaceNumResult;
		$curl_re = json_decode($curl_re,true);
		if((int)$curl_re['ResCode']==0){
			$curl_re['Data'] = empty($curl_re['Data'])?array():$curl_re['Data'];
			returnjson(array('code'=>200,'data'=>$curl_re['Data']), $this->returnstyle, $this->callback);
		}else{
			returnjson(array('code'=>11,'msg'=>$curl_re['ResMsg']), $this->returnstyle, $this->callback);
		}
	}

	protected function emptyCheck($params,$key_arr) {
		foreach($key_arr as $v){
			if(empty($params[$v])){
				$msg['code']=1051;
				echo returnjson($msg,$this->returnstyle,$this->callback);exit;
			}   
		}   
	}

}
