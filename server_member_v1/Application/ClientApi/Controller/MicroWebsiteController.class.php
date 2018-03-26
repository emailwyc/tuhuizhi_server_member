<?php
namespace ClientApi\Controller;
use Think\Controller;
use Common\Controller\RedisController as A;
/**
 * 微网站相关
 * @author soone
 * @date 2016-01-09
 * error:11,12,13,14,15,16,17
 */
class MicroWebsiteController extends ClientCommonController
{
	public function _initialize(){
		parent::_initialize();
		//$params = $this->params;
		//$this->emptyCheck($params,array('buildid','floor','poi_no'));
	}
    
	/**
	 * center clolr
	 * @param array
     * @return mixed
	 */
	public function getCenterAdvertColor() {
		//check params
		$db = M('navigation', $this->setting['pre_table']);
		$arr = $db->where(array('position'=>'center'))->find();
		$msg = !empty($arr)?array('code'=>200,'data'=>$arr):array('code'=>102);
		returnjson($msg, $this->returnstyle, $this->callback);
	}
	/**
	 * 获取顶部广告列表
	 * @param array
     * @return mixed
	 */
	public function getTopAdvertList() {
		//check params
		$db = M('navigation', $this->setting['pre_table']);
		$resdb = M('nav_resour', $this->setting['pre_table']);
		$arr = $db->where(array('position'=>'top'))->find();
		if(empty($arr)){ returnjson(array('code'=>11,'msg'=>'未找到相关数据'), $this->returnstyle, $this->callback);exit; }
		if($arr['status']==2){ returnjson(array('code'=>12,'msg'=>'顶部广告已禁用'), $this->returnstyle, $this->callback);exit; }
		$field = "id,name,link,property,sort";
		$res = $resdb->field($field)->where(array('type_id'=>$arr['id']))->order('sort asc')->select();
		$msg = !empty($res)?array('code'=>200,'data'=>$res):array('code'=>102);
		returnjson($msg, $this->returnstyle, $this->callback);
	}

	/**
	 * 获取底部广告列表
	 * @param array
     * @return mixed
	 */
	public function getFootAdvertList() {
		//check params
		$db = M('navigation', $this->setting['pre_table']);
		$resdb = M('nav_resour', $this->setting['pre_table']);
		$arr = $db->where(array('position'=>'foot'))->find();
		if(empty($arr)){ returnjson(array('code'=>11,'msg'=>'未找到相关数据'), $this->returnstyle, $this->callback);exit; }
		if($arr['status']==2){ returnjson(array('code'=>12,'msg'=>'底部广告已禁用'), $this->returnstyle, $this->callback);exit; }
		$field = "id,name,link,property,sort";
		$res = $resdb->field($field)->where(array('type_id'=>$arr['id']))->find();
		$msg = !empty($res)?array('code'=>200,'data'=>$res):array('code'=>102);
		returnjson($msg, $this->returnstyle, $this->callback);
	}

	/**
	 * 获取中部泡泡列表
	 * @param array
     * @return mixed
	 */
	public function getBubbleList() {
		//check params
		$db = M('navigation', $this->setting['pre_table']);
		$resdb = M('nav_resour', $this->setting['pre_table']);
		$arr = $db->where(array('position'=>'center'))->find();
		if(empty($arr)){ returnjson(array('code'=>11,'msg'=>'未找到相关数据'), $this->returnstyle, $this->callback);exit; }
		if($arr['status']==2){ returnjson(array('code'=>12,'msg'=>'中部功能区域已禁用'), $this->returnstyle, $this->callback);exit; }
		$res = $resdb->where(array('type_id'=>$arr['id']))->order('sort asc')->select();
		$res = ArrKeyFromId($res,'sort');
		for($i=1;$i<=8;$i++){
			$res[$i] = empty($res[$i])?array('id'=>'','name'=>'','link'=>'','author'=>'','content'=>'','property'=>'','sort'=>'','type_id'=>'','createtime'=>''):$res[$i];
		}
		$msg = !empty($res)?array('code'=>200,'data'=>$res):array('code'=>102);
		returnjson($msg, $this->returnstyle, $this->callback);
	}

	/**
	 * 获取广告内容
	 * @param array
     * @return mixed
	 */
	public function getAdvertContent() {
		//check params
		$params = $this->params;
		$this->emptyCheck($params,array('id'));
		$resdb = M('nav_resour', $this->setting['pre_table']);
		$res = $resdb->where(array('id'=>$params['id']))->find();
		if($res){
			$res['content'] = htmlspecialchars_decode($res['content']);
		}
		$msg = !empty($res)?array('code'=>200,'data'=>$res):array('code'=>102);
		returnjson($msg, $this->returnstyle, $this->callback);
	}
    
}

?>
