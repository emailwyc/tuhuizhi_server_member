<?php
/**
 * Created by EditPlus.
 * User: zhanghang
 * Date: 4/6/17
 * Time: 17:13 PM
 */

namespace Integral\Controller;
use Think\Controller;
use Common\Controller\CommonController;

class YcoinshopController extends CommonController
{

	public $admin_arr;
	public $key_admin;

	public function _initialize(){
		parent::__initialize();
		$key_admin=I('key_admin');
		if(empty($key_admin)){
			echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
		}else{
			$admin_db=M('admin','total_');
			$admin_arr=$admin_db->where(array('ukey'=>$key_admin))->find();
			if(empty($admin_arr)){
				echo returnjson(array('code'=>1001),$this->returnstyle,$this->callback);exit();
			}else{
				$this->admin_arr=$admin_arr;
				$this->key_admin=$key_admin;
			}
		}
	}


	/**
	 * 查询Y币下所有券
	 * @param $key_admin $type_id
     * @return mixed
	 */
	public function curl_api(){
				$integral_db=M('activity','coin_');//查询活动ID
				$integral_arr=$integral_db->where(array('admin_id'=>$this->admin_arr['id']))->find();
				if(empty($integral_arr)){
					$msg=array('code'=>307,'data'=>'暂无活动，敬请期待...');
				}else{
// 					$url=http("http://182.92.31.114/rest/act/status/".$integral_arr['activity'],array());
// 					if($url==1){
    				    $act_arr=explode(',', $integral_arr['activity']);
    				    if(count($act_arr)>1){
    				        $i=0;
    				        foreach($act_arr as $k=>$v){
    				            $url="http://182.92.31.114/rest/act/levels/".$v;//活动下所有券
    				            $res[$i]=json_decode(http($url),true);//处理返回结果
        				        if($res[$i]!= ''){
        				            if($i==1){
        				                $arr=array_merge($res[$i],$res[0]);
        				            }else if($i>1){
        				                $arr=array_merge($res[$i],$arr);
        				            }
        				            $i++;
    				            }
    				        }
    				    }else{
    				        $url="http://182.92.31.114/rest/act/levels/".$integral_arr['activity'];//活动下所有券
    				        $arr=json_decode(http($url),true);//处理返回结果
    				    }
						//处理数据，返回实用数据
						foreach($arr as $k=>$v){
							if($v['num']>$v['issue']){
								$api_arr[$v['pid']]['main']=$v['main'];
								$api_arr[$v['pid']]['extend']=$v['extend'];
								$api_arr[$v['pid']]['imgUrl']=$v['imgUrl'];
								$api_arr[$v['pid']]['num']=$v['num'];
								$api_arr[$v['pid']]['issue']=$v['issue'];
								$api_arr[$v['pid']]['startTime']=$v['startTime'];
								$api_arr[$v['pid']]['endTime']=$v['endTime'];
								$api_pid[]=$v['pid'];
							}
						}
						$integral_pro_db=M('property','coin_');
						$pro_arr=$integral_pro_db->where('admin_id='.$this->admin_arr['id'])->order('des')->select();
						foreach($pro_arr as $k=>$v){
							if(in_array($v['pid'],$api_pid) && $v['integral']>=0){
								$res_api[]=array_merge($api_arr[$v['pid']],$v);
							}
						}
						$msg=array('code'=>200,'data'=>$res_api);
// 					}else{
// 						$msg=array('code'=>307);
// 					}
				}
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	/**
	 * 查询某个奖品的详细信息
	 * @param $key_admin $pid
     * @return mixed
	 */
	public function curl_api_once(){
		$act_id=I('pid');//券ID
		if(empty($act_id)){
			$msg=array('code'=>1030);
		}else{
			$url="http://182.92.31.114/rest/act/level/".$act_id;
			$once_arr=json_decode(http($url,array()),true);
			if(!is_array($once_arr)){
				$msg=array('code'=>317);
			}else{
				$once_arr_db=M('property','coin_');
				$res=$once_arr_db->where('pid='.$once_arr['pid'].' and admin_id='.$this->admin_arr['id'])->find();
				if($once_arr['num']>$once_arr['issue']){
					$data['main']=$once_arr['main'];
					$data['extend']=$once_arr['extend'];
					$data['imgUrl']=$once_arr['imgUrl'];
					$data['num']=$once_arr['num'];
					$data['issue']=$once_arr['issue'];
					$data['desc']=$once_arr['desc'];
					$data=array_merge($data,$res);
					$msg=array('code'=>200,'data'=>$data);
				}else{
					$msg=array('code'=>102);
				}
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 查询用户积分
	 * @param $key_admin $openid
     * @return mixed
	 */
	public function integral_admin(){
	        //判断openid
			$openid=I('openid');
			if(empty($openid)){
				$msg=array('code'=>1030);
			}else{
				//判断用户是否登录
				$db=M('coin',$this->admin_arr['pre_table']);
				$arr=$db->where(array('openid'=>array('eq',$openid)))->find();
				if($arr){
				    $msg['code']=200;
				    $msg['data']=$arr;
				}else{
				    $msg['code']=102;
				}
			}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	
	/**
	 * 扣除用户积分
	 * @param $key_admin $pid $main $openid
     * @return mixed
	 */
	public function integral_delete(){
		$pid=I('pid');
		$main=I('main');
		$openid=I('openid');
		if(empty($pid) || empty($main) || empty($openid)){
			$msg=array('code'=>1030);
		}else{
				//查询用户
    		    $db=M('coin',$this->admin_arr['pre_table']);
    		    $user_arr=$db->where(array('openid'=>array('eq',$openid)))->find();
				if($user_arr==2000){
					echo returnjson(array('code'=>2000,'data'=>$user_arr),$this->returnstyle,$this->callback);exit();
				}else{
					//判断奖品数据是否纯在
					$once_arr_db=M('property','coin_');
					$once_arr=$once_arr_db->where('pid='.$pid.' and admin_id='.$this->admin_arr['id'])->find();//查询奖品数据
					if($once_arr){
					    $default_db=M('default',$this->admin_arr['pre_table']);
					    $default_arr=$default_db->where(array('customer_name'=>array('eq','integral_status')))->find();
					    if($default_arr['function_name']==1){
					        $par['default_return_description']="请到".$default_arr['description']."兑换";
					        writeOperationLog($par,'zhanghang');
					        $msg['code']=1033;
					        $msg['data']="请到".$default_arr['description']."兑换";
					        echo returnjson($msg,$this->returnstyle,$this->callback);die;
					    }
						//判断用户积分是否充足
						if($user_arr['ycoin']<(int)$once_arr['integral']){
							$msg=array('code'=>319);
						}else{
							//扣除用户积分积分
							if($once_arr['integral']==0){
							    $res['code']=200;
							}else{
                                $res=$this->del_integral($this->key_admin,$this->admin_arr['signkey'],$openid,$once_arr['integral'],$main);							    
							}
							if($res['code']==200){
								$activity_db=M('activity','coin_');
								$activity_arr=$activity_db->where(array('admin_id'=>$this->admin_arr['id']))->find();
								if(!empty($activity_arr)){
									$url2='http://101.201.176.54/rest/act/prize/'.$activity_arr['activity'].'/'.$pid.'/'.$openid;//领券接口
									$act_arr=http($url2,array());
									$act_res=json_decode($act_arr,true);
									$par['integral_delete_return']=$act_res;
									writeOperationLog($par,'zhanghang');
									if($act_res['code']==0){
										//领券成功写入日志
										$this->log_integral($activity_arr['activity'],$once_arr['integral'],$main,'F',$this->admin_arr['pre_table'],'',$openid,$pid);
										$msg=array('code'=>200,'data'=>'领奖成功');
									}else{
									    if($once_arr['integral']==0){
									        $return_integral['code']=200;
									    }else{
									        //领券失败返回积分
									        $return_integral=$this->add_integral($this->key_admin,$this->admin_arr['signkey'],$once_arr['integral'],$openid,$main,$res['data']['scorecode']);
									    }
										//写入日志
										$return_log_id=$this->log_integral($activity_arr['activity'],$once_arr['integral'],$main,'M',$this->admin_arr['pre_table'],'',$openid,$pid);
										if($return_integral['code']==200){
											//恢复积分成功修改日志状态
											$this->log_integral($activity_arr['activity'],$once_arr['integral'],$main,'A',$this->admin_arr['pre_table'],$return_log_id);
											$msg=array('code'=>1082,'msg'=>$act_res['message']);
										}else{
											$msg=array('code'=>304);
										}
									}
								}else{
									$msg=array('code'=>307);
								}
							}else{
							    $msg=array('code'=>$res['code']);
							}
						}
					}else{
						$msg=array('code'=>307);
					}
				}
		}
		$par['integral_delete_return']='pid:'.$pid.',main:'.$main.',openid:'.$openid;
		writeOperationLog($par,'zhanghang');
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	//恢复积分
	public function add_integral($key_admins,$admin_arrs,$scorenumber,$cardno,$main,$scorecode){
		$url4=C('DOMAIN').'/ClientApi/Inside/ycoinChangeLog';//恢复积分接口
		$res['data']['key_admin']=$key_admins;
		$res['data']['sign_key']=$admin_arrs;
		$res['data']['openid']=$cardno;
		$res['data']['coin_change']=$scorenumber;
		$res['data']['title']='兑换失败返还Y币';
		$res['data']['mark']='ycoinreturn';
		$res['data']['remarks']='兑换'.$main.'失败,返回Y币';
		$res['data']['sign']=sign($res['data']);
		unset($res['data']['sign_key']);
		$add_integral_arr=http($url4,$res['data']);
		$return_integral=json_decode($add_integral_arr,true);
		return $return_integral;
	}


	//扣除积分
	public function del_integral($key_admins,$admin_arrs,$cardno,$once_arr,$main){
		$param['key_admin']=$key_admins;
		$param['sign_key']=$admin_arrs;
		$param['openid']=$cardno;
		$param['coin_change']=$once_arr*-1;
		$param['title']='积分兑换商品';
		$param['mark']='coinshop';
		$param['remarks']='兑换'.$main;
		$param['sign']=sign($param);
		unset($param['sign_key']);
		$url=C('DOMAIN').'/ClientApi/Inside/ycoinChangeLog';//扣除积分接口
		$curl_res=http($url,$param);
		$res=json_decode($curl_res,true);
		return $res;
	}



	//添加日志
	public function log_integral($activity_id,$integral,$main,$re,$pre_table,$id=null,$openid='',$pid=''){
		$log_integral_db=M('coin_log',$pre_table);
		$data['integral']=$integral;
		$data['description']="兑换".$main;
		$data['activity_id']=$activity_id;
		if($re=='F'){
			$data['starttime']=date('Y-m-d H:i:s');
			$data['status']=1;
			$data['openid']=$openid;
			$data['pid']=$pid;
			$data['prize_name']=$main;
			$log_integral_db->add($data);
		}else if($re=='M'){
			$data['starttime']=date('Y-m-d H:i:s');
			$data['status']=2;
			$data['prize_name']=$main;
			$data['openid']=$openid;
			$data['pid']=$pid;
			$res=$log_integral_db->add($data);
			return $res;
		}else if($re=='A'){
			$upda['status']=3;
			$map['id']=$id;
			$upda['prize_name']=$main;
			$log_integral_db->where($map)->save($upda);
		}
	}
	
	/**
	 * 判断活动id状态
	 * @param $key_admin $activity_id
	 * @return mixed
	 */
	public function activity_id_status(){
	    $params['activity_id']=I('activity_id');
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	        $url="http://182.92.31.114/rest/act/status/".$params['activity_id'];//活动下所有券
	        $arr=json_decode(http($url),true);//处理返回结果
	        $msg['code']=200;
	        $msg['data']=$arr;
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	

	//查看用户是否登录
	public function getUserCardByOpenId($prefix, $openid) {

			$user = M('mem', $prefix);
			$re = $user->where(array('openid' => $openid))->find();
			
			//return $re['cookie'].','.$_COOKIE[$prefix.'ck'];exit();
			
			if (!$re) {
				return '2000';exit();
			} else {
				//if ($re['cookie'] != $_COOKIE[$prefix.'ck']) {
					//return '2000';exit();
				//}
				return $re;exit();
			}

		
	}
	
	
	
	/**
	 * 获取banner接口
	 */
	public function banner_list(){
	    $banner_db=M('banner','coin_');
	    $map['admin_id']=array('eq',$this->admin_arr['id']);
	    $res=$banner_db->where($map)->order('sort asc')->limit(5)->select();
	    if($res){
	        $msg['code']=200;
	        $msg['data']=$res;
	    }else{
	        $msg['code']=102;
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
}
