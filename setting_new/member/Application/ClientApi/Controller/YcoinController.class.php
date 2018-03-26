<?php
namespace ClientApi\Controller;
use Think\Controller;
use Common\Controller\RedisController as A;
/**
 * Ｙ币相关
 * @date 2016-03-14
 * error:11,12,13,14,15,16,17
 */
class YcoinController extends ClientCommonController
{
	public function _initialize(){
		parent::_initialize();
		//$params = $this->params;
	}
    
	public function getYcoinUser() {
		$params = $this->params;
		$this->emptyCheck($params,array('openid'));

		//check params
		$db = M('coin', $this->setting['pre_table']);
		$arr = $db->where(array('openid'=>$params['openid']))->find();
		$msg = !empty($arr)?array('code'=>200,'data'=>$arr):array('code'=>102);
		returnjson($msg, $this->returnstyle, $this->callback);
	}

	//getYcoinChangeList
	public function getYcoinChangeList(){
		$params = $this->params;
		$this->emptyCheck($params,array('page','openid'));
		$page = ((int)$params['page'])<=0?1:((int)$params['page']);
		$offset = 10;
		$start = ($page-1)*$offset;
		$db=M('coin_changelog',$this->setting['pre_table']);
		$where = array('openid'=>$params['openid']);
		$arr=$db->where($where)->order("id desc")->limit($start,$offset)->select();
		$count =$db->where($where)->count();
		$allpage = ceil($count/$offset);
		$msg = array('code'=>200,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

    
}

?>
