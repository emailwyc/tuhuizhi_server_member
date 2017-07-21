<?php
namespace ClientApi\Controller;
use Think\Controller;
use Common\Controller\RedisController as A;
/**
 * 评价分类
 * @date 2016-03-14
 * error:11,12,13,14,15,16,17
 */
class EvaluateController extends ClientCommonController
{
	public function _initialize(){
		parent::_initialize();
		//$params = $this->params;
	}
    
	//C获取员工相关信息
	public function getStaffInfo() {
		$params = $this->params;
		$this->emptyCheck($params,array('number','class_id'));

		//check params
		$db = M('evaluate_staff', $this->setting['pre_table']);
		$arr = $db->field("id,name,number,avatar")->where(array('number'=>$params['number']))->find();
		if(empty($arr)){ returnjson(array('code'=>11,'msg'=>'未找到相关数据'), $this->returnstyle, $this->callback);exit; }
		$cInfo = M('evaluate_class', $this->setting['pre_table'])->field("name")->where(array('id'=>$params['class_id']))->find();
		$arr['class_name'] = @!empty($cInfo)?$cInfo['name']:"服务评价";
		//获取标签
		$db1 = M('evaluate_tags', $this->setting['pre_table']);
		$tagsT = $this->setting['pre_table']."evaluate_tags";
		$sfrelT = $this->setting['pre_table']."evaluate_sfrel";
		$relaT = $this->setting['pre_table']."evaluate_relation";
		$field = "$tagsT.id,$tagsT.name,$tagsT.star";
		$arr1=$db1->field($field)->join("left join ".$relaT." on ".$tagsT.".id=".$relaT.".tags_id")
			->join("join ".$sfrelT." on ".$sfrelT.".class_id=".$relaT.".class_id")->where(array("$sfrelT.staff_id"=>$arr['id'],"$relaT.class_id"=>$params['class_id']))->order("$tagsT.order asc")->select();
		$tag = array('bad'=>array(),'good'=>array());
		foreach($arr1 as $k=>$v){
			if($v['star']>3){
				$tag['good'][]=$v;
			}else{
				$tag['bad'][] =$v;
			}
		}
		$arr['tags'] = $tag;
		$msg = !empty($arr)?array('code'=>200,'data'=>$arr):array('code'=>102);
		returnjson($msg, $this->returnstyle, $this->callback);
	}
	//C获提交评价
	public function sendEvaluate() {
		//check params
		$params = $this->params;
		$this->emptyCheck($params,array('number','star','tags','msg','nickname','avatar','openid'));
		//检查是否可以评价
		$dbEva = M('evaluate', $this->setting['pre_table']);
		$dateT = date('Y-m-d 00:00:00',time());
		$check = $dbEva->field("id")->where(array('staff_num'=>$params['number'],'openid'=>$params['openid'],'createtime'=>array('egt',$dateT)))->find();
		if($check){ returnjson(array('code'=>13,'msg'=>'您今天已经评价过了，不能重复评价!'), $this->returnstyle, $this->callback);exit;	}
		
		$db = M('evaluate_staff', $this->setting['pre_table']);
		$arr = $db->field("id,comment")->where(array('number'=>$params['number']))->find();
		if(empty($arr)){ returnjson(array('code'=>11,'msg'=>'未找到相关数据'), $this->returnstyle, $this->callback);exit; }
		//添加数据
		if($params['tags'] && is_array($params['tags'])){
			$tags = json_encode($params['tags']);
		}else{
			$tags = "";
		}
		$params['msg'] = filterEmoji($params['msg']);
		//$params['msg'] = base64_encode($params['msg']);
		$inArr = array('openid'=>$params['openid'],'nickname'=>$params['nickname'],'avatar'=>$params['avatar'],'staff_num'=>$params['number'],'star'=>$params['star'],'tags'=>$tags,'message'=>$params['msg']);
		$db1 = M('evaluate', $this->setting['pre_table']);
		$lastid = $db1->add($inArr);
		//更新员工
		$comment = json_decode($arr['comment'],true);
		if(isset($comment[(int)$params['star']])){
			$comment[$params['star']]+=1;
			$comment['all']+=1;
		}
		$commentArr = array('comment'=>json_encode($comment));
		$db->where(array('number'=>$params['number']))->save($commentArr);
		$msg = array('code'=>200);
		returnjson($msg, $this->returnstyle, $this->callback);
	}

    
}

?>
