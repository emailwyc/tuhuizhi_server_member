<?php
/**
 *员工评价系统
 */
namespace MerAdmin\Controller;
// use Common\Controller\CommonController;
class EvaluateController extends AuthController
{
    public $key_admin;
    public $admin_arr;
    public function _initialize(){
        parent::_initialize();
		//查询商户信息
        $this->key_admin=$this->ukey;
        
    }
    
    //获取评价所有评价分类
    public function getClassAll(){
        $db=M('evaluate_class',$this->admin_arr['pre_table']);
		$arr=$db->select();
        if($arr){
            $msg['code']=200; $msg['data']=$arr;
		}else{
			$msg = array('code'=>102,'data'=>$arr);
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

    //获取评价评价分类列表(暂无分页最多40条)
	public function getClassList(){
		//获取列表
		$db=M('evaluate_class',$this->admin_arr['pre_table']);
		$classT = $this->admin_arr['pre_table']."evaluate_class";
		$sfrelT = $this->admin_arr['pre_table']."evaluate_sfrel";
		$field = "id,name";
		$arr=$db->field($field)->order("$classT.id asc")->limit(0,40)->select();
		if(!$arr){
			$msg = array('code'=>102,'data'=>$arr);
			returnjson($msg,$this->returnstyle,$this->callback);exit();
		}

		$allid = ArrKeyAll($arr,'id',0);
		$arr = ArrKeyFromId($arr,'id');

		$db3=M('evaluate_sfrel',$this->admin_arr['pre_table']);
		$arr3=$db3->field("class_id,count(id) as count")->where(array("class_id"=>array('IN',$allid)))->group("class_id")->select();
		$arr3 = ArrKeyFromId($arr3,'class_id');

		foreach($arr as $k=>$v){
			$arr[$k]['count'] = isset($arr3[$v['id']]['count'])?$arr3[$v['id']]['count']:0;
			$arr[$k]['tags'] = array();
		}
		//获取所有评价标签
		$db1=M('evaluate_tags',$this->admin_arr['pre_table']);
		$relaT = $this->admin_arr['pre_table']."evaluate_relation";
		$tagsT = $this->admin_arr['pre_table']."evaluate_tags";
		$field1 = "$tagsT.star,$tagsT.name,$relaT.class_id";
		$arr1=$db1->field($field1)->where(array("$relaT.class_id"=>array('IN',$allid)))->join(" join ".$relaT." on ".$tagsT.".id=".$relaT.".tags_id")->select();
		foreach($arr1 as $k=>$v){
			$arr[$v['class_id']]['tags'][] = $v;
		}
		//标签处理完毕返回数据
        if($arr){
            $msg['code']=200; $msg['data']=$arr;
		}else{
			$msg = array('code'=>102,'data'=>$arr);
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	//获取单条评价分类
	public function getClassOne(){
		//获取单条
		$params = $this->params;
		$this->emptyCheck($params,array('class_id'));
		$db=M('evaluate_class',$this->admin_arr['pre_table']);
		$arr=$db->where(array('id'=>$params['class_id']))->find();
		if(!$arr){
			$msg = array('code'=>102,'data'=>$arr);
			returnjson($msg,$this->returnstyle,$this->callback);exit();
		}
		$arr['tags'] = array();
		//获取所有评价标签
		$db1=M('evaluate_tags',$this->admin_arr['pre_table']);
		$relaT = $this->admin_arr['pre_table']."evaluate_relation";
		$tagsT = $this->admin_arr['pre_table']."evaluate_tags";
		$allid = ArrKeyAll($arr,'id',0);
		$field1 = "$tagsT.star,$tagsT.name,$relaT.class_id,$relaT.tags_id";
		$arr1=$db1->field($field1)->where(array("$relaT.class_id"=>$arr['id']))->join("left join ".$relaT." on ".$tagsT.".id=".$relaT.".tags_id")->select();
		foreach($arr1 as $k=>$v){
			$arr['tags'][$v['tags_id']] = $v;
		}
		$arr['tags'] = empty($arr1)?((object)array()):$arr['tags'];
		//标签处理完毕返回数据
        if($arr){
            $msg['code']=200; $msg['data']=$arr;
		}else{
			$msg = array('code'=>102,'data'=>$arr);
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	//添加或者编辑评价分类
	public function editClassOne(){
		//获取单条class_id;
		$params = $this->params;
		$this->emptyCheck($params,array('name','tags'));
		$db=M('evaluate_class',$this->admin_arr['pre_table']);
		if(empty($params['class_id'])){
			//添加
			$insert = array('name'=>$params['name']);
			$lastid = $db->add($insert);
		}else{
			//编辑
			$db->where(array('id'=>$params['class_id']))->save(array('name'=>$params['name']));
			$lastid = $params['class_id'];
		}
		//处理标签
		$relaT=M('evaluate_relation',$this->admin_arr['pre_table']);
		$relaT->where(array('class_id'=>$lastid))->delete();
		if(!empty($params['tags'])&&is_array($params['tags'])){
			//删除已有标签
			$addArr = array();
			foreach($params['tags'] as $k=>$v){
				$addArr[] = array('class_id'=>$lastid,'tags_id'=>$v);
			}
			$relaT->addAll($addArr);
			//添加新标签
		
		}
        $msg['code']=200; $msg['data']=$arr;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	//删除评价分类（删除评价分类将会删除对应标签和关联员工）
	public function delClassOne(){
		$params = $this->params;
		$this->emptyCheck($params,array('class_id'));
		$db=M('evaluate_class',$this->admin_arr['pre_table']);
		$relaT=M('evaluate_relation',$this->admin_arr['pre_table']);
		$sfreT=M('evaluate_sfrel',$this->admin_arr['pre_table']);
		if(!empty($params['class_id'])){
			//删除
			$db->where(array('id'=>$params['class_id']))->delete();
			//删除评价分类标签
			$relaT->where(array('class_id'=>$params['class_id']))->delete();
			//解除评价分类对应员工关系
			$sfreT->where(array('class_id'=>$params['class_id']))->delete();
		}
        $msg['code']=200; $msg['data']=$arr;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	//获取所有评价标签
    public function getTagsAll(){
        $db=M('evaluate_tags',$this->admin_arr['pre_table']);
		$arr=$db->field('id,name,star')->order('`order` asc')->select();
        if($arr){
            $msg['code']=200; $msg['data']=$arr;
		}else{
			$msg = array('code'=>102,'data'=>$arr);
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	//获取评价标签列表
	public function getTagsList(){
		$params = $this->params;
		$this->emptyCheck($params,array('page'));
		$page = ((int)$params['page'])<=0?1:((int)$params['page']);
		$offset = 10;
		$start = ($page-1)*$offset;
		$db=M('evaluate_tags',$this->admin_arr['pre_table']);
		$tagsT = $this->admin_arr['pre_table']."evaluate_tags";
		$classT = $this->admin_arr['pre_table']."evaluate_class";
		$relaT = $this->admin_arr['pre_table']."evaluate_relation";
		$field = "$tagsT.id,$tagsT.name,$tagsT.star,$tagsT.order";
		$where = array();
		if(!empty($params['star'])){
			if((int)$params['star']>=4){
				$where = array("$tagsT.star"=>array('EGT',4));
			}else{
				$where = array("$tagsT.star"=>array('LT',4));
			}
		}

		if(!empty($params['class_id'])){
			$where["$relaT.class_id"] = $params['class_id'];
		}
		$arr=$db->field($field)->join("left join $relaT on $relaT.tags_id=$tagsT.id")->where($where)->group("$tagsT.id")->order("$tagsT.`order` asc")->limit($start,$offset)->select();
		$arr = ArrKeyFromId($arr,'id');
		foreach($arr as $k=>$v){
			$arr[$k]['class_name'] = array();
		}
		//$count = "select count(distinct($tagsT.id)) from $tagsT left join $relaT on $relaT.tags_id=$tagsT.id where"
		$count =$db->field("count(distinct($tagsT.id)) as count")->join("left join $relaT on $relaT.tags_id=$tagsT.id")->where($where)->select();
		$count = @(int)$count[0]['count'];
		//获取对应class_name
		if($arr){
			$db1=M('evaluate_class',$this->admin_arr['pre_table']);
			$allid = ArrKeyAll($arr,'id',0);
			if($allid){
				$where = array("$relaT.tags_id"=>array('in',$allid));
				$arr1=$db1->field("$relaT.tags_id,$classT.name")->join("$relaT on $relaT.class_id=$classT.id")->where($where)->select();
				foreach($arr1 as $k=>$v){
					if(isset($arr[$v['tags_id']]['class_name'])){
						$arr[$v['tags_id']]['class_name'][] = $v['name'];
					}
				}
			}
		}

		//标签处理完毕返回数据
		$arr = ArrObjChangeList($arr);
		if($arr){
			$allpage = ceil($count/$offset);
			$msg = array('code'=>200,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
		}else{
			$allpage = ceil($count/$offset);
			$msg = array('code'=>102,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	//获取单条标签
	public function getTagsOne(){
		$params = $this->params;
		$this->emptyCheck($params,array('tags_id'));
		if(empty($params['tags_id'])){
			$msg = array('code'=>11,'msg'=>'参数错误,请重新提交');
			returnjson($msg,$this->returnstyle,$this->callback);exit();
		}
		$tagsT = $this->admin_arr['pre_table']."evaluate_tags";
		$classT = $this->admin_arr['pre_table']."evaluate_class";
		$relaT = $this->admin_arr['pre_table']."evaluate_relation";
		$db=M('evaluate_tags',$this->admin_arr['pre_table']);
		$arr=$db->where(array('id'=>$params['tags_id']))->find();
		if(empty($arr)){
			$msg = array('code'=>12,'msg'=>'未找到相应数据');
			returnjson($msg,$this->returnstyle,$this->callback);exit();
		}

		$db=M('evaluate_class',$this->admin_arr['pre_table']);
		$field = "$classT.id,$classT.name";
		$arr1=$db->field($field)->join("left join ".$relaT." on ".$classT.".id=".$relaT.".class_id")->where(array("$relaT.tags_id"=>$params['tags_id']))->select();
		$arr1 = ArrKeyFromId($arr1,'id');
		$arr1 = empty($arr1)?((object)array()):$arr1;
		$arr['class'] = $arr1;
		//标签处理完毕返回数据
		if($arr){
			$msg = array('code'=>200,'data'=>$arr);
		}else{
			$msg = array('code'=>102,'data'=>$arr);
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	//添加或者编辑评价标签
	public function editTagsOne(){
		//获取单条class_id;
		$params = $this->params;
		$this->emptyCheck($params,array('name','star','order','class'));
		$db=M('evaluate_tags',$this->admin_arr['pre_table']);
		$inArr = array('name'=>$params['name'],'star'=>(int)$params['star'],'order'=>(int)$params['order']);
		if(empty($params['tags_id'])){
			//添加
			$lastid = $db->add($inArr);
		}else{
			//编辑
			$db->where(array('id'=>$params['tags_id']))->save($inArr);
			$lastid = $params['tags_id'];
		}
		//处理标签
		$relaT=M('evaluate_relation',$this->admin_arr['pre_table']);
		$relaT->where(array('tags_id'=>$lastid))->delete();
		if(!empty($params['class'])&&is_array($params['class'])){
			//删除已有标签
			$addArr = array();
			foreach($params['class'] as $k=>$v){
				$addArr[] = array('class_id'=>$v,'tags_id'=>$lastid);
			}
			$relaT->addAll($addArr);
			//添加新标签
		
		}
        $msg['code']=200; $msg['data']=$arr;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	//删除评价标签(删除后对应评价分类中将会失去该标签)
	public function delTagsOne(){
		$params = $this->params;
		$this->emptyCheck($params,array('tags_id'));
		$db=M('evaluate_tags',$this->admin_arr['pre_table']);
		$relaT=M('evaluate_relation',$this->admin_arr['pre_table']);
		$sfreT=M('evaluate_sfrel',$this->admin_arr['pre_table']);
		if(!empty($params['tags_id'])){
			//删除
			$db->where(array('id'=>$params['tags_id']))->delete();
			//删除评价分类标签
			$relaT->where(array('tags_id'=>$params['tags_id']))->delete();
		}
        $msg['code']=200; $msg['data']=$arr;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	//获取员工列表
	public function getStaffList(){
		$params = $this->params;
		$this->emptyCheck($params,array('page'));
		$page = ((int)$params['page'])<=0?1:((int)$params['page']);
		$offset = 10;
		$start = ($page-1)*$offset;

		$db=M('evaluate_staff',$this->admin_arr['pre_table']);
		$staffT = $this->admin_arr['pre_table']."evaluate_staff";
		$classT = $this->admin_arr['pre_table']."evaluate_class";
		$sfrelT = $this->admin_arr['pre_table']."evaluate_sfrel";
		$field = "$staffT.id,$staffT.name,$staffT.number,$staffT.mobile,$staffT.avatar,$staffT.qrcode,$staffT.comment,$staffT.createtime";
		$where = array();
		if(!empty($params['class_id'])){
			$where["$sfrelT.class_id"] = (int)$params['class_id'];
		}
		if(!empty($params['keyword'])){
			$keyword = $params['keyword'];
			$where["_complex"] = array("$staffT.name"=>array('like',"%$keyword%"),"$staffT.number"=>array('like',"%$keyword%"),'_logic'=>'or');
		}
		$arr=$db->field($field)->join("left join $sfrelT on $sfrelT.staff_id=$staffT.id")->where($where)->group("$staffT.id")->order("$staffT.`id` asc")->limit($start,$offset)->select();
		foreach($arr as $k=>$v){
			$arr[$k]['comment'] = json_decode($v['comment'],true);
		}
		$count =$db->field("$staffT.id,count(distinct($staffT.id)) as count")->join("left join $sfrelT on $sfrelT.staff_id=$staffT.id")->where($where)->select();
		$count = @(int)$count[0]['count'];
		//标签处理完毕返回数据
		if($count>0){
			$allpage = ceil($count/$offset);
			$msg = array('code'=>200,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
		}else{
			$allpage = ceil($count/$offset);
			$msg = array('code'=>102,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	//获取单条员工
	public function getStaffOne(){
		$params = $this->params;
		$this->emptyCheck($params,array('staff_id'));
		if(empty($params['staff_id'])){
			$msg = array('code'=>11,'msg'=>'参数错误,请重新提交');
			returnjson($msg,$this->returnstyle,$this->callback);exit();
		}
		$db=M('evaluate_staff',$this->admin_arr['pre_table']);
		$arr=$db->where(array('id'=>$params['staff_id']))->find();
		if(empty($arr)){
			$msg = array('code'=>12,'msg'=>'未找到相应数据');
			returnjson($msg,$this->returnstyle,$this->callback);exit();
		}
		$arr['comment'] = json_decode($arr['comment'],true);
		//员工分类
		$sfrelT = $this->admin_arr['pre_table']."evaluate_sfrel";
		$classT = $this->admin_arr['pre_table']."evaluate_class";
		$db1=M('evaluate_class',$this->admin_arr['pre_table']);
		$field = "$classT.id,$classT.name";
		
		$arr1=$db1->field($field)->join("left join ".$sfrelT." on ".$classT.".id=".$sfrelT.".class_id")->where(array("$sfrelT.staff_id"=>$params['staff_id']))->select();
		$arr1 = ArrKeyFromId($arr1,'id');
		$arr1 = empty($arr1)?((object)array()):$arr1;
		$arr['class'] = $arr1;
		//标签处理完毕返回数据
		if($arr){
			$msg = array('code'=>200,'data'=>$arr);
		}else{
			$msg = array('code'=>102,'data'=>$arr);
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	//编辑或者添加员工
	public function editStaffOne(){
		$params = $this->params;
		$this->emptyCheck($params,array('name','number','mobile','avatar','qrcode','class'));
		$db=M('evaluate_staff',$this->admin_arr['pre_table']);
		$inArr = array('name'=>$params['name'],'number'=>$params['number'],'mobile'=>$params['mobile'],'avatar'=>$params['avatar'],'qrcode'=>$params['qrcode']);
		if(empty($params['staff_id'])){
			//检查
			$check = $db->where(array('number'=>$params['number']))->find();
			if($check){
				$msg = array('code'=>13,'msg'=>'员工工号已经存在');
				returnjson($msg,$this->returnstyle,$this->callback);exit();
			}
			//添加
			$inArr['comment'] = json_encode(array('1'=>0,'2'=>0,'3'=>0,'4'=>0,'5'=>0,'all'=>0));
			$lastid = $db->add($inArr);
		}else{
			//检查
			//
			$check = $db->where(array('number'=>$params['number'],'id'=>array('NEQ',$params['staff_id'])))->find();
			if($check){
				$msg = array('code'=>13,'msg'=>'员工工号已经存在');
				returnjson($msg,$this->returnstyle,$this->callback);exit();
			}
			//编辑
			$db->where(array('id'=>$params['staff_id']))->save($inArr);
			$lastid = $params['staff_id'];
		}
		//处理标签
		$relaT=M('evaluate_sfrel',$this->admin_arr['pre_table']);
		$relaT->where(array('staff_id'=>$lastid))->delete();
		if(!empty($params['class'])&&is_array($params['class'])){
			//删除已有标签
			$addArr = array();
			foreach($params['class'] as $k=>$v){
				$addArr[] = array('staff_id'=>$lastid,'class_id'=>(int)$v);
			}
			$relaT->addAll($addArr);
			//添加新标签
		}
        $msg['code']=200; $msg['data']=$arr;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	//删除员工
	public function delStaffOne(){
		$params = $this->params;
		$this->emptyCheck($params,array('staff_id'));
		$db=M('evaluate_staff',$this->admin_arr['pre_table']);
		$sfreT=M('evaluate_sfrel',$this->admin_arr['pre_table']);
		if(!empty($params['staff_id'])){
			//删除
			$db->where(array('id'=>$params['staff_id']))->delete();
			//解除评价分类对应员工关系
			$sfreT->where(array('staff_id'=>$params['staff_id']))->delete();
		}
        $msg['code']=200; $msg['data']=$arr;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	public function getEvalList(){
		$params = $this->params;
		$this->emptyCheck($params,array('page','number'));
		$page = ((int)$params['page'])<=0?1:((int)$params['page']);
		$offset = 10;
		$start = ($page-1)*$offset;
		$db=M('evaluate',$this->admin_arr['pre_table']);
		$where = array('staff_num'=>$params['number']);
		if(!empty($params['startDate'])){
			$params['startDate'] = $params['startDate']." 00:00:00";
			$where['createtime'][] = array('EGT',$params['startDate']);
		}
		if(!empty($params['endDate'])){
			$params['endDate'] = $params['endDate']." 23:59:59";
			$where['createtime'][] = array('ELT',$params['endDate']);
		}
		$arr=$db->where($where)->order("`id` desc")->limit($start,$offset)->select();
		foreach($arr as $k=>$v){
			if(!empty($v['tags'])){
				$arr[$k]['tags'] = @json_decode($v['tags'],true);
			}else{
				$arr[$k]['tags'] = array();
			}
			//$arr[$k]['message'] =base64_decode($v['message']);
		}
		$count = $db->where($where)->count();
		//标签处理完毕返回数据
		if($count>0){
			$allpage = ceil($count/$offset);
			$msg = array('code'=>200,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
		}else{
			$allpage = ceil($count/$offset);
			$msg = array('code'=>102,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	//C获取员工相关信息
	//C获提交评价
}

?>
