<?php
/**
 * Created by EditPlus.
 * User: zhanghang
 * Date: 6/24/16
 * Time: 10:10 AM
 */

namespace MerAdmin\Controller;
use common\ServiceLocator;
use MerAdmin\Model\ActivityModel;

class ScoremallController extends AuthController
{
	
	public $key_admin;
	public $admin_arr;
	public $status;
	
	public function _initialize(){
		parent::_initialize();
		//查询商户信息
		$this->admin_arr=$this->getMerchant($this->ukey);
		$this->key_admin=$this->ukey;
		
        $this->status=array(
            '0'=>'未开放',
            '1'=>'已开放',
            '2'=>'已领取',
            '3'=>'已核销',
            '4'=>'已撤销',
            '5'=>'已过期',
            '6'=>'转增中',
            '7'=>'核销中',
            '8'=>'退款中',
            '9'=>'已退款'
        );
	}

	//应用平台

	//积分商城

	/**
	 * 判断活动id状态
	 * @param $key_admin $type_id
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
	
	/**
	 * 查询所有奖品信息
	 * @param $key_admin $type_id
     * @return mixed
	 */
	public function integral_list(){
	    
	    $params['buildid']=I('buildid')?I('buildid'):"";
	    $params['type']=I('type')?I('type'):'ZHT_YX';
	    $params['status']=I('status') != null?I('status'):'';
		//查询活动ID
	        $activityService = ServiceLocator::getActivityService();
	        $integral_arr = $activityService->getOnce($this->admin_arr['id'], $params['type'], $params['buildid'], 0);
	    
			if(empty($integral_arr)){

				$msg=array('code'=>307,'data'=>'暂无奖品，敬请期待...');

			}else{ 
			    $integral_pro_db=M('property','integral_');
				//调用奖品信息
				if($params['type']=='ZHT_YX'){
    			    $act_arr=explode(',', $integral_arr['activity']);
    				if(count($act_arr)>1){
    				    $i=0;
    				    foreach($act_arr as $k=>$v){
    				        $url="http://101.201.175.219/promo/prize/ka/prize/list/".$v;//活动下所有券
    				        $return=json_decode(http($url,array()),true);//处理返回结果
    				        $res[$i]=$return['data'];
    				        
        				    foreach($res[$i] as $k=>$val){
        				        $delete_arr[]=$val['id'];
                                $res[$i][$k]['activity']=$v;
                                $res[$i][$k]['activity_type']='ZHT_YX';
                            }
                            $i++;
    				    }
    				    
    				    foreach($res as $key=>$l){
    				        foreach($l as $k=>$v){
    				            if($params['status'] != null){
    				                if($v['status'] == $params['status']){
    				                    $score_arr[]=$v;
    				                }
    				            }else{
    				                $score_arr[]=$v;
    				            }
    				        }
    				    }

    				}else{
    				    $url="http://101.201.175.219/promo/prize/ka/prize/list/".$integral_arr['activity'];//活动下所有券
    				    $return=json_decode(http($url,array()),true);//处理返回结果
    				    $score_arr=$return['data'];
    				    
    				    foreach($score_arr as $key=>$val){
    				        $delete_arr[]=$val['id'];
    				        if($params['status']!=null){
    				            if($val['status'] != $params['status']){
    				                unset($score_arr[$key]);
    				            }else{
    				                $score_arr[$key]['activity']=$integral_arr['activity'];
    				                $score_arr[$key]['activity_type']='ZHT_YX';
    				            }
    				        }else{
    				            $score_arr[$key]['activity']=$integral_arr['activity'];
    				            $score_arr[$key]['activity_type']='ZHT_YX';
    				        }
				        }
    				}
				}
				
				//调取ERP系统所有券
				if($params['type']=='ERP_YX'){
				    $url=C('DOMAIN') . '/ErpService/Erpoutput/prize_list';
				    $erp_params['key_admin']=$this->key_admin;
				    $erp_params['activity']=$integral_arr['activity'];
				    $erp_params['sign_key']=$this->admin_arr['signkey'];
				    $erp_params['sign']=sign($erp_params);
				    unset($erp_params['sign_key']);
				    $return_erp_arr=json_decode(http($url,$erp_params),true);//处理返回结果
				    if($return_erp_arr['code']==200){
    				    $score_arr=$return_erp_arr['data'];
    				    foreach($score_arr as $k=>$v){
    				        $delete_arr[]=$v['pid'];
    				        if($params['status'] != null){
        				        if($v['status'] != $params['status']){
        				            unset($score_arr[$k]);
        				        }else{
        				            $score_arr[$k]['activity']=$integral_arr['activity'];
        				            $score_arr[$k]['activity_type']='ERP_YX';
        				        }
    				        }else{
    				            $score_arr[$k]['activity']=$integral_arr['activity'];
    				            $score_arr[$k]['activity_type']='ERP_YX';
    				        }
    				    }
				    }else{
				        $score_arr=array();
				    }
				}

				//判断返回结果是否为空，如果为空则删除当前商户之前配置的活动券信息

				if(!empty($score_arr)){
					//处理调用后信息，判断券是否过期，券是否已被领取完毕
					foreach($score_arr as $k=>$v){
						$api_arr[$v['id']]['main']=$v['main_info'];
						$api_arr[$v['id']]['extend']=$v['extend_info']?$v['extend_info']:$v['main_info'];
						$api_arr[$v['id']]['imgUrl']=$v['image_url']?$v['image_url']:'';
						$api_arr[$v['id']]['startTime']=$v['start_time'];
						$api_arr[$v['id']]['endTime']=$v['end_time'];
						$api_arr[$v['id']]['pid']=$v['id'];
						$api_arr[$v['id']]['status']=$v['status'];
						$api_arr[$v['id']]['activity']=$v['activity'];
						$api_arr[$v['id']]['integral']=$v['integral']?$v['integral']:'';
						$api_arr[$v['id']]['activity_type']=$v['activity_type'];
						$api_arr[$v['id']]['writeoff_count']=$v['writeoff_count'];
						$api_arr[$v['id']]['issue']=$v['issue'];
						$api_arr[$v['id']]['num']=$v['num'];
						$api_pid[]=$v['id'];
					}

					if($params['buildid'] != ''){
					   $where['buildid']=array('eq',$params['buildid']);  
					}
					//array("integral_property.admin_id"=>$this->admin_arr['id'],"activity_type"=>$params['type'])
					$where['integral_property.admin_id']=array('eq',$this->admin_arr['id']);
					$where['activity_type']=array('eq',$params['type']);
					$where['_logic']='and';
					//查询表中券列表
					$pro_arr=$integral_pro_db->join('integral_type on integral_property.type_id=integral_type.id')->where($where)->select();
					
					if(empty($pro_arr)){
						foreach($api_arr as $key=>$val){
								$api_arr[$key]['id']='';
								$api_arr[$key]['des']='';
								if($val['integral']!=''){
								    $api_arr[$key]['integral']=$val['integral'];
								}else{
								    $api_arr[$key]['integral']='';
								}
								$api_arr[$key]['is_status']=2;
								$api_arr[$key]['type_id']='';
								$api_arr[$key]['admin_id']='';
								$api_arr[$key]['type_name']='';
								$api_arr[$key]['content']='';
								$api_arr[$key]['discount']=1;
						}
					}else{
						//未能顾忌排序
						//判断表中券ID是否与返回的券ID对应，如果没有则不显示一些属性，如果存在则全部显示
						
						foreach($pro_arr as $k=>$v){
						    if($v['discount']==2){
						        $pro_arr[$k]['integral']=json_decode($v['integral'],true);
						    }
							$pro_arr2[$v['pid']]=$v;
							$pid[]=$v['pid'];
						}
						foreach($api_arr as $key=>$val){
							if(!empty($pro_arr2[$key])){
							    unset($api_arr[$key]['integral']);
							    
							    $api_arr[$key]+=$pro_arr2[$key];
							}else{
								$api_arr[$key]['id']='';
								$api_arr[$key]['des']='';
							    if($val['integral']!=''){
								    $api_arr[$key]['integral']=$val['integral'];
								}else{
								    $api_arr[$key]['integral']='';
								}
								$api_arr[$key]['type_id']='';
								$api_arr[$key]['admin_id']='';
								$api_arr[$key]['type_name']='';
								$api_arr[$key]['is_status']=2;
								$api_arr[$key]['content']='';
								$api_arr[$key]['discount']=1;
							}
						}
// 						foreach($pid as $k=>$v){
// 							if(!in_array($v,$api_pid)){
// 								$pid2[]=$v;
// 							}
// 						}
// 						foreach($pid as $k=>$v){
// 						    if(!in_array($v, $delete_arr)){
// 						        $pid3[]=$v;
// 						    }
// 						}
// // 						print_R($pid);
// // 						print_r($delete_arr);
// // 						print_R($pid3);die;
// 						if(!empty($pid3)){
// 						    $delete_where['pid']=array('in',$pid3);
// 						    $delete_where['builid']=array('eq',$params['buildid']);
// 						    $delete_where['admin_id']=array('eq',$this->admin_arr['id']);
// 						    $delete_where['_logic']='and';
// 							$integral_pro_db->where($delete_where)->delete();
// 						}
					}
				}
				//返回数据
				if($api_arr){
					$msg=array('code'=>200,'data'=>$api_arr);
				}else{
					$msg=array('code'=>200,'data'=>'');
				}
				
			}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	/**
	 * 查询奖品信息
	 * @param $key_admin $pid
     * @return mixed
	 */
	public function integral_list_once(){
		//参数为空判断
		$act_id=I('pid');
		if(empty($act_id)){
			$msg=array('code'=>1030);
		}else{
		    $buildid=I('buildid')?I('buildid'):"";
		    
			//连表查询券所属分类以及券的一些信息
			$integral_pro_db=M('property','integral_');
			$where['pid']=array('eq',$act_id);
			$where['integral_property.admin_id']=array('eq',$this->admin_arr['id']);
			$where['buildid']=array('eq',$buildid);
			
			$where['_logic']='and';
			$pro_arr=$integral_pro_db->join('integral_type on integral_property.type_id=integral_type.id')->where($where)->find();

			if(empty($pro_arr)){

				$msg=array('code'=>102);

			}else{
			    if($pro_arr['discount']==2){
			        $pro_arr['integral']=json_decode($pro_arr['integral'],true);
			    }
			    
				$msg=array('code'=>200,'data'=>$pro_arr);

			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	/**
	 * 修改奖品信息
	 * @param $key_admin $pid $des $integral $type_id
     * @return mixed
	 */
	public function integral_operation(){
		//参数为空判断
		$act_id=I('pid');
		$des=I('des');
		$integral=I('integral');
		$discount=I('discount')?I('discount'):1;
        $prize_name=I('prize_name');
		if(empty($act_id) || !isset($des) || empty($integral) || empty($prize_name)){
			$msg=array('code'=>1030);
		}else{
		        $activity_type=I('activity_type')?I('activity_type'):'ZHT_YX';
		        $type_id=I('type_id');
		        $content=I('content');
		        $buildid=I('buildid')?I('buildid'):"";
		        $vip_area=I('vip_area');
    		    $type_db=M('type','integral_');
    		    $type_arr=$type_db->where(array('id'=>$type_id))->find();
    		    $par['scoremall_type_id']=$type_id;
    		    writeOperationLog($par,'zhanghang');
    		    if(!$type_arr){
    		        $msg['code']=1081;
    		        returnjson($msg,$this->returnstyle,$this->callback);exit();
    		    }
     
			//查看表中是否存在这条信息，如果存在则是修改，如果不存在则是添加
				$integral_pro_db=M('property','integral_');
				$where['pid']=array('eq',$act_id);
				$where['admin_id']=array('eq',$this->admin_arr['id']);
				$where['buildid']=array('eq',$buildid);
				$where['_logic']='and';
				$pro_arr=$integral_pro_db->where($where)->find();
				//echo $integral_pro_db->_sql();die;
				$data['type_id']=$type_id;
				$data['des']=$des;
				if($discount == 2){
				    $data['integral']=json_encode($integral);
				}else{
				    $data['integral']=$integral;
				}
				$data['discount']=$discount;
				$data['content']=htmlspecialchars_decode($content);
				$data['pid']=$act_id;
				$data['vip_area']=$vip_area;
                $data['prize_name']=$prize_name;
                $data['activity_type']=$activity_type;
                
				if(empty($pro_arr)){
				    $data['buildid']=$buildid;
				    $data['is_status']=2;
					$data['admin_id']=$this->admin_arr['id'];
					$res=$integral_pro_db->add($data);
				}else{
					unset($data['pid']);
					$res=$integral_pro_db->where(array('id'=>$pro_arr['id']))->save($data);
				}
				//echo $integral_pro_db->_sql();die;
				$par['scoremall_sql']=$integral_pro_db->_sql();
				writeOperationLog($par,'zhanghang');
				if($res !== false){
					$msg=array('code'=>200);
				}else{
					$msg=array('code'=>104);
				}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 获取卡类别接口
	 */
	public function card_type_list(){
	    $db=M('member_code','total_');
	    $where['admin_id']=array('eq',$this->admin_arr['id']);
	    $arr=$db->where($where)->order('sort asc')->select();
	    if($arr){
	        $msg['code']=200;
	        $msg['data']=$arr;
	    }else{
	        $msg['code']=102;
	    }
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	/**
	 * 礼品上线下线接口
	 */
	public function prize_offline(){
	    $params['buildid']=I('buildid');
	    $params['pid']=I('pid');
	    $params['is_status']=I('is_status');
	    if(in_array('', $params)){
	        $msg['code']=1030;
	    }else{
	        $db=M('property','integral_');
	        $where['buildid']=array('eq',$params['buildid']);
	        $where['pid']=array('eq',$params['pid']);
	        $where['_logic']='and';
	        $data['is_status']=$params['is_status'];
	        $res=$db->where($where)->save($data);
	        if($res){
	            $msg['code']=200;
	        }else{
	            $msg['code']=104;
	        }
	    }
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	/**
	 * 查询分类列表
	 * @param $key_admin
     * @return mixed
	 */
	public function integral_type_list(){
		$activityTypeService = ServiceLocator::getActivityTypeService();
		$arr = $activityTypeService->getAll($this->admin_arr['id'], 0);
		
		if($arr){
				
			$msg=array('code'=>200,'data'=>$arr);

		}else{
				
			$msg=array('code'=>102);

		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	/**
	 * 修改分类名称
	 * @param $key_admin $id $type_name $status
     * @return mixed
	 */
	public function integral_type_save(){
		//参数为空判断
		$type_id=I('type_id');
		$type_name=I('type_name');
		$status=I('status');//M 修改  F 添加
		if(empty($type_name) || empty($status)){
			$msg=array('code'=>1030);
		}else{
			
			//根据参数判断添加还是修改
			$data['type_name']=$type_name;
// 			$type_db=M('type','integral_');

			$activityTypeService = ServiceLocator::getActivityTypeService();
			
			if($status=='M'){

				if(empty($type_id)){

					$msg=array('code'=>1030);

				}else{

				    $res = $activityTypeService->updateById($type_id, $data);
				    
// 					$res=$type_db->where(array('id'=>$type_id))->save($data);

				}

			} else if ($status=='F'){

			    $type_arr = $activityTypeService->getByName($this->admin_arr['id'], $type_name, 0);
			    
// 				$map['type_name']=array('eq',$type_name);
// 				$map['admin_id']=array('eq',$this->admin_arr['id']);
// 				$map['system']=array('eq',0);
// 				$map['_logic']='and';
// 				$type_arr=$type_db->where($map)->find();

				//判断分类是否已经添加
				if(empty($type_arr)){

					//如果没有则入库
					$data['admin_id']=$this->admin_arr['id'];
					$data['system']=0;
					
					$res = $activityTypeService->add($data);
// 					$res=$type_db->add($data);
					
				}else{

					//否则返回已经存在
					echo returnjson(array('code'=>1008),$this->returnstyle,$this->callback);exit();

				}
				
			}
				
			if($res){
				$msg=array('code'=>200);
			}else{
				$msg=array('code'=>104);
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	/**
	 * 删除分类
	 * @param $key_admin $id
     * @return mixed
	 */
	public function integral_type_del(){
		$type_id=I('type_id');
		if(empty($type_id)){
			$msg=array('code'=>1030);
		}else{
				
			$type_db=M('type','integral_');
			$res=$type_db->where('id='.$type_id.' and admin_id='.$this->admin_arr['id'])->delete();

			if($res){
				$msg=array('code'=>200);
			}else{
				$msg=array('code'=>104);
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	/**
	 * 添加或修改活动ID
	 * @param $key_admin $activity
     * @return mixed
	 */
	public function act_add(){
	    $params['activity']=I('activity');
	    if(in_array('', $params)){
	        $msg['code']=1030;
	        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	    }
	    
// 	    $activity_db=M('activity','integral_');
	    $params['type']=I('type')?I('type'):'ZHT_YX';
	    $params['buildid']=I('buildid')?I('buildid'):"";
	    $params['activity'] = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)/",',',$params['activity']);
	    
	    //判断是否存在当前的配置,如果存在则是修改,反之添加
	    
	    $activityService = ServiceLocator::getActivityService();
	    $activity_arr = $activityService->getOnce($this->admin_arr['id'], $params['type'], $params['buildid'], 0);
	     
	    
// 	    $activity_db=M('activity','integral_');
// 	    $where['admin_id']=array('eq',$this->admin_arr['id']);
// 	    $where['buildid']=array('eq',$params['buildid']);
// 	    $where['type']=array('eq',$params['type']);
// 	    $where['_logic']='and';
// 	    $activity_arr=$activity_db->where($where)->find();
		
	    if(empty($activity_arr)){
	        
	        //修改
	        $params['admin_id']=$this->admin_arr['id'];
	        $params['system']=0;
	        
	        $res=$activityService->add($params);
	        
	    }else{
	        
	        //添加updateById
	        $res=$activityService->updateById($activity_arr['id'],array('activity'=>$params['activity']));
// 	        $res=$activityService->where(array('id'=>$activity_arr['id']))->save($params);
	        
	    }
	    
	    if($res !== false){
	        $msg['code']=200;
	    }else{
	        $msg['code']=104;
	    }
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	    
	}

	/**
	 * 获取活动ID
	 * @param $key_admin
     * @return mixed
	 */
	public function obtain_act(){
        $params['status'] = I('status');
        
        $activityService = ServiceLocator::getActivityService();
        if($params['status'] == 'new')
        {
            $act_arr = $activityService->getAll($this->admin_arr['id'], '', '', '',0);
        }
        else
        {
            //查询活动ID
            $act_arr = $activityService->getAll($this->admin_arr['id'], 'ZHT_YX', '', '',0);
        }

		//判断活动ID是否设置
		if(empty($act_arr)){
			$msg=array('code'=>102);
		}else{
		    $msg=array('code'=>200,'data'=>$act_arr);
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	/**
	 * 获取所有兑奖记录
	 */
	public function get_prize_list(){
	    $params['activity_id']=I('activity_id');
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	        $page=I('page');
	        $lines=I('lines');
	        $where['activity_id']=array('eq',$params['activity_id']);
	        $msg=$this->get_prize_action($params['activity_id'],$page,$lines,$where);
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	/**
	 * 搜索兑奖记录
	 */
	public function get_prize_search(){ 
    	    $params['activity_id']=I('activity_id');
    	    $isexport=I('export');
	        $buildid=I('buildid')?I('buildid'):"";
	        $type=I('type')?I('type'):'ZHT_YX';
	        
	        if($type != 'ZHT_YX'){
	            $msg['code']=102;
	            $msg['msg']='暂无当前接口';
	            returnjson($msg,$this->returnstyle,$this->callback);exit();
	        }
	        
	        if(!empty($buildid)){
	            $db=M('activity','integral_');
	            $map_act['buildid']=array('eq',$buildid);
	            $map_act['type']=array('eq',$type);
	            $map_act['_logic']='and';
	            $arr=$db->where($map_act)->field('activity,id')->find();
	            
	            if(empty($arr)){
	                $msg['code']=307;
	                returnjson($msg,$this->returnstyle,$this->callback);exit();
	            }
	            $params['activity_id']=$arr['activity'];
	        }
	        $status=I('status');
	        $page=I('page');
	        $lines=I('lines');
	        $mobile=I('mobile');
	        $prize_name=I('prize_name');
	        $starttime=I('starttime');
	        $endtime=I('endtime')?I('endtime'):date('Y-m-d H:i:s');
	        $mem_table=$this->admin_arr['pre_table'].'mem';
	        $integral_table=$this->admin_arr['pre_table'].'integral_log';
	        if($mobile){
// 	            $where[$mem_table.'.mobile']=array('eq',$mobile);
                $mem_db=M('mem',$this->admin_arr['pre_table']);
                $mem_arr=$mem_db->where(array('mobile'=>array('eq',$mobile)))->find();
                if($mem_arr['openid']!=''){
                    $where['open_id']=$mem_arr['openid'];
                }else{
                    $msg['code']=102;
                    returnjson($msg,$this->returnstyle,$this->callback);exit();die;
                }
	        }
	        if($status != ''){
	            $where['status']=array('eq',$status);
	        }
	        if($prize_name){
	            $where['prize_id']=array('eq',$prize_name);
	        }
	        if($starttime){
	            $where['get_time']=array('between',array($starttime,$endtime));
	        }
// 	        $where[$integral_table.'.status']=array('eq',1);
            $activity_str=explode(',', trim($params['activity_id'],','));
            $where['activity_id']=array('in',$activity_str);
    	    if(count($where)>=2){
    	        $where['_logic']='and';
    	    }
    	    
    	    if(strtolower($isexport) == 'yes' || strtolower($isexport) == 'yesall'){
    	        set_time_limit(0);
    	        $this->exportHeader();
        	    $title=array("会员名称","会员卡号","手机号","会员等级","奖品名称","积分","领取时间","状态");
        	    $this->addArray($title);
    	    }

	        $msg=$this->get_prize_action($params['activity_id'],$page,$lines,$where,$isexport);
	        
	        $use_db=M('member_code','total_');
			$level_returns=$use_db->where("admin_id=".$this->admin_arr['id'])->field('name,id,code')->select();
			
			foreach($level_returns as $k=>$v){
			    $level_return[$v['code']]=$v['name'];
			}
	        foreach($msg['data'] as $k=>$v){
	            $msg['data'][$k]['level']=$level_return[$v['level']];
	        }             
	        
	        if(strtolower($isexport) == 'yes' || strtolower($isexport) == 'yesall'){
	            if($msg['code']=='200'){
	                $msg1=$this->export_yes($msg['data']);
	                returnjson($msg1,$this->returnstyle,$this->callback);exit();
	            }
	        }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	public function export_yes($arr){
        $content=array();
        foreach($arr as $k=>$v){
            $content[]=$v['usermember'];
            $content[]=$v['cardno'];
            $content[]=$v['mobile'];
            $content[]=$v['level'];
            $content[]=$v['prize_name'];
            $content[]=$v['integral'];
            $content[]=$v['starttime'];
            $content[]=$this->status[$v['status']];
            
            $this->addArray($content);
            
            unset($content);  
        }
        
	}
	
	/**
	 * 将数据以Excel文件形式导出(文件头)
	 */
	private function exportHeader() {
	    // 文件头部信息
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Content-Disposition: attachment; filename=".date('Ymd').".xls");
	    header("Content-Type: application/vnd.ms-excel; charset=GBK");
	}
	
	private function str2csv($s) {
	    $s = str_replace('"', '""', $s);
	    return '"'.$s.'"';
	}
	
	/**
	 * 获取数据，并输出
	 *
	 * @param array $dataArray 主体数据
	 * 主体数据必须为二维数组或一维数组
	 * @return boolean 为true，数据获取成功，否则没有获取数据
	 */
	public function addArray($Array=array()) {
	    $dataArray[]=$Array;
	        foreach ($dataArray as $key => $val) {
	            $lineStr = "";
	            foreach ($val as $k => $v) {
	                if($v != null){
	                    $lineStr .= $this->str2csv(iconv('UTF-8', 'GBK', $v))."\t";
	                }else{
	                    $lineStr .= '""'."\t";
	                }
	            }
	            // 	        $lineStr = substr($lineStr, 0, -1);
	            echo $lineStr."\n";
	            flush();
	        }
	    return true;
	}
	
	
	//查询返回数据方法
	private function get_prize_action($activity_id,$page,$lines,$where,$export){
	    $page=$page?$page:1;
	    $lines=$lines?$lines:10;
	    $start=($page-1)*$lines;

	    //测试
	    $connection=$this->db_connect();//连接营销平台数据库
        //根据openid 查询营销平台中奖记录
        $db=M('prize_instance','shake_',$connection);//实例化营销平台记录表
        if(strtolower($export) == 'yesall'){          
            $arr=$db->where($where)->field('id,prize_id,open_id,get_time,status,qr_code')->order('get_time desc')->select();
        }else{
            $arr=$db->where($where)->field('id,prize_id,open_id,get_time,status,qr_code')->limit($start,$lines)->order('get_time desc')->select();
        }
        if($arr){
            
            $sum=$db->where($where)->count();
            
            foreach($arr as $k=>$v){
                $qr_code[]=$v['qr_code'];
            }
            
            $integral_db=M('integral_log',$this->admin_arr['pre_table']);

            $qr_num=count($qr_code);
            if($qr_num){          
                $qu_once=ceil($qr_num/100);
                
                $integral_arr=array();
                
                for($i=0;$i<$qu_once;$i++){
                    $start_num=$i*100;   
                    $new_qr=array_slice($qr_code,$start_num,100);
                    
                    $qr_code_str=implode(',', $new_qr);
                    
                    $integral_log=$this->admin_arr['pre_table'].'integral_log';
                    $mem=$this->admin_arr['pre_table'].'mem';
                    
                    $integral_arr_once=$integral_db->where(array('code'=>array('in',$qr_code_str)))->join('LEFT JOIN '.$mem.' on '.$integral_log.'.cardno = '.$mem.'.cardno')->field($mem.'.cardno,usermember,mobile,level,prize_name,integral,code')->select();
                    
                    $integral_arr=array_merge($integral_arr,$integral_arr_once);
                    
//                     sleep(1);
                }
            }
            
            //注:禁止循环套循环
            foreach($integral_arr as $k=>$v){
                $res_arr[$v['code']]=$v;
            }
            foreach($arr as $key=>$val){
                if($res_arr[$val['qr_code']] != ''){
                    $arr[$key]=array_merge($val,$res_arr[$val['qr_code']]);
                }
                $arr[$key]['starttime']=$val['get_time'];
            }
            $msg['code']=200;
            $msg['data']=$arr;
            $msg['sum']=$sum;
            $msg['page']=$page;
        }else{
            $msg['code']=102;
        }
        //结束
        return $msg;

	}
	
	public function prize_level(){
	    $params['activity_id']=I('activity_id');
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	        $url="http://182.92.31.114/rest/act/levels/".$params['activity_id'];//活动下所有券
	        $arr=json_decode(http($url),true);//处理返回结果
	        foreach($arr as $key=>$val){
	            $return[$key]['pid']=$val['pid'];
	            $return[$key]['main']=$val['main'];
	        }

	        foreach ($return as $key=>$value){
	            $pid[$key] = $value['pid'];
	            $main[$key] = $value['main'];
	        }
	        
	        array_multisort($main,SORT_STRING,SORT_ASC,$pid,SORT_STRING,SORT_ASC,$return);
	        $msg['code']=200;
	        $msg['data']=$return;
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	
	//数据库
	public function db_connect(){
	    if(C('DOMAIN') == 'http://localhost/member/index.php' ){
    	    $connection = array(
    	        'db_type'    =>   'mysql',
    	        'db_host'    =>   '192.168.1.117',
    	        'db_user'    =>   'rtmap',
    	        'db_pwd'     =>   'rtmap911',
    	        'db_port'    =>    3306,
    	        'db_name'    =>    'promo3-full',
    	    );
	    }else{
	        //正式
	        $connection = array(
	            'db_type'    =>   'mysql',
	            'db_host'    =>   'rdsbu5ogq3pvu740c9c9.mysql.rds.aliyuncs.com',
	            'db_user'    =>   'luck3_read',
	            'db_pwd'     =>   '123456A',
	            'db_port'    =>    3306,
	            'db_name'    =>    'promo',
	        );
	    }
	    
	    return $connection;
	    
	}
	
	
	
	
	
	/**
	 * 查看支付状态
	 */
	public function integral_status(){
	   $db=M('default',$this->admin_arr['pre_table']);
	   $arr=$db->where(array('customer_name'=>array('eq','integral_status')))->select();
	   if($arr){
	       $msg['code']=200;
	       $msg['data']=$arr;
	   }else{
	       $msg['code']=102;
	   }
	   returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	/**
	 * 添加或修改兑换状态
	 */
	public function integral_save(){
	    
	    $params['function_name']=I('function_name');
	    if(in_array('', $params)){
	        $msg['code']=1030;
	    }else{
	        $params['description']=I('description');
	        $db=M('default',$this->admin_arr['pre_table']);
	        $arr=$db->where(array('customer_name'=>array('eq','integral_status')))->select();
	        
	        $data['description']=$params['description'];
	        $data['function_name']=$params['function_name'];
	        if($arr){
	            $res=$db->where(array('customer_name'=>array('eq','integral_status')))->save($data);
	            if($res !== false){
	                $msg['code']=200;  
	            }else{
	                $msg['code']=104;
	            }
	        }else{
	            $data['customer_name']='integral_status';
	            $res=$db->add($data);
	            if($res !== false){
	                $msg['code']=200;
	            }else{
	                $msg['code']=104;
	            }
	        }
	        returnjson($msg,$this->returnstyle,$this->callback);exit();
	    }
	}
	
	/**
	 * 获取会员等级
	 */
	public function MemberLevelList(){
        $static = M('total_static');
        $page_info = $static->where(array('tid' => 5, 'admin_id' => $this->admin_arr['id']))->find();
//         $page_info = $static->where(array('id' => $sid, 'tid' => $tid, 'admin_id' => $mid))->find();
        if($page_info){
            $msg['code']=200;
            $arr=json_decode($page_info['content'],true);
            $i=0;
            foreach($arr as $k=>$v){
                $return[$i]['id']=$k;
                $return[$i]['level']=$v;
                $i++;
            }
            $msg['data']=$return;
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	/**
	 * 修改或添加Y币商城的banner
	 */
	public function banner_save(){
	    $params['banner_name']=I('banner_name');
	    $params['url']=I('img_url');
	    $params['jump_url']=I('jump_url');
	    $params['buildid']=I('buildid');
	    if(in_array('', $params)){
	        $msg['code']=1030;
	    }else{
	        $params['sort']=I('sort');
	        $banner_id=I('id');
	        $banner_db=M('banner','integral_');
	         
	        if($banner_id){
	            $map['id']=array('eq',$banner_id);
	            $map['admin_id']=array('eq',$this->admin_arr['id']);
	            $map['_logic']='and';
	            $res=$banner_db->where($map)->save($params);
	        }else{
	            $map['admin_id']=array('eq',$this->admin_arr['id']);
	            $res=$banner_db->where($map)->order('sort desc')->field('sort')->find();
	            $params['admin_id']=$this->admin_arr['id'];
	            $params['status']=2;
	            if($params['sort']==''){
	                if($res){
	                    $params['sort']=$res['sort']+1;
	                }else{
	                    $params['sort']=1;
	                }
	            }
	            $res=$banner_db->add($params);
	        }
	        if($res !== false){
	            $msg['code']=200;
	        }else{
	            $msg['code']=104;
	        }
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	/**
	 * Y币banner置顶接口
	 */
	public function banner_up(){
	    $params['id']=I('banner_id');
	    if(in_array('', $params)){
	        $msg['code']=1030;
	    }else{
	        $banner_db=M('banner','integral_');
	        $map['id']=array('eq',$params['id']);
	        $res=$banner_db->where($map)->field('sort')->find();
	         
	        if($banner_db->where(array('admin_id'=>$this->admin_arr['id'],'sort'=>array('lt',$res['sort'])))->setInc('sort')){
	            if($banner_db->where(array('admin_id'=>$this->admin_arr['id'],'id'=>array('eq',$params['id'])))->save(array('sort'=>1))){
	                $msg['code']=200;
	            }else{
	                $msg['code']=104;
	            }
	        }else{
	            $msg['code']=104;
	        }
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	/**
	 * Y币banner删除接口
	 */
	public function banner_del(){
	    $params['id']=I('banner_id');
	    if(in_array('', $params)){
	        $msg['code']=1030;
	    }else{
	        $banner_db=M('banner','integral_');
	        $map['id']=array('eq',$params['id']);
	        $re=$banner_db->where($map)->delete();
	        if($re){
	            $msg['code']=200;
	        }else{
	            $msg['code']=104;
	        }
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	/**
	 * 获取建筑物ID
	 */
	public function builid_list(){
	    $where['adminid']=array('eq',$this->admin_arr['id']);
	    $where['is_del']=array('eq',2);
	    $where['_logic']='and';
	    $db=M('buildid','total_');
	    $arr=$db->where($where)->select();
	    if($arr){
	        $msg['code']=200;
	        $msg['data']=$arr;
	    }else{
	        $msg['code']=102;
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	/**
	 * Y币获取单个banner接口
	 */
	public function banner_find(){
	    $params['id']=I('banner_id');
	    if(in_array('', $params)){
	        $msg['code']=1030;
	    }else{
	        $banner_db=M('banner','integral_');
	        $map['admin_id']=array('eq',$this->admin_arr['id']);
	        $map['id']=array('eq',$params['id']);
	        $map['_logic']='and';
	        $res=$banner_db->where($map)->find();

	        if($res){
	            $where['adminid']=array('eq',$this->admin_arr['id']);
	            $where['buildid']=array('eq',$res['buildid']);
	            $where['_logic']='and';
	            $db=M('buildid','total_');
	            $arr=$db->where($where)->find();
	            $res['name']=$arr['name']?$arr['name']:$res['buildid'];
	            
	            $msg['code']=200;
	            $msg['data']=$res;
	        }else{
	            $msg['code']=102;
	        }
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	/**
	 * Y币获取banner列表
	 */
	public function banner_list(){
	    $buildid=I('buildid');
	    if($buildid){
	        $map['buildid']=array('eq',$buildid);
	        $map['_logic']='and';
	    }
	    $banner_db=M('banner','integral_');
	    $map['admin_id']=array('eq',$this->admin_arr['id']);
	    $res=$banner_db->where($map)->select();
	    
	    $where['adminid']=array('eq',$this->admin_arr['id']);
	    $db=M('buildid','total_');
	    $arr=$db->where($where)->select();
	    
	    foreach($arr as $k=>$v){
	        $arr[$v['buildid']]=$v['name'];
	    }
	    
	    foreach($res as $key=>$val){
	        if($arr[$val['buildid']] != ''){
	            $res[$key]['name']=$arr[$val['buildid']];
	        }else{
	            $res[$key]['name']=$val['buildid'];
	        }
	    }
	    
	    if($res){
	        $msg['code']=200;
	        $msg['data']=$res;
	    }else{
	        $msg['code']=102;
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	/**
	 * 添加颜色接口
	 */
	public function integral_color_add(){
	    $params['color']=I('color');
	    $find=$this->GetOneAmindefault($this->admin_arr['pre_table'], $this->key_admin, 'integralcolorset');
	    $db=M('default',$this->admin_arr['pre_table']);
	    if($find != ''){
	        $save_url=$db->where(array('customer_name'=>'integralcolorset'))->save(array('function_name'=>$params['color']));
	    }else{
	        $save_url=$db->add(array('function_name'=>$params['color'],'customer_name'=>'integralcolorset','description'=>'设置C端积分商城颜色配置'));
	    }
	    $this->redis->del('admin:default:one:integralcolorset:'. $this->key_admin);//删除redis缓存
	    
	    if($save_url !== false){
	        $msg['code']=200;
	    }else{
	        $msg['code']=104;
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	/**
	 * 获取颜色接口
	 */
	public function get_integral_color(){
	    $find=$this->GetOneAmindefault($this->admin_arr['pre_table'], $this->key_admin, 'integralcolorset');
	    if($find){
	        $msg['code']=200;
	        $msg['data']=$find;
	    }else{
	        $msg['code']=102;
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	/**
	 * 编辑上线下线
	 */
	public function banner_status(){
	    $params['id']=I('banner_id');
	    if(in_array('', $params)){
	        $msg['code']=1030;
	    }else{
	        $db=M('banner','integral_');
	        $arr=$db->where(array('id'=>array('eq',$params['id'])))->find();
	        if($arr['status'] == 1){
	            $data['status']=2;
	        }else{
	            $data['status']=1;
	        }
	        if($arr['status'] == 1){
	            $where['admin_id']=array('eq',$this->admin_arr['id']);
	            $where['status']=array('eq',1);
	            $where['_logic']='and';
	            $num=$db->where($where)->count();
	            if($num>=5){
	                $msg['code']=1009;
	                $msg['msg']='开启已达上限';
	                returnjson($msg,$this->returnstyle,$this->callback);exit();
	            }
	        }
	        $res=$db->where(array('id'=>array('eq',$params['id'])))->save($data);
	        if($res){
	            $msg['code']=200;
	        }else{
	            $msg['code']=104;
	        }
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	public function action_prize(){
        
	    $buildid = I('buildid');
	    $type = I('type');
	    
	    $connection=$this->db_connect();//连接营销平台数据库
	    //根据openid 查询营销平台中奖记录
	    $db1=M('prize_instance','shake_',$connection);//实例化营销平台记录表
	    $integral_db=M('integral_log',$this->admin_arr['pre_table']);
	    
	    $db=M('activity','integral_');
	    $map_act['buildid']=array('eq',$buildid);
	    $map_act['admin_id']=array('eq',$this->admin_arr['id']);
	    $map_act['type']=array('eq',$type);
	    $map_act['_logic']='and';
	    $arr=$db->where($map_act)->field('activity,id')->find();
	     
	    if(empty($arr)){
	        $msg['code']=307;
	        returnjson($msg,$this->returnstyle,$this->callback);exit();
	    }
	    $params['activity_id']=$arr['activity'];

	    $activity_str=explode(',', trim($params['activity_id'],','));
	    $where['activity_id']=array('in',$activity_str);
	    
	    $arr1=$db1->where($where)->order('get_time desc')->select();
	    $where['status']=array('eq',1);
	    $where['_logic']='and';
	    $integral_arr=$integral_db->where($where)->order('starttime desc')->select();
        $i=1;
	    foreach($integral_arr as $k=>$v){
	        foreach($arr1 as $key=>$val){
	            if($v['openid'] == $val['open_id'] && $v['pid'] == $val['prize_id']){
	                
	                if($v['code'] == ''){
	                    $integral_db->where(array('id'=>$v['id']))->save(array('code'=>$val['qr_code']));
	                }
	                echo $integral_db->_sql();echo "<br>";echo $i;
	                $i++;
	                unset($arr1[$key]);
	                break;
	            }else{
	                unset($arr1[$key]);
	            }
	        }
	    }
	    
	}
	
}

