<?php
/**
 * Created by EditPlus.
 * User: zhanghang
 * Date: 6/24/16
 * Time: 10:10 AM
 */

namespace MerAdmin\Controller;
use PublicApi\Controller\QiniuController;

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
			$integral_db=M('activity','integral_');
			$zht_where['admin_id']=array('eq',$this->admin_arr['id']);
			$zht_where['buildid']=array('eq',$params['buildid']);
			$zht_where['type']=array('eq',$params['type']);
			$zht_where['_logic']='and';
			$integral_arr=$integral_db->where($zht_where)->find();
// 			$erp_where['admin_id']=array('eq',$this->admin_arr['id']);
// 			$erp_where['type']=array('eq','ERP_YX');
// 			$erp_where['_logic']='and';
// 			$erp_integral_arr=$integral_db->where($erp_where)->find();
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
    				        if($res[$i]!= ''){
    				            if($i==1){
    				                $score_arr=array_merge($res[$i],$res[0]);
    				            }else if($i>1){
    				                $score_arr=array_merge($res[$i],$score_arr);
    				            }
    				            foreach($score_arr as $key=>$val){
    				                if($params['status'] != null){
    				                    if($val['status'] != $params['status']){
    				                        $delete_arr[]=$val['pid'];
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
    				            $i++;
    				        }
    				    }
    				}else{
    				    $url="http://101.201.175.219/promo/prize/ka/prize/list/".$integral_arr['activity'];//活动下所有券
    				    $return=json_decode(http($url,array()),true);//处理返回结果
    				    $score_arr=$return['data'];
    				    
    				    foreach($score_arr as $key=>$val){
    				        if($params['status']!=null){
    				            if($val['status'] != $params['status']){
    				                $delete_arr[]=$val['pid'];
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
    				        if($params['status'] != null){
        				        if($v['status'] != $params['status']){
        				            $delete_arr[]=$v['pid'];
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
				if(empty($score_arr)){
				    if($params['status'] != null){
				        $msg['code']=102;
				    }else{
				        if($params['buildid'] != ''){
				            $where['buildid']=array('eq',$params['buildid']);
				        }
				        $where['admin_id']=array('eq',$this->admin_arr['id']);
				        if($params['type']){
				            $where['activity_type']=array('eq',$params['type']);
				        }
				        $where['_logic']='and';
				        $integral_pro_db->where($where)->delete();
				    }
				    
				}else{
				
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

						foreach($pid as $k=>$v){
							if(!in_array($v,$api_pid)){
								$pid2[]=$v;
							}
						}
						foreach($pid2 as $k=>$v){
						    if(!in_array($v, $delete_arr)){
						        $pid3[]=$v;
						    }
						}
						if(!empty($pid2)){
						    $delete_where['pid']=array('in',$pid3);
						    $delete_where['builid']=array('eq',$params['buildid']);
						    $delete_where['admin_id']=array('eq',$this->admin_arr['id']);
						    $delete_where['_logic']='and';
							$integral_pro_db->where($delete_where)->delete();
						}
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
		
		$type_db=M('type','integral_');

		$arr=$type_db->where(array('admin_id'=>$this->admin_arr['id']))->select();

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
			$type_db=M('type','integral_');

			if($status=='M'){

				if(empty($type_id)){

					$msg=array('code'=>1030);

				}else{

					$res=$type_db->where(array('id'=>$type_id))->save($data);

				}

			} else if ($status=='F'){
				$map['type_name']=array('eq',$type_name);
				$map['admin_id']=array('eq',$this->admin_arr['id']);
				$map['_logic']='and';
				$type_arr=$type_db->where($map)->find();

				//判断分类是否已经添加
				if(empty($type_arr)){

					//如果没有则入库
					$data['admin_id']=$this->admin_arr['id'];
					$res=$type_db->add($data);
					
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
	    
	    $params['type']=I('type')?I('type'):'ZHT_YX';
	    $params['buildid']=I('buildid')?I('buildid'):"";
	    $params['activity'] = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)/",',',$params['activity']);
	    
	    //判断是否存在当前的配置,如果存在则是修改,反之添加
	    $activity_db=M('activity','integral_');
	    $where['admin_id']=array('eq',$this->admin_arr['id']);
	    $where['buildid']=array('eq',$params['buildid']);
	    $where['type']=array('eq',$params['type']);
	    $where['_logic']='and';
	    $activity_arr=$activity_db->where($where)->find();
		
	    if(empty($activity_arr)){
	        
	        //修改
	        $params['admin_id']=$this->admin_arr['id'];
	        $res=$activity_db->add($params);
	        
	    }else{
	        
	        //添加
	        $res=$activity_db->where(array('id'=>$activity_arr['id']))->save($params);
	        
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
        $params['status']=I('status');
        $act_db=M('activity','integral_');
        if($params['status'] == 'new'){
            $map['admin_id']=array('eq',$this->admin_arr['id']);
            $act_arr=$act_db->where($map)->select();
        }else{
            //查询活动ID
            $map['admin_id']=array('eq',$this->admin_arr['id']);
            $map['type']=array('eq','ZHT_YX');
            $map['_logic']='and';
            $act_arr=$act_db->where($map)->find();
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
    	    $par['integral_arr_where']=$where;
    	    $par['integral_arr_export']=$isexport;
    	    writeOperationLog($par,'zhanghang');
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
	                $msg1=$this->export_yes($msg,$level_return);
	                returnjson($msg1,$this->returnstyle,$this->callback);exit();
	            }
	        }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	public function export_yes($res,$level_return){
	    //{"01":["AA","银卡"],"02":["BB","金卡"],"03":["GG":"黑卡"],"04":["DD","黑钻卡"]}
	    if($res['code']==200){
	        $str[]="会员名,手机号,卡号,会员等级,奖品名称,消耗积分,注册时间,状态";
	        foreach($res['data'] as $k=>$v){
	            $str[]=$v['usermember'].",".$v['mobile'].",".$v['cardno'].",".$level_return[$v['level']].",".$v['prize_name'].",".$v['integral'].",".$v['starttime'].",".$this->status[$v['status']];
	        }
	        
	        $return=CreateCsvFile($str,RUNTIME_PATH.'wechat/fans/','csv');
	        if($return){
	            $time = date("Ymd");
	            $uniqid = uniqid();
	            $key = 'fans_'.$time.'_'.$uniqid.'.csv';
	            $qiniu=new QiniuController;
	            list($ret, $err)=$qiniu->uploadfile($return,$key);
	            unlink($return);
	            if ($err !== null) {
	                $msg['code']=104;
	            }else{
	                $msg['code']=200;
	                $msg['data']=array('url'=>"https://img.rtmap.com/".$key);
	            }
	        }else{
	            $msg['code']=$return['code'];
	        }
	    }else{
	        $msg['code']=$res['code'];
	    }
	    return $msg;
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
        $sum=$db->where($where)->field('id,prize_id,open_id,get_time,status,qr_code')->count();
        if($arr){
            foreach($arr as $k=>$v){
//                 $openid[]=$v['open_id'];
//                 $prizeid[]=$v['prize_id'];
                $qr_code[]=$v['qr_code'];
            }
            
            //获取openid
//             $open_arr=array_unique($openid);
//             $open_str=implode(',', $open_arr);
            //获取奖品id
//             $prize_arr=array_unique($prizeid);
            $integral_db=M('integral_log',$this->admin_arr['pre_table']);
//             if($this->key_admin == '75458833a43dc64df5069e03bdad1ec5'){
            if(count($qr_code) > 1){
                $qr_code_str=implode(',', $qr_code);
                $integral_arr=$integral_db->where(array('code'=>array('in',$qr_code_str)))->select();
            }else{
                $integral_arr=$integral_db->where(array('code'=>array('eq',$qr_code[0])))->select();
            }
            
            foreach($integral_arr as $k=>$v){
                $cardno[]=$v['cardno'];
            }
            if($cardno){
                $cardno_arr=array_unique($cardno);
                $cardno_str=implode(',', $cardno_arr);
                
                $mem_db=M('mem',$this->admin_arr['pre_table']);
                $mem_arr=$mem_db->where(array('cardno'=>array('in',$cardno_str)))->select();
                
                foreach($mem_arr as $k=>$v){
                    foreach($integral_arr as $key=>$val){
                        if($v['cardno'] == $val['cardno'] ){
                            $integral_arr[$key]['usermember']=$v['usermember'];
                            $integral_arr[$key]['mobile']=$v['mobile'];
                            $integral_arr[$key]['cardno']=$v['cardno'];
                            $integral_arr[$key]['level']=$v['level'];
                        }
                    }
                }
            }else{
                foreach($integral_arr as $key=>$val){
                    $integral_arr[$key]['usermember']='';
                    $integral_arr[$key]['mobile']='';
                    $integral_arr[$key]['cardno']='';
                    $integral_arr[$key]['level']='';
                }
            }
            
            foreach($integral_arr as $k=>$v){
                foreach($arr as $key=>$val){
                    if($v['code'] == $val['qr_code']){
                        $arr[$key]['prize_name']=$v['prize_name'];
                        $arr[$key]['integral']=$v['integral'];
                        $arr[$key]['starttime']=$val['get_time'];
                        $arr[$key]['usermember']=$v['usermember'];
                        $arr[$key]['mobile']=$v['mobile'];
                        $arr[$key]['cardno']=$v['cardno'];
                        $arr[$key]['level']=$v['level'];
                    }
                }
            }
                
//             }else{
//                 if(count($prize_arr) > 1){
//                     $prize_str=implode(',', $prize_arr);
//                     $integral_arr=$integral_db->where(array('pid'=>array('in',$prize_str)))->select();
//                 }else{
//                     $integral_arr=$integral_db->where(array('pid'=>array('eq',$prize_arr[0])))->select();
//                 }
                
//                 $mem_db=M('mem',$this->admin_arr['pre_table']);
//                 $mem_arr=$mem_db->where(array('openid'=>array('in',$open_str)))->select();
                
//                 foreach($mem_arr as $k=>$v){
//                     foreach($arr as $key=>$val){
//                         if($v['openid'] == $val['open_id']){
//                             $arr[$key]['usermember']=$v['usermember'];
//                             $arr[$key]['mobile']=$v['mobile'];
//                             $arr[$key]['cardno']=$v['cardno'];
//                             $arr[$key]['level']=$v['level'];
//                             $arr[$key]['openid']=$v['openid'];
//                             $arr[$key]['status']=$val['status'];
//                         }
//                         $arr[$key]['starttime']=$val['get_time'];
//                     }
//                 }
                
//                 foreach($integral_arr as $k=>$v){
//                     foreach($arr as $key=>$val){
//                         if($v['pid'] == $val['prize_id']){
//                             $arr[$key]['prize_name']=$v['prize_name'];
//                             $arr[$key]['integral']=$v['integral'];
//                         }
//                     }
//                 }
                
//             }

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
