<?php
/**
 * Info: 商户管理员相关接口
 * User: wangyc
 * Date: 12/26/16
 * Time: 19:00
 */
namespace DevAdmin\Controller;
use Think\Controller;
use DevAdmin\Controller\DevcommonController;
use Common\Controller\RedisController as A;

class MerchantController extends DevcommonController
{
	public function _initialize(){
		parent::_initialize();
	}

	/**
	 * 1.获取支付子账号列表
	 * @param  $adminid
     * @return mixed
	 */
	public function getPayChildList(){
		$this->emptyCheck(array('adminid'));
		$db=M('pay_child','total_');
		$arr=$db->where(array('adminid'=>$this->params['adminid']))->select();
		$msg = !empty($arr) ? array('code'=>200,'data'=>$arr) : array('code'=>102);
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 2.创建支付子账号
	 * @param  array('adminid','buildid','floor','poi_no','pay_child_account',"poi_name")
     * @return mixed
	 */
	public function createPayChild(){
		$params = $this->params;
		$saveParams = $this->emptyCheck(array('adminid','buildid','floor','poi_no','pay_child_account'));
		$saveParams['poi_name'] = $params['poi_name'];
		//查询adminid是否存在
		$db = M('admin','total_');
		$arr = $db->where(array('id'=>$params['adminid']))->count();
		if(!$arr){ echo returnjson(array('code'=>4002),$this->returnstyle,$this->callback);exit(); }
		//查询是否有重复数据
		$payDb = M('pay_child','total_');
		$arr = $payDb->where(array('buildid'=>$params['buildid'],'floor'=>$params['floor'],'poi_no'=>$params['poi_no']))->count();
		if($arr){ echo returnjson(array('code'=>1008),$this->returnstyle,$this->callback);exit(); }
		//入库
		$res = $payDb->add($saveParams);
		$msg = $res == false ? array('code'=>104):array('code'=>200);
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 3.更新支付子账号
	 * @param  array('id','buildid','floor','poi_no','pay_child_account',"poi_name")
     * @return mixed
	 */
	public function updatePayChild(){
		$params = $this->params;
		$this->emptyCheck(array('id'));
		$saveParams = $this->emptyCheck(array('buildid','floor','poi_no','pay_child_account'));
		$saveParams['poi_name'] = $params['poi_name'];
		//查询是否有重复数据
		$payDb = M('pay_child','total_');
		$arr = $payDb->where(array('buildid'=>$params['buildid'],'floor'=>$params['floor'],'poi_no'=>$params['poi_no'],'id'=>array('neq',$params['id'])))->count();
		if($arr){ echo returnjson(array('code'=>1008),$this->returnstyle,$this->callback);exit(); }
		$res = $payDb->where(array('id'=>$params['id']))->save($saveParams);
		$msg = array('code'=>200);
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 4.删除支付子账号
	 * @param  array('id')
     * @return mixed
	 */
	public function delPayChild(){
		$params = $this->params;
		$saveParams = $this->emptyCheck(array('id'));
		$db = M('pay_child','total_');
		$selInfo = $db->where(array('id'=>$params['id']))->find();
		if(!$selInfo){
			echo returnjson(array('code'=>102),$this->returnstyle,$this->callback);exit();
		}
		$res = $db->where(array('id'=>$params['id']))->delete();
		$msg = array('code'=>200);
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 *5	get支付子账号
	 * @param  array('id')
     * @return mixed
	 */
	public function getPayChildById(){
		$params = $this->params;
		$this->emptyCheck(array('id'));
		$db = M('pay_child','total_');
		$selInfo = $db->where(array('id'=>$params['id']))->find();
		if(!$selInfo){
			echo returnjson(array('code'=>102),$this->returnstyle,$this->callback);exit();
		}
		$msg = array('code'=>200,'data'=>$selInfo);
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

}
