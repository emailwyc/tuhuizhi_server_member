<?php
namespace ResourcesApi\Controller;
use Think\Controller;
use Common\Controller\RedisController as A;
/**
 * 商户子支付账户相关
 * @author soone
 * @date 2016-12-6
 */
class MerchantController extends ResCommonController
{
	public function _initialize(){
		parent::_initialize();
	}

	/**
	 * 根据建筑物id和poi信息查询商户子支付账户
	 * @param array
     * @return mixed
	 */
	public function getChildAccount() {
		//check params
		$params = $this->params;
		$this->emptyCheck($params,array('buildid','floor','poi_no'));
		$db = M('pay_child', 'total_');
		$field = "buildid,floor,poi_no,poi_name,pay_child_account";
		$data = $db->field($field)->where(array('buildid'=>$params['buildid'],'floor'=>$params['floor'],'poi_no'=>$params['poi_no']))->find();
		$msg = !empty($data)?array('code'=>200,'data'=>$data):array('code'=>102);
		returnjson($msg, $this->returnstyle, $this->callback);
	}
    
}

?>
