<?php
/**
 * Created by EditPlus.
 * User: zhanghang
 * Date: 4/6/17
 * Time: 17:24 PM
 */

namespace MerAdmin\Controller;
use PublicApi\Controller\QiniuController;

class YcoinactionController extends AuthController
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

	//Y币商城

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
	        $arr=json_decode(http($url),assoc);//处理返回结果
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
		//查询活动ID
			$integral_db=M('activity','coin_');
			$integral_arr=$integral_db->where(array("admin_id"=>$this->admin_arr['id']))->find();

			if(empty($integral_arr)){

				$msg=array('code'=>307,'data'=>'暂无奖品，敬请期待...');

			}else{
			    $integral_pro_db=M('property','coin_');
				//调用奖品信息
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
				//判断返回结果是否为空，如果为空则删除当前商户之前配置的活动券信息
				if(empty($arr)){
					$integral_pro_db->where(array('admin_id'=>$this->admin_arr['id']))->delete();
				}else{
				
					//处理调用后信息，判断券是否过期，券是否已被领取完毕
					foreach($arr as $k=>$v){
						if($v['num']>$v['issue']){
							$api_arr[$v['pid']]['main']=$v['main'];
							$api_arr[$v['pid']]['extend']=$v['extend'];
							$api_arr[$v['pid']]['imgUrl']=$v['imgUrl'];
							$api_arr[$v['pid']]['startTime']=$v['startTime'];
							$api_arr[$v['pid']]['endTime']=$v['endTime'];
							$api_arr[$v['pid']]['pid']=$v['pid'];
							$api_pid[]=$v['pid'];
						}
					}
					
					//查询表中券列表
					$pro_arr=$integral_pro_db->where(array('coin_property.admin_id'=>$this->admin_arr['id']))->select();
					
					if(empty($pro_arr)){
						foreach($api_arr as $key=>$val){
								$api_arr[$key]['id']='';
								$api_arr[$key]['des']='';
								$api_arr[$key]['integral']='';
								$api_arr[$key]['type_id']='';
								$api_arr[$key]['admin_id']='';
								$api_arr[$key]['type_name']='';
						}
					}else{
						//未能顾忌排序
						//判断表中券ID是否与返回的券ID对应，如果没有则不显示一些属性，如果存在则全部显示
						foreach($pro_arr as $k=>$v){
							$pro_arr2[$v['pid']]=$v;
							$pid[]=$v['pid'];
						}
						foreach($api_arr as $key=>$val){
							if(!empty($pro_arr2[$key])){
								$api_arr[$key]+=$pro_arr2[$key];	
							}else{
								$api_arr[$key]['id']='';
								$api_arr[$key]['des']='';
								$api_arr[$key]['integral']='';
								$api_arr[$key]['type_id']='';
								$api_arr[$key]['admin_id']='';
								$api_arr[$key]['type_name']='';
							}
						}

						foreach($pid as $k=>$v){
							if(!in_array($v,$api_pid)){
								$pid2[]=$v;
							}
						}
						if(!empty($pid2)){
							$integral_pro_db->where(array('pid'=>array('in',$pid2)))->delete();
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
			 
			//连表查询券所属分类以及券的一些信息
			$integral_pro_db=M('property','coin_');

			$pro_arr=$integral_pro_db->join('coin_type on coin_property.type_id=coin_type.id')->where(array('pid'=>$act_id))->find();

			if(empty($pro_arr)){

				$msg=array('code'=>102);

			}else{

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
        $prize_name=I('prize_name');
		if(empty($act_id) || !isset($des) || !is_numeric($integral) || empty($prize_name)){
			$msg=array('code'=>1030);
		}else{
		        
			//查看表中是否存在这条信息，如果存在则是修改，如果不存在则是添加
				$integral_pro_db=M('property','coin_');
				$pro_arr=$integral_pro_db->where('pid='.$act_id.' and admin_id='.$this->admin_arr['id'])->find();
				//echo $integral_pro_db->_sql();die;
				$data['des']=$des;
				$data['integral']=$integral;
				$data['pid']=$act_id;
                $data['prize_name']=$prize_name;
                
				if(empty($pro_arr)){
					$data['admin_id']=$this->admin_arr['id'];
					$res=$integral_pro_db->add($data);
				}else{
					unset($data['pid']);
					$res=$integral_pro_db->where(array('pid'=>$act_id))->save($data);
				}

				if($res !== false){
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
		
		//参数为空判断
		$activity=I('activity');
		if(empty($activity)){
			$msg=array('code'=>1030);
		}else{

		    $activity = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)/",',',$activity);
			//查询表中数据，存在则是修改，不存在则是添加
			$activity_db=M('activity','coin_');

			$data['admin_id']=$this->admin_arr['id'];
			$data['activity']=$activity;

			$activity_arr=$activity_db->where(array('admin_id'=>$this->admin_arr['id']))->find();

			if(empty($activity_arr)){
				//添加
				$activity_res=$activity_db->add($data);

			}else{
				//修改
				$activity_res=$activity_db->where(array('id'=>$activity_arr['id']))->save($data);

			}

			if($activity_res !== false){
				$msg=array('code'=>200);
			}else{
				$msg=array('code'=>104);
			}

		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 获取活动ID
	 * @param $key_admin
     * @return mixed
	 */
	public function obtain_act(){

		//查询活动ID
		$act_db=M('activity','coin_');
		$act_arr=$act_db->where(array('admin_id'=>$this->admin_arr['id']))->find();
		
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
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	        $page=I('page');
	        $lines=I('lines');
	        $openid=I('openid');
	        $status=I('status');
	        $starttime=I('starttime');
	        $endtime=I('endtime')?I('endtime'):date('Y-m-d');
	        $endtime=date('Y-m-d',strtotime($endtime)+3600*24);
	        $mem_table=$this->admin_arr['pre_table'].'coin';
	        $integral_table=$this->admin_arr['pre_table'].'coin_log';
	        if($openid){
	            $where['open_id']=array('like',array('%'.$openid.'%'));
	        }
	        if($status != ''){
	            $where['status']=array('eq',$status);
	        }
	        if($starttime){
	            $where['get_time']=array('between',array($starttime,$endtime));
	        }
// 	        $where[$integral_table.'.status']=array('eq',1);
	        $where['activity_id']=array('eq',$params['activity_id']);
    	    if(count($where)>=2){
    	        $where['_logic']='and';
    	    }
    	    $par['coin_arr_where']=$where;
    	    $par['coin_arr_export']=$isexport;
    	    writeOperationLog($par,'zhanghang');
	        $msg=$this->get_prize_action($params['activity_id'],$page,$lines,$where,$isexport);
	        
	        if(strtolower($isexport) == 'yes' || strtolower($isexport) == 'yesall'){
	            if($msg['code']=='200'){
	                $level_return=$this->get_mem_level($this->key_admin);
	                $msg1=$this->export_yes($msg,$level_return);
	                returnjson($msg1,$this->returnstyle,$this->callback);exit();
	            }
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
            $arr=$db->where($where)->field('id,prize_id,open_id,get_time,status')->order('get_time desc')->select();
        }else{
            $arr=$db->where($where)->field('id,prize_id,open_id,get_time,status')->limit($start,$lines)->order('get_time desc')->select();
        }
        $sum=$db->where($where)->field('id,prize_id,open_id,get_time,status')->count();
        if($arr){
            foreach($arr as $k=>$v){
                $openid[]=$v['open_id'];
                $prizeid[]=$v['prize_id'];
            }
            
            //获取openid
            $open_arr=array_unique($openid);
            $open_str=implode(',', $open_arr);
            //获取奖品id
            $prize_arr=array_unique($prizeid);
            $integral_db=M('coin_log',$this->admin_arr['pre_table']);
            if(count($prize_arr) > 1){
                $prize_str=implode(',', $prize_arr);
                $integral_arr=$integral_db->where(array('pid'=>array('in',$prize_str)))->select();
            }else{
                $integral_arr=$integral_db->where(array('pid'=>array('eq',$prize_arr[0])))->select();
            }
            
            $mem_db=M('coin',$this->admin_arr['pre_table']);
            $mem_arr=$mem_db->where(array('openid'=>array('in',$open_str)))->select();
            
            foreach($mem_arr as $k=>$v){
                foreach($arr as $key=>$val){
                    if($v['openid'] == $val['open_id']){
                        $arr[$key]['usermember']=$v['nickname'];
                        $arr[$key]['headimg']=$v['headimg'];
                        $arr[$key]['openid']=$v['openid'];
                        $arr[$key]['status']=$val['status'];
                    }
                    $arr[$key]['starttime']=$val['get_time'];
                }
            }
            
            foreach($integral_arr as $k=>$v){
                foreach($arr as $key=>$val){
                    if($v['pid'] == $val['prize_id']){
                        $arr[$key]['prize_name']=$v['prize_name'];
                        $arr[$key]['integral']=$v['integral'];
                    }
                }
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
	
	//数据库
	public function db_connect(){
	    if(C('DOMAIN') == 'http://localhost/member/index.php' ){
    	    $connection = array(
    	        'db_type'    =>   'mysql',
    	        'db_host'    =>   '192.168.1.58',
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
	   $arr=$db->where(array('customer_name'=>array('eq','Ycoin_status')))->select();
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
	        $arr=$db->where(array('customer_name'=>array('eq','Ycoin_status')))->select();
	        
	        $data['description']=$params['description'];
	        $data['function_name']=$params['function_name'];
	        if($arr){
	            $res=$db->where(array('customer_name'=>array('eq','Ycoin_status')))->save($data);
	            if($res !== false){
	                $msg['code']=200;  
	            }else{
	                $msg['code']=104;
	            }
	        }else{
	            $data['customer_name']='Ycoin_status';
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
	    if(in_array('', $params)){
	        $msg['code']=1030;
	    }else{
	        $banner_id=I('id');
	        $banner_db=M('banner','coin_');
	        if($banner_id){
	            $map['id']=array('eq',$banner_id);
	            $map['admin_id']=array('eq',$this->admin_arr['id']);
	            $map['_logic']='and';
	            $res=$banner_db->where($map)->save($params);
	        }else{
	            $map['admin_id']=array('eq',$this->admin_arr['id']);
	            $res=$banner_db->where($map)->order('sort desc')->field('sort')->find();
	            $params['admin_id']=$this->admin_arr['id'];
	            if($res){
	                $params['sort']=$res['sort']+1;
	            }else{
	                $params['sort']=1;
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
	        $banner_db=M('banner','coin_');
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
	        $banner_db=M('banner','coin_');
	        $map['id']=array('eq',$params['id']);
	        $res=$banner_db->where($map)->field('sort')->find();
	        
	        $re=$banner_db->where($map)->delete();
	        if($re){
	           $date=$banner_db->where(array('admin_id'=>$this->admin_arr['id'],'sort'=>array('gt',$res['sort'])))->setDec('sort');
	           if($date){
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
	 * Y币获取单个banner接口
	 */
	public function banner_find(){
	    $params['id']=I('banner_id');
	    if(in_array('', $params)){
	        $msg['code']=1030;
	    }else{
	        $banner_db=M('banner','coin_');
	        $map['admin_id']=array('eq',$this->admin_arr['id']);
	        $map['id']=array('eq',$params['id']);
	        $map['_logic']='and';
	        $res=$banner_db->where($map)->find();
	        if($res){
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
	    $banner_db=M('banner','coin_');
	    $map['admin_id']=array('eq',$this->admin_arr['id']);
	    $res=$banner_db->where($map)->order('sort asc')->select();
	    if($res){
	        $msg['code']=200;
	        $msg['data']=$res;
	    }else{
	        $msg['code']=102;
	    }
	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
}

   
