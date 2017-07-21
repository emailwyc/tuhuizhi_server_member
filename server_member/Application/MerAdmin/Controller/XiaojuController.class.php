<?php
/**
 * 滴滴打车配置相关
 */
namespace MerAdmin\Controller;
// use Common\Controller\CommonController;
class XiaojuController extends AuthController
{
    public $key_admin;
    public function _initialize(){
        
        parent::_initialize();
        //查询商户信息
        $this->key_admin=$this->ukey;
        
    }

	//获取滴滴打车配置
    public function get_conf(){
        $db=M('admin_xiaoju','total_');
		$arr=$db->where(array('adminid'=>$this->admin_arr['id']))->find();
		if($arr){
			$arr['setting'] = json_decode($arr['setting']);
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
            $msg['data']='还没有添加配置';
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	//获取滴滴打车配置
    public function update_conf(){
		$params = I('param.');
		$this->emptyCheck(array('points','price','minscore','CLIENT_ID','CLIENT_SECRET','SIGN_KEY','TEST_CALL_PHONE','TEMPLATE'),$params);
        $db=M('admin_xiaoju','total_');
		$arr=$db->where(array('adminid'=>$this->admin_arr['id']))->find();
		//整合数据
		$setting = json_encode(array('CLIENT_ID'=>$params['CLIENT_ID'],'CLIENT_SECRET'=>$params['CLIENT_SECRET'],'SIGN_KEY'=>$params['SIGN_KEY'],'TEST_CALL_PHONE'=>$params['TEST_CALL_PHONE'],'TEMPLATE'=>$params['TEMPLATE']));
		$edit_arr = array(
			'points'  =>$params['points'],
			'price'   =>$params['price'],
			'minscore'=>$params['minscore'],
			'setting' =>$setting
		);
		if($arr){
			//更新
			$db->where(array('adminid'=>$this->admin_arr['id']))->save($edit_arr);
		}else{
			//添加
			$edit_arr['adminid'] = $this->admin_arr['id'];
			$db->add($edit_arr);
        }
		returnjson(array('code'=>200,'msg'=>"操作成功"),$this->returnstyle,$this->callback);exit();
    }

	//获取滴滴订单列表
    public function getorderlist(){
		$params = I('param.');
		$this->emptyCheck(array('page'),$params);
        $db=M('carorder',$this->admin_arr['pre_table']);
        $count=$db->count();
		$pageSize = 10;
		$params['page'] = (int)$params['page']<=0?1:(int)$params['page'];
		$countPage = @(int)ceil($count / $pageSize);//获得总页数
		$start = ($params['page'] - 1) * $pageSize;//开始条数
		$data=$db->limit($start, $pageSize)->order('id desc')->select();
        returnjson(array('code'=>200,'data'=>array('data'=>$data,'pagenum'=>$countPage,'count'=>(int)$count)), $this->returnstyle, $this->callback);
	}

	//获取滴滴打车活动规则
    public function getActRule(){
        $db=M('admin_xiaoju','total_');
		$arr=$db->where(array('adminid'=>$this->admin_arr['id']))->find();
		$params = I('param.');
		$this->emptyCheck(array('page'),$params);
		$db=M('carorder',$this->admin_arr['pre_table']);

        returnjson(array('code'=>200,'data'=>array('data'=>$data,'pagenum'=>$countPage,'count'=>(int)$count)), $this->returnstyle, $this->callback);
	}

	protected function emptyCheck($key_arr,$params) {
		$params = !empty($params)?$params:$this->params;
		$new_params = array();
		foreach($key_arr as $v){
			if(!isset($params[$v])){
				$msg['code']=1051;
				echo returnjson($msg,$this->returnstyle,$this->callback);exit;
			}else{
				$new_params[$v] = $params[$v];
			}
		}   
		return $new_params;
	}

}

?>
