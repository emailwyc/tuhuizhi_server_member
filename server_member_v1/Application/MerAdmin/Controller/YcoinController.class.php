<?php
/**
 * ycoinController
 */
namespace MerAdmin\Controller;
class YcoinController extends AuthController
{
    public $key_admin;
    public $admin_arr;
    public function _initialize(){
        parent::_initialize();
		//查询商户信息
        $this->key_admin=$this->ukey;
        
    }
    
    //getCoinSetting
    public function getCoinSetting(){
        $db=M('coin_setting',$this->admin_arr['pre_table']);
		$arr=$db->select();
        $msg['code']=200; $msg['data']=$arr;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	//editCoinSetting
	public function editCoinSetting(){
		$params = $this->params;
		$this->emptyCheck($params,array('setnumlist'));
		$db=M('coin_setting',$this->admin_arr['pre_table']);
		if(!empty($params['setnumlist']) && is_array($params['setnumlist'])){
			foreach($params['setnumlist'] as $k=>$v){
				if($k>=40){ break;}
				if(!empty($v['mark']) && isset($v['num'])){
					$db->where(array('mark'=>$v['mark']))->save(array('num'=>(int)$v['num']));
				}
			}
		}
        $msg['code']=200;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	//editCoinSetting
	public function editCoinStatusSetting(){
		$params = $this->params;
		$this->emptyCheck($params,array('mark','status'));
		$db=M('coin_setting',$this->admin_arr['pre_table']);
		if(!empty($params['mark']) && isset($params['status'])){
			$status = empty($params['status'])?0:1;
			$db->where(array('mark'=>$params['mark']))->save(array('status'=>$status));
		}
        $msg['code']=200;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	//getUserList
	public function getUserList(){
		$params = $this->params;
		$this->emptyCheck($params,array('page'));
		$page = ((int)$params['page'])<=0?1:((int)$params['page']);
		$offset = 10;
		$start = ($page-1)*$offset;
		$db=M('coin',$this->admin_arr['pre_table']);
		$where = array();
		if(!empty($params['stime'])){
			$stime = $params['stime']." 00:00:00";
			$where['createtime'] = array('egt',$stime);
		}
		if(!empty($params['etime'])){
			$etime = $params['etime']." 23:59:59";
			$where['createtime'] = array('elt',$etime);
		}
		if(!empty($params['keyword'])){
			$keyword = $params['keyword'];
			$where["_complex"] = array("openid"=>array('like',"%$keyword%"),"nickname"=>array('like',"%$keyword%"),'_logic'=>'or');
		}
		$arr=$db->where($where)->order("id desc")->limit($start,$offset)->select();
		$count =$db->where($where)->count();
		$allpage = ceil($count/$offset);
		$msg = array('code'=>200,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	//getUserOne
	public function getUserOne(){
		$params = $this->params;
		$this->emptyCheck($params,array('userid'));
		if(empty($params['userid'])){
			$msg = array('code'=>11,'msg'=>'参数错误,请重新提交');
			returnjson($msg,$this->returnstyle,$this->callback);exit();
		}
		$db=M('coin',$this->admin_arr['pre_table']);
		$arr1=$db->where(array('id'=>(int)$params['userid']))->find();
		$arr1 = empty($arr1)?((object)array()):$arr1;
		$msg = array('code'=>200,'data'=>$arr1);
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	//getYcoinChangeList
	public function getYcoinChangeList(){
		$params = $this->params;
		$this->emptyCheck($params,array('page','userid'));
		$page = ((int)$params['page'])<=0?1:((int)$params['page']);
		$offset = 10;
		$start = ($page-1)*$offset;
		$db=M('coin_changelog',$this->admin_arr['pre_table']);
		$where = array('userid'=>$params['userid']);
		if(!empty($params['stime'])){
			$stime = $params['stime']." 00:00:00";
			$where['createtime'] = array('egt',$stime);
		}
		if(!empty($params['etime'])){
			$etime = $params['etime']." 23:59:59";
			$where['createtime'] = array('elt',$etime);
		}
		if(!empty($params['mark'])){
			$where["mark"] = $params['mark'];
		}
		$arr=$db->where($where)->order("id desc")->limit($start,$offset)->select();
		$count =$db->where($where)->count();
		$allpage = ceil($count/$offset);
		$msg = array('code'=>200,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	//addYcoinRecord
	public function addYcoinRecord(){
		$params = $this->params;
		$this->emptyCheck($params,array('remarks','userid'));//add_ycion,reduce_ycoin
		$db=M('coin_changelog',$this->admin_arr['pre_table']);
		$db1=M('coin',$this->admin_arr['pre_table']);
		$userInfo=$db1->where(array('id'=>(int)$params['userid']))->find();
		if(empty($userInfo)){
			$msg['code']=11; $msg['msg']="user not find!";
			returnjson($msg,$this->returnstyle,$this->callback);exit();
		}
		if(!empty($params['add_ycion'])){
			$changelog = (int)$params['add_ycion'];
			$changelog = $changelog<0 ?($changelog*-1):$changelog;
			$inArr = array('userid'=>$userInfo['id'],'title'=>"manager",'coin_change'=>$changelog,'remarks'=>$params['remarks'],'mark'=>'manager','openid'=>$userInfo['openid']);
			$lastid = $db->add($inArr);
			$db1->where(array('id'=>$userInfo['id']))->setInc('ycoin',$changelog);
		}
		if(!empty($params['reduce_ycion'])){
			$changelog = (int)$params['reduce_ycion'];
			$changelog = $changelog>0 ?($changelog*-1):$changelog;
			$inArr = array('userid'=>$userInfo['id'],'title'=>"manager",'coin_change'=>$changelog,'remarks'=>$params['remarks'],'mark'=>'manager','openid'=>$userInfo['openid']);
			$lastid = $db->add($inArr);
			$changelog = abs($changelog);
			$db1->where(array('id'=>$userInfo['id']))->setDec('ycoin',$changelog);
		}

		//处理标签
        $msg['code']=200;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

}

?>
