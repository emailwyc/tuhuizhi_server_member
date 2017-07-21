<?php
namespace MerAdmin\Controller;

use Common\Controller\JaleelController;

class ShopController extends JaleelController
{
	//登录状态和权限验证接口(外部调用，注：签名的时候需要signkey)
	public function login_status(){
        $childid=I('childid');
        $params['childid']=$childid;
		$params['key_admin']=I('key_admin');
		$sign=I('sign');
		$childid = !empty($childid)?$childid:"";
		if(in_array('',$params)){
			$msg['code']=1030;
		}else{
		    $reskey = $childid=="ismaster"?"":$childid;
			//$key=$params['key_admin']."zhihuitu";
			//$key_admin=$_SESSION[$key];
			$key_admin=$this->redis->get($this->ukey.'MerAdmin'.$reskey);
			if(!$key_admin){
				$msg['code']=502;
			}else{
				$this->redis->expire($this->ukey.'MerAdmin'.$reskey,1800);
				$admin_arr=$this->getMerchant($this->ukey);
				$params['sign_key']=md5(substr($params['key_admin'],-4)."zhihuitu");
				//echo sign($params);
				if($sign!=sign($params)){
					$msg['code']=1002;
				}else{
					if(!$this->Auth_Admin($admin_arr['id'])){
						$msg['code']=5002;
					}else{
						$msg['code']=200;
						$arr['pre_table']=$admin_arr['pre_table'];
						$arr['describe']=$admin_arr['describe'];
						$arr['sign_key']=$admin_arr['signkey'];
						$build_db=M('buildid','total_');
						$build_arr=$build_db->field('name,buildid')->where(array('adminid'=>$admin_arr['id']))->select();
						$arr['build_list']=$build_arr;
                        $arr['build_child_acc']=(object)array();
                        $arr['childid'] = $childid;
                        if($childid && $childid!="ismaster"){
                            $childMerInfo = $this->getChildMerchant($childid);
                            $buildInfo = $build_db->field('name,buildid')->where(array('id'=>(int)@$childMerInfo['buildid']))->find();
                            $arr['build_child_acc']=$buildInfo;
                        }
                        $msg['data']=$arr;
						
					}
				}
			}
		}
		$write_arr['Shop_request']=$params;
		$write_arr['Shop_reshpone']=$msg;
		writeOperationLog($write_arr,'Shop');
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	//获取微信appid
	public function get_appid(){
	    $params['key_admin']=I('key_admin');
		$sign=I('sign');
		if(in_array('',$params)){
			$msg['code']=1030;
		}else{
				$this->redis->expire($this->ukey.'MerAdmin',1800);
				$admin_arr=$this->getMerchant($this->ukey);
				$params['sign_key']=$admin_arr['signkey'];
				if($sign!=sign($params)){
					$msg['code']=1002;
				}else{
				        $default_db=M('default',$admin_arr['pre_table']);
						$msg['code']=200;
						$default_arr=$default_db->where(array('customer_name'=>'subpayacc'))->field('function_name')->find();
					   
						$arr['appid']=$admin_arr['wechat_appid'];
						$arr['subpayacc']=$default_arr['function_name'];
						
						$msg['data']=$arr;
				}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	    
	}
	
	
	/**
	 * 获取会员卡等级
	 */
	public function staticpagedetails() {
	    $admin_arr=$this->getMerchant($this->ukey);
	    $db=M('member_code','total_');
	    $where['admin_id']=array('eq',$admin_arr['id']);
	    $arr=$db->where($where)->order('sort asc')->select();
	    if($arr){
	        foreach($arr as $k=>$v){
	            $page_info[$v['code']]=$v['name'];
	        }
	    }else{
	        $page_info=array();
	    }
	    $return_data = array('code' => '200', 'msg' => 'success', 'data' => $page_info);
	    returnjson($return_data, $this->returnstyle, $this->callback);
	}
	
	/**
	 * @param $tid
	 * @param $mid
	 * @param string $sid
	 * @return mixed
	 */
	protected function getstaticpage($tid, $mid, $sid = '') {
	    $static = M('total_static');
	
	    if (empty($sid)) {
	        $page_info = $static->where(array('tid' => $tid, 'admin_id' => $mid))->find();
	    } else {
	        $page_info = $static->where(array('id' => $sid, 'tid' => $tid, 'admin_id' => $mid))->find();
	    }
	    return $page_info;
	}
	
	
	/**
	 * 根据建筑物获取商铺信息
	 */
	public function poi_list(){
	    $params['buildid']=I('buildid');
	    $params['key_admin']=I('key_admin');
	    $sign=I('sign');
	    if(in_array('', $params)){
	        $msg['code']=1030;
	    }else{
	        $params['floor']=I('floor');
	        $admininfo = $this->getMerchant($params['key_admin']);
	        $params['sign_key']=$admininfo['signkey'];//echo sign($params);
	        if($sign!=sign($params)){
	            $msg['code']=1002;
	        }else{
	            $db = M( $admininfo['pre_table'].'map_poi_'.$params['buildid'] , '', 'DB_CONFIG2');
	             
	            if($params['floor']){
	                $where['floor']=array('eq',$params['floor']);
	            }
	            $where['id_build']=array('eq',$params['buildid']);
	            $where['del_status']=array('eq',1);
	            $where['_logic']='and';
	             
	            $arr=$db->where($where)->field('poi_name,floor,id_build,class_id,poi_number')->select();
	             
	            if($arr){
	                $msg['code']=200;
	                $msg['data']=$arr;
	            }else{
	                $msg['code']=102;
	            }
	        }
	    }
	    returnjson($msg, $this->returnstyle, $this->callback);
	}
}
?>