<?php
/**
 * Created by PhpStorm.
 * User: jaleel and zhanghang
 * Date: 5/30/16
 * Time: 10:10 AM
 */

namespace DevAdmin\Controller;
use Common\Service\RedisService;
use Think\Controller;
use Common\Controller\CommonController;
use Common\Controller\RedisController as A;

class MemberController extends CommonController
{
	public $key_admin;
	public $merInfo;
	public $admin_id;

	public function _initialize(){
		parent::__initialize();
		$tokens=I('ukey');
		if($tokens==''){
		    echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
		}
		
		//判断用户是否过期
		$get=$_SESSION[$tokens];
		//echo $get."....................".$tokens;die;
		//if(empty($get)){
		  //  echo returnjson(array('code'=>502),$this->returnstyle,$this->callback);exit();
		//}

		$this->merInfo = $this->getMerchant($tokens);
		
		$this->key_admin=$tokens;
		$this->ukey=$tokens;
	}

	/**
	 * 商场列表
	 * @param $search
     * @return mixed
	 */
	public function admin_list(){
		//搜索关键字
		$search=I('search');
		$db=M('admin','total_');
		$page=I('page');
		$lines=I('lines');
        $page= false != $page ? $page : 1;
        $lines= false != $lines ? $lines : 10;
        $start=($page-1)*$lines;
        $classes=I('classes');//1商场端 2商户端
        $where['pid'] = $classes==1?0:array('neq',0);
        if(!empty($search)){
			//搜索关坚持describe和ukey 进行搜索
			$where['describe|ukey']=array('like','%'.$search.'%');
            $count=$db->where($where)->count();
			//查询搜索后的数据
			$res=$db->where($where)->limit($start, $lines)->select();
		}else{
            $count=$db->where($where)->count();
			//查询全部数据
			$res=$db->where($where)->limit($start, $lines)->select();
			
		}
		if(!empty($res)){
			//获取子支付账户数
			$payDb=M('pay_child','total_');
			$payRes = $payDb->field('adminid,count(id) as pay_acc_num')->group('adminid')->select();
			$payRes = ArrKeyFromId($payRes,'adminid');
			//循环赋值到商户下
			foreach($res as $k=>$v){
				$res[$k]['pay_acc_num'] = !empty($payRes[$v['id']])?$payRes[$v['id']]['pay_acc_num']:0;

				//调用方法，获取crm和停车类名
                $crm=$this->GetOneAmindefault($v['pre_table'], $v['ukey'], 'crmclassname');
                $car=$this->GetOneAmindefault($v['pre_table'], $v['ukey'], 'parkclassname');
                $res[$k]['crm']=$crm['function_name'];
                $res[$k]['car']=$car['function_name'];
 			}
 			$pagenum=ceil($count/$lines);
 			$data=array('data'=>$res,'page'=>$page, 'count'=>$count, 'count_page'=>$pagenum);
			$msg=array('code'=>200,'data'=>$data);
		}else{
			$msg=array('code'=>102);	
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	
	/**
	 * 配置商场
	 * @param $username,$password,$re_pwd,$pre_table,$describe
     * @return mixed
	 */
	public function add_admin(){
		$username=I('username');
		$password=I('password');
		$re_pwd=I('re_pwd');
		$pre_table=I('pre_table');
		$describe=I('describe');
        $pid=I('pid');
        $pid=!empty($pid)?(int)$pid:0;
        $db=M('admin','total_');
		//$appid=I('appid');
		if(empty($username) || empty($password) || empty($re_pwd) || empty($pre_table) || empty($describe)){
			$msg=array('code'=>1030,'msg'=>'ERROR','data'=>'未获取到必要的信息');
		}else{
			if($password!=$re_pwd){
				$msg=array('code'=>3000,'msg'=>'ERROR','data'=>'两次密码不一致');
			}else{
				if($pid){
					$arr3=$db->where(array('pid'=>$pid))->select();
					if($arr3){
						returnjson(array('code'=>1082,'msg'=>"该商户客户端已经被其他商户绑定！"),$this->returnstyle,$this->callback);
					}
				
				}
				$datetime=time();
				$ukey=md5(date("Y-m-d H:i:s",time()).'&'.rand(1000,9999).$username);
				$signkey=md5($username.'&'.date("Y-m-d H:i:s",time()).'&'.rand(1000,9999));
				$data['name']=$username;
				$data['password']=md5($password);
				$data['datetime']=$datetime;
				$data['ukey']=$ukey;
				$data['signkey']=$signkey;
				$data['pre_table']=$pre_table;
				$data['describe']=$describe;
                $data['pid']=!empty($pid)?(int)$pid:0;
				$data['enable']=0;
				//$data['wechat_appid']=$appid;
				$arr=$db->where(array('name'=>$username))->select();
				/*子账户判断同名账户start*/
                $child_arr=M('admin_child','total_')->where(array('name'=>$username))->select();
                if(!empty($child_arr)) {
                    $msg1 = array('code' => 2001, 'msg' => 'ERROR', 'data' => '用户已存在(子账户)');
                    echo returnjson($msg1,$this->returnstyle,$this->callback);exit();
                }
                /*子账户判断同名账户end*/
				if(!empty($arr)){
					$msg=array('code'=>2001,'msg'=>'ERROR','data'=>'用户已存在');
				}else{
					$re=$db->add($data);
					if($pre_table){
						$this->AuthCreateTable($pre_table);
					}
					//创建数据表
					//$sql=$this->sql_table($pre_table,$describe);
					//$res=M()->execute($sql);
					//dump($res);die;
                    if($re){
                        $msg=array('code'=>200);
                    }else{
                        $msg=array('code'=>104);
                    }
				}
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	

	/**
	 * 账户修改利用ID获取单条记录
	 * @param $id
     * @return mixed
	 */
	public function record(){
		$id=I('id');
		if(empty($id)){
			$msg=array('code'=>1030,'msg'=>'ERROR','data'=>'未获取到必要的信息');
		}else{
			$db=M('admin','total_');
			$arr=$db->where(array('id'=>$id))->find();
			if($arr){
				$arr['datetime']=date('Y-m-d H:i:s',$arr['datetime']);
				unset($arr['password']);
				$msg=array('code'=>200,'data'=>$arr);
			}else{
				$msg=array('code'=>102);
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 账户修改
	 * @param $username,$pre_table,$describe,$id
     * @return mixed
	 */
	public function modification(){
		$id=I('id');
		$username=I('username');
		$pre_table=I('pre_table');
		$describe=I('describe');
        $pid=I('pid');
		//$appid=I('appid');
		if(empty($username) || empty($pre_table) || empty($describe) || empty($id)){
			$msg=array('code'=>1030,'msg'=>'ERROR','data'=>'未获取到必要的信息');
		}else{
			$data['name']=$username;
			$data['pre_table']=$pre_table;
			$data['describe']=$describe;
            $data['pid']=!empty($pid)?(int)$pid:0;//前端修改以后才能打开
			//$data['wechat_appid']=$appid;
			$redis_db=new A();
			$redis_re=$redis_db->connectredis();
			$redis_re->del('wechat:'.$this->key_admin.':appid');
			$db=M('admin','total_');
            /*子账户判断同名账户start*/
            $child_arr=M('admin_child','total_')->where(array('name'=>$username))->select();
            if(!empty($child_arr)) {
                $msg1 = array('code' => 2001, 'msg' => 'ERROR', 'data' => '用户已存在(子账户)');
                echo returnjson($msg1,$this->returnstyle,$this->callback);exit();
            }
            $child_arr1=$db->where(array('name'=>$username,'id'=>array('neq',$id)))->select();
            if(!empty($child_arr1)) {
                $msg1 = array('code' => 2001, 'msg' => 'ERROR', 'data' => '用户已存在');
                echo returnjson($msg1,$this->returnstyle,$this->callback);exit();
            }
			if($pid){
				$arr4=$db->where(array('id'=>$id))->find();
				if($arr4['pid']==0){
					$arr5=$db->where(array('pid'=>$id))->select();
					if($arr5 && $id != $pid){
						returnjson(array('code'=>1082,'msg'=>"该账号已被关联，请解除关联后重试！"),$this->returnstyle,$this->callback);
					}
				}
				/*
				$arr3=$db->where(array('pid'=>$pid,'id'=>array('neq'=>$id)))->select();
				if($arr3){
					returnjson(array('code'=>1082,'msg'=>"该商户客户端已经被其他商户绑定！"),$this->returnstyle,$this->callback);
				}
				*/
			
			}
            /*子账户判断同名账户end*/
			$re=$db->where('id='.$id)->save($data);
			if($re !== false){
				if($pre_table){
					$this->AuthCreateTable($pre_table);
				}
				$msg=array('code'=>200);
			}else{
				$msg=array('code'=>104);
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	/**
	 * 修改账户密码
	 * @param $password,$new_pwd,$re_pwd,$id
     * @return mixed
	 */
	public function save_pwd(){
		//echo $_SESSION['name']."<br />";
		$id=I('id');
		//print_r($_GET);die;
		if(empty($id)){
			$msg=array('code'=>1030);
		}else{
			$db=M('admin','total_');
			$data['password']=md5('rtmap911');
			$res=$db->where('id='.$id)->save($data);
			//echo $this->key_admin;
			//echo "<h2>".$_SESSION['name']."</h2>";die;
			//dump($res);
			if($res===false){
				$msg=array('code'=>104);
			}else{
				$msg=array('code'=>200);
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	/**
	 * 修改账户状态
	 * @param $enable,$id
     * @return mixed
	 */
	public function enable(){
		$id=I('id');
		$enable=I('enable');
		if(empty($id)){
			$msg=array('code'=>1030,'msg'=>'ERROR','data'=>'未获取到必要的信息');
		}else{
			if($enable!='1' and $enable!='0'){
				$msg=array('code'=>1030,'msg'=>'ERROR','data'=>'未获取到必要的信息');
			}else{
				$data['enable']=$enable;
				$db=M('admin','total_');
				$res=$db->where('id='.$id)->save($data);
				if($res !== false){
					$msg=array('code'=>200);
				}else{
					$msg=array('code'=>104);
				}
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}




    /**
     * 获得所有api类别列表
     * @return mixed
     */
    public function getApiList() {
        $user = M('api_type','total_');
        $apis = $user->select();
        if($apis){
			$msg=array('code'=>200,'data'=>$apis);
		}else{
			$msg=array('code'=>102);
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 获得当前api现有请求keys
     * @param $apiid
     * @return mixed
     */
    public function getRequestKey() {
		$apiid=I('apiid');
        if (!isset($apiid)) {
            $msg=array('code'=>1030,'msg'=>'ERROR','data'=>'未获取到必要的信息');
        }else{
			        // 读取缓存
			$user = M('api_type_key','total_');
			$api_conf = $user->where('api_type_id=' . $apiid)->find();
			$keys = json_decode($api_conf['request_keys'], true);
			if (!empty($keys)) {
				$msg=array('code' => 200, 'data' => $keys);
			}else{
				$msg=array('code' => 102);
			}
		}

        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 配置api的请求keys
     * @param $apiid
     * @return mixed
     */
    public function setRequestKey() {
		$apiid=I('apiid');
		$request_keys=I('keys');
        if (!isset($apiid) || !is_array($request_keys)) {
            $msg=array('code' => 1030, 'msg' => '参数错误');
        }else{
			$user = M('api_type_key','total_');

			$type_arr=$user->where('api_type_id='.$apiid)->find();
			
			$data['request_keys'] = json_encode($request_keys);
			if(empty($type_arr)){
				$data['api_type_id']=$apiid;
				$re = $user->add($data);
			}else{
				$re = $user->where('api_type_id='. $apiid)->save($data);
			}
			if ($re) {
				// 更新缓存
				$msg=array('code' => 200);
			}else{
				$msg=array('code' => 104);
			}
		}
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 获得api返回key
     * @param $apiid
     * @return mixed
     */
    public function getResponseKey() {
		$apiid=I('apiid');
        if (!isset($apiid)) {
            $msg=array('code' => 1030, 'msg' => '参数错误');
        }else{
		
        // 读取缓存

			$user = M('api_type_key','total_');
			$api_conf = $user->where('api_type_id=' . $apiid)->find();
			$keys = json_decode($api_conf['response_keys'], true);
			if (!empty($keys)) {
				$msg=array('code' => 200, 'data' => $keys);
			}else{
				$msg=array('code' => 102);
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 设置api返回key
     * @param $apiid
     * @return mixed
     */
    public function setResponseKey() {
		
		$apiid=I('apiid');
		$response_keys=I('keys');
        if (!isset($apiid) || !is_array($response_keys)) {
            $msg=array('code' => 1030, 'msg' => '参数错误');
        }else{
			$user = M('api_type_key','total_');
			
			$type_arr=$user->where('api_type_id='. $apiid)->find();

			$data['response_keys'] = json_encode($response_keys);
			if(empty($type_arr)){
				$data['api_type_id']=$apiid;
				$re = $user->add($data);
			}else{
				$re = $user->where('api_type_id='. $apiid)->save($data);
			}
			
			if ($re) {
				//更新缓存
				$msg=array('code' => 200);
			}else{
				$msg=array('code' => 104);
			}
		}
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
	

	/**
     * 查询建筑物下的所有接口
     * @param $id
     * @return mixed
     */
	public function apitype(){
		$id=I('id');
		if(!isset($id)){
			$msg=array('code' => 1030);
		}else{
			$db=M('admin','total_');
			$arr=$db->where(array('id'=>$id))->find();
			$pre_table=$arr['pre_table'];
			//dump($pre_table);die;
			if(!isset($pre_table)){
				$msg=array('code' => 113);
			}else{
				$pre_db=M('api',$pre_table);
				$api_type=$pre_db->getField('api_type',true);
				if(empty($api_type)){
					$msg=array('code' => 3000, 'data'=> '暂无配置接口类型');
				}else{
					//print_r($api_type);die;
					if(count($api_type)==1){
						$res['ID']=$api_type[0];
					}else{
						$res['ID']=array('in',$api_type);
					}
					//print_r($res);die;
					$total_api=M('api_type','total_');
					$total_api_name=$total_api->where($res)->select();
					//echo $total_api->_sql();die;
					if($total_api_name){
						$msg=array('code'=>200,'data'=>$total_api_name);
					}else{
						$msg=array('code'=>102);
					}
				}
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	/**
     * 接口配置
     * @param $total_id $id $api_type $request_param_type $request_type $response_data_type $api_url $request_keys $response_keys $is_sign
     * @return mixed
     */
	public function apiconfig(){
		$total_id=I('total_id');//商场ID
		$api_type=I('api_type');//接口类别ID
		//dump($api_type);die;
		$request_param_type=I('request_param_type');
		$request_type=I('request_type');
		$response_data_type=I('response_data_type');
		$api_url=I('api_url');
		$request_keys=I('request_keys');
		$response_keys=I('response_keys');
		$is_sign=I('is_sign');
		$request_data=I('request_data')?I('request_data'):null;
		$header=I('header')?I('header'):null;
		//dump($_GET);die;
			//验证
		if(empty($api_type) || empty($request_param_type) || empty($request_type) || empty($response_data_type) || empty($api_url) || empty($total_id)){
			$msg=array('code' => 1030);
		}else{
			//获取商户表前缀
			$db=M('admin','total_');
			$arr=$db->where(array('id'=>$total_id))->find();
			$pre_table=$arr['pre_table'];
			//实例化商户下的api表
			$pre_db=M('api',$pre_table);
			$data['request_param_type']=strtolower($request_param_type);
			$data['request_type']=strtolower($request_type);
			$data['response_data_type']=strtolower($response_data_type);
			$data['api_url']=$api_url;
			$data['request_data']=$request_data;
			$data['header']=$header;
			//处理请求keys
			$arr=explode("&",$request_keys);
			foreach($arr as $v){
				$res[]=explode("=",$v);
			}
			//处理返回keys
			$arr1=explode("&",$response_keys);
			foreach($arr1 as $v){
				$res1[]=explode("=",$v);
			}
			$data['api_request']=json_encode($res);
			$data['api_response']=json_encode($res1);
			$data['is_sign']=$is_sign;
			//判断是添加还是修改 ID存在则是修改，ID不存在则是添加
				//判断添加时是否这条记录已经存在
				$ret=$pre_db->where(array('api_type'=>$api_type))->find();
				if(empty($ret)){
					$data['api_type']=$api_type;
					$res=$pre_db->add($data);
				}else{
					$res=$pre_db->where(array('id'=>$ret['id']))->save($data);
				}
				//echo $pre_db->_sql();die;
				if($res === false){
					$msg=array('code'=>104);
				}else{
					$msg=array('code'=>200);
				}
			
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	

	/**
     * 获取单条接口配置
     * @param $total_id
	 * @param $id
     * @return mixed
     */
	public function apiconfig_one(){
		$total_id=I('admin_id');
		$id=I('id');
		if(empty($total_id) || empty($id)){
			$msg=array('code' => 1030);
		}else{
			$db=M('admin','total_');
			$arr=$db->where(array('id'=>$total_id))->find();
			$pre_table=$arr['pre_table'];
			if(empty($pre_table)){
				$msg=array('code'=>113);
			}else{
				$pre_db=M('api',$pre_table);
				$res=$pre_db->where(array('api_type'=>$id))->find();

				if($res){
					$api_request=$res['api_request'];
					$api_response=$res['api_response'];
					$res['api_request']=json_decode($api_request,true);	
					$res['api_response']=json_decode($api_response,true);
					$msg=array('code'=>200,'data'=>$res);
				}else{
					$msg=array('code'=>102);
				}
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	/**
     * 刷新商户key_admin
     * @param $id
     * @return mixed
     */
	public function refresh_key(){
		$admin_id=I('admin_id');
		if(empty($admin_id)){
			$msg=array('code'=>1030);
		}else{
		    $username=I('username');
			$redis_db=new A();
			$redis_re=$redis_db->connectredis();
			$redis_re->del('wechat:'.$this->key_admin.':appid');
			$ukey=md5(date("Y-m-d H:i:s",time()).'&'.rand(1000,9999));
			$signkey=md5($username.'&'.date("Y-m-d H:i:s",time()).'&'.rand(1000,9999));
			$data['ukey']=$ukey;
			$data['signkey']=$signkey;
			$admin_db=M('admin','total_');
			$res=$admin_db->where(array('id'=>$admin_id))->save($data);
			if($res){
				$msg=array('code'=>200);
			}else{
				$msg=array('code'=>102);
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	/**
     * 微信第三方授权列表
     * @param $id
     * @return mixed
     */
	public function wei_list(){
		$db=M('third_authorizer_info','total_');
		$page=I('page');
		$lines=I('lines');
		$name=I('name');
		$appid=I('appid');
        if ($name != false){
            $where['nick_name']=array( 'like', '%'.$name.'%');
        }
        if ($appid != false){
            $where['appid']=array('like', $appid);
        }
        $page= false==$page ? 1 : $page;
        $lines= false==$lines ? 10 : $lines;
        $start=($page - 1) * $lines;
        $count=$db->where($where)->count('id');
        if ($count > 0){
            $count_page=ceil($count/$lines);
            $arr=$db->field('id,nick_name,head_img,authorization_info,appid,createtime')->where($where)->limit($start, $lines)->select();
            if($arr){
                $msg=array('code'=>200,'data'=>array('wechatdata'=>$arr,'count'=>$count,'page'=>$page, 'count_page'=>$count_page));
            }
        }else{
			$msg=array('code'=>102);
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	/**
     * 权限列表
     * @param $id
     * @return mixed
     */
	public function jurisdiction_list(){
		$quan_db=M('auth','total_');
		$quan_arr=$quan_db->select();
		if(empty($quan_arr)){
			$msg=array('code'=>102);
		}else{
			$msg=array('code'=>200,'data'=>$quan_arr);
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	/**
     * 权限列表修改
     * @param $id
     * @return mixed
     */
	public function jurisdiction_save(){
	    $id=I('id');
		$column_name=I('column_name');
		$column_api=I('column_api');
		$column_html=I('column_html');
		if(empty($column_name) || empty($column_api) || empty($column_html) || empty($id)){
			$msg=array('code'=>1030);
		}else{
		    $remark = I('remark');
		    
			$quan_db=M('auth','total_');
			$quan_arr=$quan_db->where(array('id'=>$id))->find();
			$data['column_api']=$column_api;
			$data['column_html']=html_entity_decode($column_html);
			$data['column_name']=$column_name;
			if($remark){
			    $data['remark'] = $remark;
			}
		    if($column_html != $quan_arr['column_html']){
		        $html_arr=$quan_db->where(array('column_html'=>$column_html))->find();
		        if($html_arr){
		            returnjson(array('code'=>1008),$this->returnstyle,$this->callback);exit();
		        }
		    }
		    $res=$quan_db->where(array('id'=>$id))->save($data);
		    if($res === false){
		        $msg=array('code'=>104);
		    }else{
		        $msg=array('code'=>200);
		        $data['id']=$id;
		        $often_db = M('auth_often','total_');
		         
		        $res = $often_db->field('id,often_column')->select();
		        
		        foreach($res as $k=>$v){
		            $v['often_column']=json_decode($v['often_column'],true);
		            foreach($v['often_column'] as $key=>$val){   
		                if($id == $val['id']){
		                    $v['often_column'][$key]=$data;
		                    $often_db->where(array('id'=>$v['id']))->save(array('often_column'=>json_encode($v['often_column'])));
		                }
		            }
		        }
		    }
		    
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 权限列表添加
	 * @param $id
	 * @return mixed
	 */
	public function jurisdiction_add(){
	    $column_name=I('column_name');
	    $column_api=I('column_api');
	    $column_html=I('column_html');
	    if(empty($column_name) || empty($column_api) || empty($column_html)){
	        $msg=array('code'=>1030);
	    }else{
	        $quan_db=M('auth','total_');
	        $quan_arr=$quan_db->where(array('column_html'=>$column_html))->find();
	        $data['column_api']=$column_api;
	        $data['column_html']=html_entity_decode($column_html);
	        $data['column_name']=$column_name;
	        if($quan_arr){
	            $msg['code']=1008;
	        }else{
	            $res=$quan_db->add($data);
	            if($res === false){
	                $msg=array('code'=>104);
	            }else{
	                $msg=array('code'=>200);
	            }
	        }
	    }
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	

	/**
     * 获取单个栏目信息或删除
     * @param $id
     * @return mixed
     */
	public function jurisdiction_one(){
		$id=I('id');
		$status=I('status')?strtolower(I('status')):null;
		if(empty($id) || empty($status)){
			$msg=array('code'=>1030);
		}else{
			$quan_db=M('auth','total_');
			if($status == 's'){
				$quan_arr=$quan_db->where(array('id'=>$id))->find();
			}else if($status == 'd'){
				$quan_arr=$quan_db->where(array('id'=>$id))->delete();
			}
			if($quan_arr){
				$msg=array('code'=>200,'data'=>$quan_arr);
				
				if($status == 'd'){
				    $quan_admin_db=M('admin_auth','total_');
				    
				    $quan_admin_db->where(array('auth_id'=>$id))->delete();
				}
				
			}else{
				$msg=array('code'=>104);
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}


	/**
     * 配置商户下的栏目
     * @param $id
     * @return mixed
     */
	public function admin_jurisdiction(){
	    set_time_limit(0);
		$quan_id=urldecode(I('check_auth'));
		//echo $quan_id;die;
		$admin_id=I('admin_id');
		//dump($quan_id);die;
		//echo is_json($quan_id);die;
		if(!is_json($quan_id) || empty($admin_id)){
			$msg=array('code'=>1030);
		}else{
			//$quan_id=json_encode($quan_id);
			$data['check_auth']=$quan_id;
			$quan_admin_db=M('admin_auth','total_');

			$auth_arr = json_decode($quan_id,true);
			
			$auth_data = $quan_admin_db->where(array('admin_id'=>$admin_id))->select();
			
			foreach($auth_arr as $k=>$v){
			    $auth_id[]=$v['id'];
			}
// 			print_r($auth_id);die;
			foreach($auth_data as $k=>$v){
			    if(!in_array($v['auth_id'], $auth_id)){
			        $auth_del = $quan_admin_db->where(array('admin_id'=>$admin_id,'auth_id'=>$v['auth_id']))->delete();
			        
			        if(!$auth_del){
			            echo returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
			        }
			    }
			}
// 			die;
			foreach($auth_arr as $k=>$v){
			    $auth_once = $quan_admin_db->where(array('admin_id'=>$admin_id,'auth_id'=>$v['id']))->find();
			    
			    if($auth_once){
			        $data['show_status'] = $v['column_html'] == ''?2:1;
			        $res = $quan_admin_db->where(array('admin_id'=>$admin_id,'auth_id'=>$v['id']))->save($data);
			        if($res === false){
			            echo returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
			        }
			    }else{
			        $data['admin_id']=$admin_id;
			        $data['auth_id']=$v['id'];
			        $data['is_often']=2;
			        $res = $quan_admin_db->add($data);
			        if(!$res){
			            echo returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
			        }
			    }
			    unset($data);
			}

			//创建微商城相关表
			$merInfo = M('admin','total_')->where(array('id'=>$admin_id))->find();
			if(strstr($quan_id,"icromall")!==false && !empty($merInfo['pre_table'])){ $this->micromall_table($merInfo['pre_table']); }
			if(strstr($quan_id,"valuate")!==false && !empty($merInfo['pre_table'])){ $this->AuthCreateTableEvaluate($merInfo['pre_table']); }
		    $msg['code']=200;
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

    /**
     * 设置子账号栏目
     */
    private function child_jurisdiction($admin_id,$column){
        if(!empty($column)){
            $columnArr = json_decode($column,true);
            $dbs = M('admin_child','total_');
            //获取所有子账号
            $childs = $dbs->where(array('admin_id'=>$admin_id))->select();
            if(!empty($childs)) {
                foreach ($childs as $key=>$val){
                    $temp = array();
                    $check = false;
                    $childColumnArr = json_decode($val['column'],true);
                    if(!empty($childColumnArr)) {
                        foreach($childColumnArr as $j){
                            if(in_array($j,$columnArr)){
                                $temp[]=$j;
                            }else{
                                $check = true;
                            }
                        }
                        //更新
                        if($check){
                            $dbs->where(array('id'=>$val['id']))->save(array('column'=>json_encode($temp)));
                        }
                    }

                }
            }

        }
        return true;

    }


	/**
     * 查询商户下配置的栏目
     * @param $id
     * @return mixed
     */
	public function admin_jurisdiction_list(){
		$admin_id=I('admin_id');
		if(empty($admin_id)){
			$msg=array('code'=>1030);
		}else{
			$db = M('admin_auth','total_');

			$pre = 'total_admin_auth';
            $auth = 'total_auth';
			
			$res = $db->where(array('admin_id'=>$admin_id))->join($auth.' on '.$auth.'.id='.$pre.'.auth_id')->order($pre.'.auth_id asc')->select();
			
			foreach($res as $k=>$v){
			    $data['column_name'] = $v['column_name'];
			    $data['column_api'] = $v['column_api'];
			    $column = $v['show_status'] == 2?'':$v['column_html'];
			    $data['column_html'] = $column; 
			    $data['id'] = $v['id'];
			    $return_arr[]=$data;
			}
// 			print_r($return_arr);
			if($res){
                
				$msg=array('code'=>200,'data'=>$return_arr);
			}else{
				$msg=array('code'=>102);
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
     * 查询商城建筑物id和appid
     * @param $admin_id
     * @return mixed
     */
	public function getBuildAndAppid(){
		$admin_id = I('admin_id');
		if(empty($admin_id)){
			$msg = array('code'=>1030);
		}else{
			$db = M('admin','total_');
// 			$join = ' `total_buildid` on `total_admin`.`id` = `total_buildid`.`adminid`';
// 			$field = 'total_admin.id,total_admin.name,total_admin.describe,total_admin.wechat_appid,total_admin.enable,total_buildid.buildid,total_admin.pre_table';
			$arr = $db->where(array('`total_admin`.`id`'=>$admin_id))->find();
			if($arr){
			    $build_db=M('buildid','total_');
			    $build_arr=$build_db->where(array('adminid'=>array('eq',$admin_id),'is_del'=>array('eq',2)))->field('buildid')->select();
				foreach($build_arr as $k=>$v){
				    $res_arr[]=$v['buildid'];
				}
			    $arr['buildid'] = $res_arr;
				$dbm=M();
				$c=$dbm->execute('SHOW TABLES like "'.$arr['pre_table'].'default"');
				if (1 ==$c){
					$default_db=M('default',$arr['pre_table']);
					$sup_arr=$default_db->where(array('customer_name'=>'subpayacc'))->find();
					$op_arr=$default_db->where(array('customer_name'=>'op'))->find();
					$coupon_arr = $default_db->where(array('customer_name'=>'coupon_default'))->find();
					$arr['op']=$op_arr['function_name'];
					$arr['subpayacc']=$sup_arr['function_name'];
					$arr['coupon'] = $coupon_arr['function_name']?$coupon_arr['function_name']:1;
				}else{
					$arr['op'] = '';
					$arr['subpayacc'] = '';
				}
				$msg=array('code'=>200,'data'=>$arr);
			}else{
				$msg=array('code'=>102);
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
     * 查询商场支付账号
     * @param $admin_id
     * @return mixed
     */
	public function getPayAccount(){
		$admin_id = I('admin_id');
		if(empty($admin_id)){
			$msg = array('code'=>1030);
		}else{
			$db = M('admin','total_');
			$arr = $db->field('pre_table')->where(array('id'=>$admin_id))->find();
			if($arr){
				$dbm=M();
				$c=$dbm->execute('SHOW TABLES like "'.$arr['pre_table'].'default"');
				if (1 !==$c){
					$msg=array('code'=>102);
				}else{
					$childDb = M('default',$arr['pre_table']);
					$arr1 = $childDb->where(array('customer_name'=>'subpayacc'))->find();
					if($arr1){
						$msg=array('code'=>200,'data'=>$arr1);
					}else{
						$msg=array('code'=>102);
					}
				}  
			}else{
				$msg=array('code'=>102);
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
     * 修改商城建筑物id和appid
     * @param $admin_id,$appid,$build_id
     * @return mixed
     */
	public function editBuildAndAppid(){
		$admin_id = I('admin_id');
		$appid = I('appid');
		$payAccount = I('pay_account');
		$build_id = str_replace('，',',',I('build_id'));
		$op_id=I('op_id');
        $applet_appid=@I('applet_appid');
        $alipay_appid=@I('alipay_appid');
        $changjk_id=@I('changjk_id');
        $coupon=I('coupon');
		if(empty($admin_id)){
			$msg = array('code'=>1030);
		}else{
			$db = M('admin','total_');
			$arr = $db->field('id,name,describe,pre_table,ukey')->where(array('id'=>$admin_id))->find();
            RedisService::connectredis()->del('wechat:' . $arr['ukey'] . ':appid');
			if($arr){
				$build_db = M('buildid','total_');
				$build_info = $build_db->where(array('adminid'=>$arr['id']))->save(array('is_del'=>1));
				//更新appid
                $updateArr = array(
                    'wechat_appid'=>$appid,
                    'alipay_appid'=>$alipay_appid,
                    'changjk_id'  =>$changjk_id,
                    'applet_appid'=>$applet_appid
                );
                $this->redis->del('member:'.$arr['ukey']);
				$res = $db->where(array('id'=>$admin_id))->save($updateArr);
				//更新buildid
				$build_id=trim($build_id,',');
				$build_arr=explode(',', $build_id);
				foreach($build_arr as $k=>$v){
				    if($build_db->where(array('adminid'=>array('eq',$arr['id']),'buildid'=>array('eq',$v)))->find()){
				        $build_res = $build_db->where(array('adminid'=>array('eq',$arr['id']),'buildid'=>array('eq',$v)))->save(array('is_del'=>2));
				    }else{
				        $res1=$build_db->add(array('buildid'=>$v,'adminid'=>$admin_id));
				    }
				}
				//删除数据库中之前保存的数据
				$del = $build_db->where(array('buildid'=>array('not in', $build_arr), 'adminid'=>$arr['id']))->delete();



	       		//更新支付账号
				$dbm=M();
				$c=$dbm->execute('SHOW TABLES like "'.$arr['pre_table'].'default"');
				if (1 ==$c){
					$childDb = M('default',$arr['pre_table']);
					$arr1 = $childDb->where(array('customer_name'=>'subpayacc'))->find();
					$arr2 = $childDb->where(array('customer_name'=>'op'))->find();
					$arr3 = $childDb->where(array('customer_name'=>'coupon_default'))->find();
					if($arr1){
						//更新
// 						$res2=$childDb->where(array('customer_name'=>'subpayacc'))->save(array('function_name'=>$payAccount));
					}else{
						//添加
// 						$res2=$childDb->add(array('customer_name'=>"subpayacc",'function_name'=>$payAccount,'description'=>"微信支付子商户号"));
					}
					if($arr2){
					    //更新
					    $res3=$childDb->where(array('customer_name'=>'op'))->save(array('function_name'=>$op_id));
					}else{
					    //添加
					    $res3=$childDb->add(array('customer_name'=>"op",'function_name'=>$op_id,'description'=>"营销平台op"));
					}
					if($arr3){
					    //更新
					    
					    if($arr3['function_name'] != $coupon){
					        
					        $integral_db = M('activity','integral_');
					        $integral_db->where(array('admin_id' => $arr['id']))->delete();
					        
					        $res4=$childDb->where(array('customer_name'=>'coupon_default'))->save(array('function_name'=>$coupon));
					        $this->redis->del('admin:default:one:coupon_default:'.$arr['ukey']);
					    }
					}else{
				        //添加
				        $res4=$childDb->add(array('customer_name'=>"coupon_default",'function_name'=>$coupon,'description'=>"券商城对接配置"));
				    }
				}  
				$msg=array('code'=>200);
			}else{
				$msg=array('code'=>102);
			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
		
	}

	/**
	 * 添加功能
	 */
	public function catalog_insert(){
	    $name=I('name');
	    $url=I('url');
	    if(empty($name) || empty($url)){
	        $msg['code']=1030;
	    }else{
	        $type_id = I('type_id')?I('type_id'):1;
	        $db=M('catalog','total_');
	        $data['name']=$name;
	        $data['status']=1;
	        $data['datetime']=date('Y-m-d H:i:s');
	        $data['url']=$url;
	        $data['type_id'] = $type_id;
	        $arr=$db->add($data);
	        if($arr){
	            $msg['code']=200;    
	        }else{
	            $msg['code']=104;
	        }
	    }
	    
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	/**
	 * 修改功能信息
	 */
	public function catalog_save(){
	    $params['id']=I('id');
	    $params['name']=I('name');
	    $params['url']=I('url');
	    $params['type_id'] = I('type_id')?I('type_id'):1;
	    if(in_array('', $params)){
	        $msg['code']=1030;
	    }else{
	        $params['url']=html_entity_decode($params['url']);
	        $db=M('catalog','total_');
// 	        $arr=$db->where(array('url'=>array('eq',$params['url'])))->find();
// 	        if($arr){
// 	            $msg['code']=1008;
// 	        }else{
	            $data['name']=$params['name'];
	            $data['url']=$params['url'];
	            $arr=$db->where(array('id'=>array('eq',$params['id'])))->save($params);
	            if($arr !== false){
	                $msg['code']=200;
	            }else{
	                $msg['code']=104;
	            }
// 	        }
	    }
	    
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

    /**
     * 获取功能列表(流量)
     */
    public function catalog_list_page(){
        $db=M('catalog','total_');
        $arr=$db->where(array('status'=>1))->select();
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
	/**
	 * 获取功能列表
	 */
	public function catalog_list(){
	    $db=M('catalog','total_');
	    $arr=$db->select();
	    if($arr){
	        $msg['code']=200;
	        $msg['data']=$arr;
	    }else{
	        $msg['code']=102;
	    }
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 启用或禁用某个功能
	 */
	public function catalog_status(){
	    $catalog=I('catalog_id');
	    if(empty($catalog)){
	        $msg['code']=1030;
	    }else{
	        $db=M('catalog','total_');
	        $res=$db->where(array('id'=>array('eq',$catalog)))->find();
	        $status=$res['status']==1?0:1;
	        $data['status']=$status;
	        $arr=$db->where(array('id'=>array('eq',$catalog)))->save($data);
	        if($arr === false){
	            $msg['code']=104;
	        }else{
	            $msg['code']=200;
	        }
	    }
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	/**
	 * 获取某个功能下面的版本
	 */
	public function catalog_version(){
	    $catalog=I('catalog_id');
	    if(empty($catalog)){
	        $msg['code']=1030;
	    }else{
	        $db=M('version','total_');
	        $where['type_id']=array('eq',$catalog);
// 	        $where['status']=array('eq',1);
// 	        $where['_logic']='and';
	        $arr=$db->where($where)->select();
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
	 * 获取单个版本详细信息
	 */
	public function version_once(){
	    $params['version_id']=I('version_id');
	    $params['type_id']=I('type_id');
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	        $db=M('version','total_');
	        $where['type_id']=array('eq',$params['type_id']);
	        $where['id']=array('eq',$params['version_id']);
	        $where['status']=array('eq',1);
	        $where['_logic']='and';
	        $arr=$db->where($where)->find();
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
	 * 修改单个版本资料
	 */
    public function version_once_save(){
        $params['version_id']=I('version_id');
        $params['type_id']=I('type_id');
        $params['name']=I('name');
        $params['url']=I('url');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $params['desc']=I('desc');
            $db=M('version','total_');

                $where['type_id']=array('eq',$params['type_id']);
                $where['id']=array('eq',$params['version_id']);
                $where['status']=array('eq',1);
                $where['_logic']='and';
                $data['name']=$params['name'];
                $data['url']=html_entity_decode($params['url']);
                $data['desc']=$params['desc']?$params['desc']:'';
                $res=$db->where($where)->save($data);
                if($res === false){
                    $msg['code']=104;
                }else{
                    $msg['code']=200;
                }



        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }	
	
	/**
	 * 修改版本状态
	 */
    public function version_once_status(){
        $params['version_id']=I('version_id');
        $params['type_id']=I('type_id');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $db=M('version','total_');
            $res=$db->where(array('id'=>array('eq',$params['version_id'])))->find();
            $status=$res['status']==1?0:1;
            $data['status']=$status;
            $where['type_id']=array('eq',$params['type_id']);
            $where['id']=array('eq',$params['version_id']);
//             $where['status']=array('eq',1);
//             $where['_logic']='and';
            $arr=$db->where($where)->save($data);
            if($arr === false){
                $msg['code']=104;
            }else{
                $msg['code']=200;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
	
	/**
	 * 添加版本
	 */
    public function version_insert(){
        $params['type_id']=I('type_id');
        $params['name']=I('name');
        $params['url']=I('url');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $params['desc']=I('desc');
            
            $params['status']=1;
            $params['datetime']=date('Y-m-d H:i:s');
            
            $db=M('version','total_');
            $where['type_id']=array('eq',$params['type_id']);
            $where['url']=array('eq',$params['url']);
            $where['_logic']='and';
            $arr=$db->where($where)->find();
            if($arr){
                $msg['code']=1008;
            }else{
                $res=$db->add($params);
                if($res){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 获取商户配置的版本信息
     */
    public function catalog_version_list(){
        $type_id=I('type_id');
        $admin_id=I('admin_id');
        if(empty($type_id) || empty($admin_id)){
            $msg['code']=1030;
        }else{
            $db=M('version_url','total_');
            $where['adminid']=array('eq',$admin_id);
            $where['type_id']=array('eq',$type_id);
            $where['_logic']='and';
            $arr=$db->where($where)->find();
            if($arr){
                $version_db=M('version','total_');
                $map['status']=array('eq',1);
                $map['id']=array('eq',$arr['version_id']);
                $map['_logic']='and';
                $version_arr=$version_db->where($map)->find();
                if($version_arr){
                    $total_db = M('admin','total_');
                    $total_arr = $total_db->where(array('id'=>array('eq',$admin_id)))->find();
                    $version_arr['url']=str_replace('{key}',$total_arr['ukey'] , $version_arr['url']);
                    $msg['code']=200;
                    $msg['data']=$version_arr;
                }else{
                    $msg['code']=102;
                }
            }else{
                $msg['code']=102;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 修改商户配置版本接口
     */
    public function admin_version(){
        $params['version_id']=I('version_id');
        $params['adminid']=I('admin_id');
        $params['type_id']=I('catalog_id');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            
            $db=M('version_url','total_');
            
            $where['adminid']=array('eq',$params['adminid']);
            $where['type_id']=array('eq',$params['type_id']);
            $where['_logic']='and';
            
            $arr=$db->where($where)->find();
            if($arr){
                $res=$db->where(array('id'=>array('eq',$arr['id'])))->save($params);
                if($res !== false){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
            }else{
                $res=$db->add($params);
                if($res){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    
    /**
     * 获取开启功能列表
     */
    public function catalog_list_status(){
        $admin_id=I('admin_id');
        if(empty($admin_id)){
            $msg['code']=1030;
        }else{
            $db=M('catalog','total_');
            $arr=$db->where(array('status'=>array('eq',1)))->select();
            $total_db = M('admin','total_');
            $total_arr = $total_db->where(array('id'=>array('eq',$admin_id)))->find();
            if($arr){
                foreach($arr as $k=>$v){
                    $url=str_replace('{key_p}',$v['id'] , $v['url']);
                    $arr[$k]['url']=str_replace('{key_admin}',$total_arr['ukey'] , $url);
//                     $arr[$k]['url']=html_entity_decode($url);
                }
                $msg['code']=200;
                $msg['data']=$arr;
            }else{
                $msg['code']=102;
            }
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 获取某个功能下面的版本
     */
    public function catalog_version_two(){
        $catalog=I('catalog_id');
        $admin_id=I('admin_id');
        if(empty($catalog) || empty($admin_id)){
            $msg['code']=1030;
        }else{
            $db=M('version','total_');
            $where['type_id']=array('eq',$catalog);
	        $where['status']=array('eq',1);
	        $where['_logic']='and';
            $arr=$db->where($where)->select();
            $total_db = M('admin','total_');
            $total_arr = $total_db->where(array('id'=>array('eq',$admin_id)))->find();
            if($arr){
                $msg['code']=200;
                foreach($arr as $k=>$v){
                    $arr[$k]['url']=str_replace('{key}',$total_arr['ukey'] , $v['url']);
                }
                $msg['data']=$arr;
            }else{
                $msg['code']=102;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 获取子栏目列表(流量)
     */
    public function sub_column_list_page(){
        $params['catalog_id']=I('catalog_id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $db=M('sub_column','total_');
            $arr=$db->where(array('status'=>1,'catalog_id'=>array('eq',$params['catalog_id'])))->select();
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
     * 获取子栏目列表
     */
    public function sub_column_list(){
        $params['catalog_id']=I('catalog_id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $db=M('sub_column','total_');
            $arr=$db->where(array('catalog_id'=>array('eq',$params['catalog_id'])))->select();
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
     * 添加子栏目
     */
    public function sub_column_insert(){
        $params['name']=I('name');
        $params['catalog_id']=I('catalog_id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $params['status']=1;
            $params['datetime']=date('Y-m-d H:i:s');
            $db=M('sub_column','total_');
            $arr=$db->add($params);
            if($arr){
                $msg['code']=200;
            }else{
                $msg['code']=104;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 修改子栏目
     */
    public function sub_column_save(){
        $params['name']=I('name');
        $params['id']=I('id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $db=M('sub_column','total_');
            $data['name']=$params['name'];
            $arr=$db->where(array('id'=>array('eq',$params['id'])))->save($data);
            if($arr !== false){
                $msg['code']=200;
            }else{
                $msg['code']=104;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     *修改子栏目状态
     */
    public function sub_column_once(){
        $id=I('id');
        if(empty($id)){
            $msg['code']=1030;
        }else{
            $db=M('sub_column','total_');
            $res=$db->where(array('id'=>array('eq',$id)))->find();
            $status=$res['status']==1?0:1;
            $data['status']=$status;
            $arr=$db->where(array('id'=>array('eq',$id)))->save($data);
            if($arr === false){
                $msg['code']=104;
            }else{
                $msg['code']=200;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
//     /**
//      * 查看功能下开启子栏目
//      */
//     public function version_column_status(){
//         $params['catalog_id']=I('catalog_id');
//         if(in_array('', $params)){
//             $msg['code']=1030;
//         }else{
//             $db=M('sub_column','total_');
//             $where['status']=array('eq',1);
//             $where['catalog_id']=array('eq',$params['catalog_id']);
//             $where['_logic']='and';
//             $arr=$db->where($where)->select();
//             if($arr){
//                 $msg['code']=200;
//                 $msg['data']=$arr;
//             }else{
//                 $msg['code']=102;
//             }
//         }
//         echo returnjson($msg,$this->returnstyle,$this->callback);exit();
//     }
    
    /**
     * 查询版本配置后子栏目列表
     */
    public function version_column_list(){
        $params['catalog_id']=I('catalog_id');
        $params['version_id']=I('version_id');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $db=M('sub_column','total_');
            $map['status']=array('eq',1);
            $map['catalog_id']=array('eq',$params['catalog_id']);
            $arr=$db->where($map)->select();
            if($arr){
                $vers_column_db=M('version_column','total_');
                $where['catalog_id']=array('eq',$params['catalog_id']);
                $where['version_id']=array('eq',$params['version_id']);
                foreach($arr as $k=>$v){
                    $column_id[]=$v['id'];
                }
                $column_str=implode(',', $column_id);
                $where['column_id']=array('in',$column_str);
                $where['_logic']='and';
                $ver_col_arr=$vers_column_db->where($where)->select();
                if($ver_col_arr){
                    foreach($arr as $key=>$val){
                        foreach($ver_col_arr as $k=>$v){
                            if($val['id'] == $v['column_id']){
                                $arr[$key]['url']=$v['url'];
                            }
                        }
                    }
                }else{
                    foreach($arr as $k=>$v){
                        $arr[$k]['url']='';
                    }
                }
//                 print_r($ver_col_arr);die;
                $msg['code']=200;
                $msg['data']=$arr;
            }else{
                $msg['code']=102;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 获取单个子栏目资料
     */
    public function version_column_once(){
        $params['column_id']=I('column_id');
        $params['version_id']=I('version_id');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $db=M('sub_column','total_');
            $arr=$db->where(array('id'=>array('eq',$params['column_id'])))->find();
            $where['version_id']=array('eq',$params['version_id']);
            $where['column_id']=array('eq',$params['column_id']);
            $where['_logic']='and';
            $vers_column_db=M('version_column','total_');
            $ver_col_arr=$vers_column_db->where($where)->find();
            if($ver_col_arr){
                $arr['url']=$ver_col_arr['url'];
            }else{
                $arr['url']='';
            }
            $msg['code']=200;
            $msg['data']=$arr;
        }
    }
    
    /**
     * 修改单个子栏目url
     */
    public function version_column_save(){
        $params['url']=I('url');
        $params['column_id']=I('column_id');
        $params['version_id']=I('version_id');
        $params['catalog_id']=I('catalog_id');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            //             $db=M('sub_column','total_');
            //             $arr=$db->where(array('id'=>array('eq',$params['column_id'])))->find();
            $where['version_id']=array('eq',$params['version_id']);
            $where['column_id']=array('eq',$params['column_id']);
            $where['_logic']='and';
            $vers_column_db=M('version_column','total_');
            //判断子栏目url是否重复
            $siteurl=array(

                "http://res.rtmap.com/ka/build/build.html",
                "https://mem.rtmap.com/Thirdwechat/Wechat/Oauth/",

            );

            $ver_col_arr=$vers_column_db->where($where)->find();

            $wheres['catalog_id']=array('eq',$params['catalog_id']);
            $wheres['id']=array('neq',$ver_col_arr['id']);
            $wheres['_logic']='and';

            $field=array('id','url');
            $datas=$vers_column_db->field($field)->where($wheres)->select();

            $resuts=array();
            foreach ($datas as $resut){
                $dd=explode('?',$resut['url']);
                $resuts[]=$dd[0];
            }

            $urls=explode('?',$params['url']);
            //判断固定的链接或者其他版本是否存在该url
            if(in_array($urls[0],$siteurl)||!in_array($urls[0],$resuts)){
                
                $data['url']=html_entity_decode($params['url']);
                if($ver_col_arr == ''){
                    $res = $vers_column_db->add(array('catalog_id'=>$params['catalog_id'],'version_id'=>$params['version_id'],'column_id'=>$params['column_id'],'url'=>$data['url']));
                }else{
                    $res=$vers_column_db->where($where)->save($data);
                }
                
                if($res !== false){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }

            }else{
                //已经存在
                $msg['code'] = 1008;
            }

        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 删除子栏目url
     */
    public function version_column_del(){
        $params['column_id']=I('column_id');
        $params['version_id']=I('version_id');
        $params['catalog_id']=I('catalog_id');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $where['catalog_id']=array('eq',$params['catalog_id']);
            $where['version_id']=array('eq',$params['version_id']);
            $where['column_id']=array('eq',$params['column_id']);
            $where['_logic']='and';
            $vers_column_db=M('version_column','total_');
            $res=$vers_column_db->where($where)->delete();
            if($res){
                $msg['code']=200;
            }else{
                $msg['code']=104;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    

	public function sql_table($pre_table,$describe){
		$sql="DROP TABLE IF EXISTS  `".$pre_table."api`;
CREATE TABLE `".$pre_table."api` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '索引id',
  `api_type` int(4) NOT NULL COMMENT '接口类型ID',
  `api_request` text NOT NULL COMMENT '请求参数映射',
  `request_type` varchar(20) NOT NULL COMMENT '请求类型  http 或 webservice 或 https',
  `request_param_type` varchar(20) NOT NULL COMMENT '请求方式 post 或get',
  `request_data` text NOT NULL COMMENT '如果是webservice方式，并且是用http方式请求，xml字符串保存在这里',
  `response_data_type` varchar(20) NOT NULL COMMENT '返回数据类型 json 或xml',
  `api_response` varchar(500) NOT NULL COMMENT '返回参数映射',
  `api_url` varchar(100) NOT NULL COMMENT 'api地址',
  `header` varchar(150) DEFAULT '' COMMENT 'header头信息',
  `is_sign` tinyint(1) unsigned DEFAULT '0' COMMENT '是否需要签名，0否，1是',
  `from_id` int(100) NOT NULL DEFAULT '0' COMMENT '哪一个来源的id（total_from表的id）',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='".$describe."接口表'";
	}


	//微商城数据表创建
	public function micromall_table($pre_table){
		//检查表是否存在
		$dbm=M();
		$c=$dbm->execute('SHOW TABLES like "'.$pre_table.'navigation"');
		if (1 ==$c){ return true;}

		//创建表
		$nav_table = $pre_table."navigation";
		$navres_table = $pre_table."nav_resour";
		$sql1 = " CREATE TABLE `$nav_table` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '标识',
		  `name` varchar(50) DEFAULT NULL COMMENT '名称',
		  `status` int(2) NOT NULL DEFAULT '1' COMMENT '状态：\n1、启用\n2、禁用',
		  `position` char(30) NOT NULL COMMENT '位置：\n上：top\n中：center\n下：foot\n左：left\n右：right',
		  `url` varchar(200) NOT NULL COMMENT '跳转地址',
		  `bg_color` varchar(20) NOT NULL DEFAULT '' COMMENT '背景颜色',
		  PRIMARY KEY (`id`)
	  ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8; ";
		$sql2 = "INSERT INTO `$nav_table` VALUES ('1', '顶部广告', '1', 'top', '/tinymall/topad', '');";
		$sql3 = "INSERT INTO `$nav_table` VALUES ('2', '功能区', '1', 'center', '/tinymall/facility', '');";
		$sql4 = "INSERT INTO `$nav_table` VALUES ('3', '底部广告', '1', 'foot', '/tinymall/newbottom', '');";
		$sql5 = "
		CREATE TABLE `$navres_table` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '标识',
		  `name` varchar(50) DEFAULT NULL COMMENT '名字',
		  `link` varchar(200) DEFAULT NULL COMMENT '链接',
		  `property` varchar(60) DEFAULT NULL COMMENT '属性：\n图片地址或者按钮颜色',
		  `author` varchar(50) DEFAULT '' COMMENT '作者',
		  `content` text COMMENT '图文描述',
		  `sort` int(7) DEFAULT NULL COMMENT '排序',
		  `type_id` int(11) DEFAULT NULL COMMENT '导航id',
		  `createtime` datetime NOT NULL COMMENT '添加时间',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
		";
		$dbm->execute($sql1);
		$dbm->execute($sql2);
		$dbm->execute($sql3);
		$dbm->execute($sql4);
		$dbm->execute($sql5);
	}

	//根据不同商户自动创建表结构
	public function AuthCreateTable($pre_table){
		set_time_limit(0);
		//检查表是否存在
		$dbm=M();
		$_sql = file_get_contents('dbfile/auth_table.sql');
		$_arr = explode(';', $_sql);
		foreach ($_arr as $_value) {
			$_value = str_replace('MeradminTablePrefix_',$pre_table,$_value);
			if(!empty($_value)){
				$dbm->execute($_value.';');
			}
		}
		return true;
	}

	//根据不同商户自动创建表结构
	public function AuthCreateTableEvaluate($pre_table){
		set_time_limit(0);
		//检查表是否存在
		$dbm=M();
		$_sql = file_get_contents('dbfile/evaluate_table.sql');
		$_arr = explode(';', $_sql);
		foreach ($_arr as $_value) {
			$_value = str_replace('MeradminTablePrefix_',$pre_table,$_value);
			if(!empty($_value)){
				$dbm->execute($_value.';');
			}
		}
		return true;
	}

	/**
	 * 页面访问量统计数据
	 * http://localhost/member/index.php/DevAdmin/Member/pagepv_data?ukey=202cb962ac59075b964b07152d234b70
	 * @return 
	 */
	public function pagepv_data(){
	    $params['name'] = I('name');
	    $db = M('total_pagepv');

	    //获取访问量数据
	    $relaT = "total_pagepv";
	    $tagsT = "total_admin";
	    $field = "$relaT.num,$relaT.name,$relaT.rote,$relaT.ctime,$relaT.adminid,$tagsT.describe";
	    
	    $arr = array($relaT.'.adminid' => $this->merInfo['id']);
	    if(!empty($params['name']))
	    {
	        $arr[$relaT.'.name'] = $params['name'];
	    }
	    $res = $db->field($field)->join("left join ".$tagsT." on ".$tagsT.".id=".$relaT.".adminid")->where($arr)->order($relaT.'.ctime DESC')->select();
        if(!empty($res)){
	        $data=array('data'=>$res);
	        $msg=array('code'=>200,'data'=>$data);
	    }else{
	        $msg=array('code'=>102);
	    }
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	/***
	*流量数据列表
     **/
	public  function  pagepv_datas(){

        $admin_id = I('admin_id');
        $start_time=I('start_time');
        $end_time=I('end_time');
        $pagename=I('pagename');
        if(empty($admin_id)){
            $msg = array('code'=>1030);
            returnjson($msg, $this->returnstyle, $this->callback);
        }else {
            $db = M('admin', 'total_');
            $admininfo = $db->where(array('`total_admin`.`id`' => $admin_id))->find();
        }
      // $dd= $db->getLastSql();

        $totalpagespv='totalpv-'.$admin_id.'-'.$pagename;
        $totalpages= $this->redis->hget($totalpagespv,$start_time);
        //默认情况下是当天
        if((strtotime($end_time)-strtotime($start_time))<=86400){

            //一天内的数据
            if($totalpages){

                $everydate='pv-'.$admin_id.'-'.$start_time.'-'.$pagename;
                $datapages=array();
                //一天内的数据
              for ($i=0;$i<=23;$i++){

                  $hourpage_times=$this->redis->hget($everydate,$i);

                   if($hourpage_times){

                       $datapages[$i]=$hourpage_times;

                   }else{

                       $datapages[$i]=0;
                   }

                }
                //print_r($datapages);

            }else{

                //返回为空
            }

        }else{
        //大于一天

            $datenums=(strtotime($end_time)-strtotime($start_time))/86400;

            $totalnum=array();
            $dt_start=strtotime($start_time);
            $dt_end=strtotime(date("Y-m-d",(strtotime($end_time) - 3600*24)));
            while ($dt_start<=$dt_end){

                $everydata=date('Y-m-d',$dt_start);
                $totalpv=  $this->redis->hget($totalpagespv,$everydata);

              if($totalpv){

                  $totalnum[$everydata]=$totalpv;
              }else{

                  $totalnum[$everydata]=0;
              }

                $dt_start = strtotime('+1 day',$dt_start);
            }
            $total=array('2017-02-01'=>10,'2017-02-03'=>5,'2017-02-05'=>13);
            ($totalnum) ;
             //print_r($totalnum);
            exit;

        }

    }
    /**
    *数据流量列表及其图标
     * @param admin_id 商户的ukey
     * @param order 流量排序
     *
     **/
    public function pvPage(){


        $start_time=I('start_time');
        $end_time=I('end_time');
        $order=I('order');
        $admininfo = $this->merInfo;
        if(!$admininfo) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
            exit;
        }
        $this->admin_id=$admininfo['id'];
        $data=$this->pvPageData($admininfo['id'],$start_time,$end_time);
        //排序

        if($order=='asc'){

            $dd=$this->my_array_multisort($data,'pv');
        }elseif ($order=='desc'){

            $dd=$this->my_array_multisort($data,'pv',SORT_DESC);


        }else{

            $dd=$this->my_array_multisort($data,'pv',SORT_DESC);
        }

        //返回
        $msg['code'] = 200;
        $msg['data'] = $dd;


        returnjson($msg,$this->returnstyle,$this->callback);exit();

    }
    /**
    *流量数据导出（图标和列表）
     *
     * @param key_admin 商户key
     * @param start_time 开始时间
     * @param
     */
    public  function  pvPageExport(){

        $start_time=I('start_time');
        $end_time=I('end_time');
        $order=I('order');

        $admininfo = $this->merInfo;
        if(!$admininfo) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
            exit;
        }
        $this->admin_id=$admininfo['id'];
        $data=$this->pvPageData($admininfo['id'],$start_time,$end_time);

        //排序

        if($order=='asc'){

            $dd=$this->my_array_multisort($data,'pv');
        }elseif ($order=='desc'){

            $dd=$this->my_array_multisort($data,'pv',SORT_DESC);


        }else{

            $dd=$this->my_array_multisort($data,'pv',SORT_DESC);
        }
        //导出
       $title=array('页面名称','总访问量','更新时间');

       $datas=array();

       foreach ($dd as $result){

           $datas[]=array(

               'name'=>$result['name'],
               'pv'  =>$result['pv'],
               'updatetime'=>$result['updatetime'],
           );
       }

        vendor("Csv.Csv");
        $cvs =new  \Csv();
        $cvs->put_csv($datas,$title);
    }

    /**
    *流量排序
     ***/
    public  function orderPage($data,$field,$order=""){

        if($order=='asc'){

            $dd=$this->my_array_multisort($data,$field);
        }elseif ($order=='desc'){

            $dd=$this->my_array_multisort($data,'pv',SORT_DESC);

        }else{

            $dd=$data;
        }

        return $dd;
    }

    /**
    *流量统计列表及其图标数据
     * @param  admin_id 商户的id
     * @param start_time 开始时间
     * @param end_time 结束时间
     ***/
    public  function pvPageData($admin_id,$start_time,$end_time){

        if(empty($start_time)||empty($end_time)){
            //今天

            $day=date('Y-m-d', time());
            $data=$this->pagepv_oneday($day);

        }else{

            if((strtotime($end_time)-strtotime($start_time))<=0){
            //一天内的数据

                $data=$this->pagepv_oneday($start_time);
            }else{

               $data= $this->pvSumData($start_time,$end_time,$admin_id);
            }
        }
        return $data;

    }
    /**
     *多天内流量的详细信息
     * @param $start_time 开始时间
     * @param $end_time 结束时间
     * @param $admin_id 商家id
     * @return 几天内的流量完整信息
     */
    public  function pvSumData($start_time,$end_time,$admin_id){

        $db_column=M('catalog','total_');

        $result_colmun = $db_column->where(array('status'=>1))->select();

        $db_cata=$this->getPageName();
        $pv=array();

        foreach ($result_colmun as $result ){

            $times=$this->pvdate($admin_id,$db_cata[$result['id']]);
            if(!empty($result['page_mark'])) {
                $pv[] = array(

                    'name' => $result['name'],
                    'id' => $result['id'],
                    'pv' => $this->pvSumPages($start_time, $end_time, $admin_id, $db_cata[$result['id']]),
                    'updatetime' => $times,
                );
            }

        }

        return $pv;

    }
    /**
    *一个栏目多天的流量总数
     * @param $start_time 开始日期
     * @param $end_time  结束日期
     * @param $admin_id 商家id
     * @param $page 一级菜单页面标识
     *
     * @return 一个栏目几天内总的访问量
     */

    public  function pvSumPages($start_time,$end_time,$admin_id,$page){

        $dt_start=strtotime($start_time);
        $dt_end=strtotime($end_time);
        //计算时间内的数据
        $total=0;
        while ($dt_start<=$dt_end){

            $everydata=date('Y-m-d',$dt_start);
            $sum=$this->columnPagesToday($admin_id,$page,$everydata);

            $total +=$sum;

            $dt_start = strtotime('+1 day',$dt_start);
        }
        return $total;
    }
    /**
     *获取标识
     **/
    public function  getPageName(){


        $db=M('catalog','total_');

        $filed=array('id','page_mark');
        $data=$db->field($filed)->select();

        $datas =array();
        foreach ($data as $result){

            $datas[$result['id']] = $result['page_mark'];
        }

       return $datas;
    }
    /**
    *一天内的流量数据
     * @param $day 一天
     **/
    public  function  pagepv_oneday($day){

        $admin_id = $this->admin_id;
        $db_column=M('catalog','total_');

        $result_colmun = $db_column->where(array('status'=>1))->select();
        //获取标识
        $db_cata=$this->getPageName();

        $pv=array();
        foreach ($result_colmun as $result){

         $sum=$this->columnPagesToday($admin_id,$db_cata[$result['id']],$day);
         $times=$this->pvdate($admin_id,$db_cata[$result['id']]);

          if(!empty($result['page_mark'])) {
             $pv[] = array(

                 'name' => $result['name'],
                 'id' => $result['id'],
                 'pv' => $sum,
                 'updatetime' => $times,
             );
          }

        }

      return $pv;

    }


    /**
    *一天内的某个栏目的流量数字
     * @param admin_id 商家id
     * @param  hours  具体的一天
     * @param page 具体的某个栏目一级栏目页面标识
     *
     */
    public  function columnPagesToday($admin_id,$page,$day){

        //一天内的流量
        $db = M('total_pagepv');
        if(empty($page)){

            return 0;
        }
        $start=date('Y-m-d H:i:s',strtotime($day));
        $end=$day.' :23:59:59';
        $where=array('rote'=>$page,'adminid'=>$admin_id);

        $where['ctime']=array(array('egt',$start),array('lt',$end),);
        $data=$db->field('num')->where($where)->select();


        if(!$data){

            return 0;
        }

        $total=0;
        foreach ($data as $result){
            $total += $result['num'];
        }

        return $total;

    }
    /**
     * 单个栏目更新的最新时间
     *@param $id 一级栏目id
     *page 页面标识
     ***/
    public function pvdate($admin_id,$page){

        $db = M('total_pagepv');

        $data=$db->field('ctime')->where(array('rote'=>$page))->order("id desc")->limit(1)->select();

        return $data[0]['ctime'];

    }

    /**
     *某个栏目详细页面流量数据
     * @param key_admin  商户key
     * @param $column_id  栏目id
     * @param $start_time 开始时间
     * @param $end_time  结束时间
     * @order 排序
     **/
    public function detailPagePv(){



        $column_id=I('column_id');
        $start_time=I('start_time');
        $end_time=I('end_time');
        $order=I('order');
        $orderdate=I('orderdate');
        if(empty($column_id)){

            $msg['code']=1030;
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        $admininfo = $this->merInfo;
        if(!$admininfo) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
            exit;
        }
        $this->admin_id=$admininfo['id'];

        $data=$this->detailPagePvData($column_id,$start_time,$end_time);

        if($order=='asc'){
            //升序
            asort($data);
        }elseif ($order=='desc'){

            arsort($data);
        }
        //按日期排序
       // ksort($data);
        if($orderdate=='desc'){
            //降序
            krsort($data);
        }
        $data=$this->pvPageSet($data);
        $msg['code'] = 200;
        $msg['data'] = $data;


        returnjson($msg,$this->returnstyle,$this->callback);exit();



    }
    /**
     *数组拼接
     */
    public  function pvPageSet($arr){

        $result=array();
        foreach ($arr as $key  =>$data){

            $result[]=array(

                'date'=>$key,
                'pv'  =>$data
            );

        }
        return $result;
    }

    /**
     *某个栏目的子页面数据（数据导出）
     * @param key_admin 商户key
     * @param $column_id栏目id
     * @param $start_time 开始时间
     * @param $end_time  结束时间
     * @order 排序
     **/
    public function detailPagePvExport(){


        $column_id=I('column_id');
        $start_time=I('start_time');
        $end_time=I('end_time');
        $order=I('order');

        if(empty($column_id)){

            $msg['code']=1030;
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }

        $admininfo = $this->merInfo;
        if(!$admininfo) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
            exit;
        }
        $this->admin_id=$admininfo['id'];
        $data=$this->detailPagePvData($column_id,$start_time,$end_time);
        //排序

        $data=$this->pvPageSet($data);
        $title=array('更新时间','总访问量');
        //导出
        vendor("Csv.Csv");
        $cvs =new  \Csv();
        $cvs->put_csv($data,$title);

    }

    /**
    *某个栏目的数据
     * @param $column_id栏目id
     * @param $start_time 开始时间
     * @param $end_time  结束时间
     * @return array 返回一天内流量或者几天的流量数据
     **/
    public function detailPagePvData($column_id,$start_time,$end_time){

        if(empty($start_time)||empty($end_time)){

            //当天24小时内
            $date=date('Y-m-d', time());
            $datas =array();
            //24小时内 00-23
            for ($i=0;$i<=23;$i++){

                $hour= $i<10?'0'.$i:$i;
                $keys=$date.' '.$hour.''.':00:00';
               $datas[$keys]=$this->pagesDataHour($date,$hour,$column_id);
            }
            $data=$datas;
        }else{

            if((strtotime($end_time)-strtotime($start_time))<=0){
                //某一天24小时内

                for ($i=0;$i<=23;$i++){

                    $hour= $i<10?'0'.$i:$i;
                    $keys=$start_time.' '.$hour.':00:00';
                    $datas[$keys]=$this->pagesDataHour($start_time,$hour,$column_id);
                }
                $data=$datas;

            }else{
                //按天计算

                $data =$this->getPagesDaysData($start_time,$end_time,$column_id);

            }

        }

        return $data;

    }
    /***
    *一个栏目不同天的流量数据
     **/
    public function  getPagesDaysData($start_time,$end_time,$column_id){


        $dt_start=strtotime($start_time);
        $dt_end=strtotime($end_time);
        //计算时间内的数据
        $pagename=$this->getPageName();
        $datas=array();
        while ($dt_start<=$dt_end){

            $everydata=date('Y-m-d',$dt_start);
            $sum=$this->columnPagesToday($this->admin_id,$pagename[$column_id],$everydata);

            $datas[$everydata]=$sum;

            $dt_start = strtotime('+1 day',$dt_start);
        }

        return $datas;


    }

    /**
    *获得一小时内某个栏目的数据
     * @param $day 每天 2018-02-23
     * @param $hour 小时 11
     * @param $column_id 一级菜单id
     * @return int  一天内，某也栏目在某个小时流量数
     **/
    public function pagesDataHour($day,$hour,$column_id){


        $admin_id=$this->admin_id;
        //获得页面标识
        $db=M('catalog','total_');
        $data=$db->field('page_mark')->where(array('id'=>$column_id))->find();

        if(!$data){
            return 0;
        }

        $start=$day.' '.$hour.':00:00';
        $end=$day.' '.$hour.':59:59';

        $db = M('total_pagepv');
        $where=array('rote'=>$data['page_mark'],'adminid'=>$this->admin_id);

        $where['ctime']=array(array('egt',$start),array('elt',$end));
        $data=$db->field('num')->where($where)->select();

        if(!$data){

            return 0;
        }

        $total=0;
        foreach ($data as $result){
            $total += $result['num'];
        }

        return $total;

    }

    /***
     *数组排序
     *
     * @param $data 数组
     * @param $field 排序的字段
     * @param $sort_order 顺序
     ***/
   public function my_array_multisort($data,$field,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC){
        foreach($data as $val){
            $key_arrays[]=$val[$field];
        }
        //print_r($key_arrays);exit;
        array_multisort($key_arrays,$sort_order,$sort_type,$data);
        return $data;
}



    /**
	 * 页面访问量统计信息表
	 * http://localhost/member/index.php/DevAdmin/Member/pagepv_list?ukey=202cb962ac59075b964b07152d234b70
	 * @return
	 */
	public function pagepv_list(){
	    $db = M('total_pagepv_list');

	    //获取所有评价标签
	    $res = $db->where('')->select();
	    if(!empty($res)){
	        $data=array('data'=>$res);
	        $msg=array('code'=>200,'data'=>$data);
	    }else{
	        $msg=array('code'=>102);
	    }
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	/**
	 * 插入页面访问量统计信息表
	 * http://localhost/member/index.php/DevAdmin/Member/pagepv_list_add?ukey=202cb962ac59075b964b07152d234b70&name=天河城&rote=thca
	 * @return
	 */
	public function pagepv_list_add(){
	    $params['id'] = I('id');
	    $params['name'] = I('name');
	    $params['rote'] = I('rote');
        
	    $db = M('total_pagepv_list');
	    
        if(empty($params['id'])){
            $condition['name'] = $params['name'];
            $condition['rote'] = $params['rote'];
            $condition['_logic'] = 'OR';
            $data = $db->where($condition)->select();

            //重复添加
            if(!empty($data))
            {
                $msg['code'] = 1008;
                returnjson($msg,$this->returnstyle,$this->callback);exit();
            }
            else
            {
                //添加
                $inster = array('name' => $params['name'], 'rote' => $params['rote'], 'ctime' => date('Y-m-d H:i;s', time()));
                $db->add($inster);
            }    
        }
        else
        {
            //编辑
            $upDate = array('name' => $params['name'], 'rote' => $params['rote']);
            $db->where(array('id'=>$params['id']))->save($upDate);
            
            $lastid = $params['id'];
        }

        $msg['code'] = 200;
        $msg['data'] = $lastid;

        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	/**
	*更新页面访问量统计信息表（一级菜单）
     */
    public function pagepv_lists_edit(){

        $params['id'] = I('id');
        $params['name'] = I('name');
        $params['rote'] = I('rote');
        if(in_array('', $params)){
            $msg['code']=1030;
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        $db=M('catalog','total_');

        //判断该字段在二级菜单中是否存在
        $db_column=M('sub_column','total_');
        $conditions['second_page_mark'] = $params['rote'];
        $result_colmun = $db_column->where($conditions)->select();
        if(!empty($result_colmun)){
            $msg['code'] = 1008;
            returnjson($msg, $this->returnstyle, $this->callback);
            exit();
        }
        $condition['page_mark'] = $params['rote'];
        $result = $db->where($condition)->select();
        if(!empty($result)&&($params['id']!=$result[0]['id'])){

            $msg['code'] = 1008;
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }else {

            $upDate = array('page_mark' => $params['rote']);
            $db->where(array('id' => $params['id']))->save($upDate);

            $lastid = $params['id'];

            $msg['code'] = 200;
            $msg['data'] = $lastid;

            returnjson($msg, $this->returnstyle, $this->callback);
            exit();
        }

    }

    /**
     *插入页面访问量统计信息表（二级菜单）
     */
    public function pagepv_second_edit(){

        $params['id'] = I('id');
        $params['name'] = I('name');
        $params['rote'] = I('rote');
        $db=M('sub_column','total_');
        if(in_array('', $params)){
            $msg['code']=1030;
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        //判断一级菜单是否存在该字段
        $db_cata=M('catalog','total_');
        $conditions['page_mark'] = $params['rote'];
        $result_cata = $db_cata->where($conditions)->select();
        if(!empty($result_cata)){
            $msg['code']=1008;
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }

        // $condition['id'] = $params['name'];
        $condition['second_page_mark'] = $params['rote'];

        $result = $db->where($condition)->select();
        if(!empty($result)&&$result[0]['id']!=$params['id']){

            $msg['code'] = 1008;
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }else {

            $upDate = array('second_page_mark' => $params['rote']);
            $db->where(array('id' => $params['id']))->save($upDate);

            $lastid = $params['id'];

            $msg['code'] = 200;
            $msg['data'] = $lastid;

            returnjson($msg, $this->returnstyle, $this->callback);
            exit();
        }


    }


	
	//删除
	public function pagepv_list_del(){
	    $params['id'] = I('id');
	    if(!empty($params['id'])){
	        //删除
	        $db = M('total_pagepv_list');
	        $db->where(array('id' => $params['id']))->delete();
	    }
	    $msg['code'] = 200;
	    $msg['data'] = $params;

	    returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

    /*
 *获取停车缴费设置
 */
    public function GetParkConf()
    {
        $admin_id = I('admin_id');
        if(empty($admin_id)){
            $msg = array('code'=>1030);
            returnjson(array('code'=>200), $this->returnstyle, $this->callback);
        }else {
            $db = M('admin', 'total_');
            $admininfo = $db->where(array('`total_admin`.`id`' => $admin_id))->find();
        }
        $park_pay_config=$this->GetOneAmindefault($admininfo['pre_table'],$admininfo['ukey'], 'park_pay_config');
        $park_pay_config = !empty($park_pay_config['function_name'])?json_decode($park_pay_config['function_name'],true):"";

        $public_pay_config = $this->GetOneAmindefault($admininfo['pre_table'], $admininfo['ukey'], 'public_pay_config');
        $public_pay_config = !empty($public_pay_config['function_name']) ? json_decode($public_pay_config['function_name'], true) : '';
        $other_pay_config = $this->GetOneAmindefault($admininfo['pre_table'], $admininfo['ukey'], 'other_pay_config');
        $other_pay_config = !empty($other_pay_config['function_name']) ? json_decode($other_pay_config['function_name'], true) : '';
       // print_r($admininfo);exit;
        //删除缓存
        $arr=array('wechat-pay-public','wechat-pay-public-classname','wechat-pay-park','wechat-pay-park-classname','crmclassname','crmmarketid','crmkeyandsecret','erpclassname','parkclassname');
        foreach ($arr as $items){
            $this->redis->del("admin:default:one:".$items.":".$admininfo['ukey']);
        }

        $db = M('default', $admininfo['pre_table']);
        $arr=array("customer_name","function_name");
        $sel=$db->field($arr)->select();
        $datas=array();
        foreach ($sel as $item){
            $datas[$item['customer_name']]=$item['function_name'];
        }
        //更改前端输入值
        $datas['payplatform_type']=isset($datas['wechat-pay-public'])?$datas['wechat-pay-public']:"";
        $datas['payplatform_pay_classname']=isset($datas['wechat-pay-public-classname'])?$datas['wechat-pay-public-classname']:"";
        $datas['platform_pay_park']=isset($datas['wechat-pay-park'])?$datas['wechat-pay-park']:"";
        $datas['platform_pay_park_classname']=isset($datas['wechat-pay-park-classname'])?$datas['wechat-pay-park-classname']:"";
        unset($datas['wechat-pay-public'],$datas['wechat-pay-park'],$datas['wechat-pay-public-classname'],$datas['wechat-pay-park-classname']);
         //print_r($datas);exit;
        returnjson(array('code'=>200,'data'=>array('public_pay_config'=>$public_pay_config, 'park_pay_config'=>$park_pay_config,'other_pay_config'=>$other_pay_config,'public_default'=>$datas)), $this->returnstyle, $this->callback);
    }

    /*
     *设置停车缴费配置
     */
    public function SetParkConf()
    {
        $params = I('param.');
        if(empty($params['admin_id'])){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }else {
            $db = M('admin', 'total_');
            $admininfo = $db->where(array('`total_admin`.`id`' => $params['admin_id']))->find();
        }
        $db = M('default', $admininfo['pre_table']);
        $sel=$db->field('customer_name')->select();
        $customer_names = array_column($sel, 'customer_name');
        $arr = array("mchid"=>@$params['mchid'],"signkey"=>@$params['signkey'],"ismacc"=>@$params['ismacc']);//停车支付配置
        $public = array("publicmchid"=>@$params['publicmchid'],"publicsignkey"=>@$params['publicsignkey'],"publicismacc"=>@$params['publicismacc']);//公共支付配置
        $other11 = array("othermchid"=>@$params['othermchid'],"othersignkey"=>@$params['othersignkey'],"otherismacc"=>@$params['otherismacc'],"otherplatform"=>@$params['otherplatform'],"otherpfclass"=>@$params['otherpfclass']);//其他支付配置
        //公共支付方式
        if (in_array('public_pay_config', $customer_names)) {
            $save=$db->where(array('customer_name'=>'public_pay_config'))->save(array('function_name'=>json_encode($public)));
        }else{
            $save=$db->add(array('customer_name'=>'public_pay_config','function_name'=>json_encode($public),'description'=>"微信支付配置"));
        }
        $this->redis->del("admin:default:one:park_pay_config:".$admininfo['ukey']);
        //停车支付方式配置
        if (in_array('park_pay_config', $customer_names)) {
            $save=$db->where(array('customer_name'=>'park_pay_config'))->save(array('function_name'=>json_encode($arr)));
        }else{
            $save=$db->add(array('customer_name'=>'park_pay_config','function_name'=>json_encode($arr),'description'=>"微信支付配置"));
        }
        $this->redis->del("admin:default:one:other_pay_config:".$admininfo['ukey']);
        //其他支付方式配置
        if (in_array('other_pay_config', $customer_names)) {
            $save=$db->where(array('customer_name'=>'other_pay_config'))->save(array('function_name'=>json_encode($other11)));
        }else{
            $save=$db->add(array('customer_name'=>'other_pay_config','function_name'=>json_encode($other11),'description'=>"其他支付配置"));
        }

        //设置支付类型
        if(@$params['payplatform']!='0'&&@$params['payplatform']!='1'){

          $params['payplatform']="";
        }
        if(in_array('wechat-pay-public',$customer_names)){
            $save=$db->where(array('customer_name'=>'wechat-pay-public'))->save(array('function_name'=>@$params['payplatform']));

        }else{
            $save=$db->add(array('customer_name'=>'wechat-pay-public','function_name'=>@$params['payplatform'],'description'=>"支付类型"));
        }

        $this->redis->del("admin:default:one:wechat-pay-public:".$admininfo['ukey']);
        //第三方支付调用的类
        if(@$params['payplatform']=='1'){

            if(!isset($params['thirdpaymentclass'])){
                $params['thirdpaymentclass']="";
            }
            if(in_array('wechat-pay-public-classname',$customer_names)){
                $save=$db->where(array('customer_name'=>'wechat-pay-public-classname'))->save(array('function_name'=>@$params['thirdpaymentclass']));

            }else{
                $save=$db->add(array('customer_name'=>'wechat-pay-public-classname','function_name'=>@$params['thirdpaymentclass'],'description'=>"第三方支付调用的类"));
            }
            $this->redis->del("admin:default:one:wechat-pay-public-classname:".$admininfo['ukey']);
        }
        //停车支付类型
        if(@$params['parkingpayplatform']!='0'&&@$params['parkingpayplatform']!='1'){

            $params['parkingpayplatform']="";
        }
        if(in_array('wechat-pay-park',$customer_names)){
            $save=$db->where(array('customer_name'=>'wechat-pay-park'))->save(array('function_name'=>@$params['parkingpayplatform']));

        }else{
            $save=$db->add(array('customer_name'=>'wechat-pay-park','function_name'=>@$params['parkingpayplatform'],'description'=>"停车支付类型"));
        }
        $this->redis->del("admin:default:one:wechat-pay-park:".$admininfo['ukey']);
        //停车第三方支付类
        if(@$params['parkingpayplatform']=='1'){

            if(!isset($params['parkpayclass'])){
                $params['parkpayclass']="" ;
            }
            if(in_array('wechat-pay-park-classname',$customer_names)){
                $save=$db->where(array('customer_name'=>'wechat-pay-park-classname'))->save(array('function_name'=>@$params['parkpayclass']));

            }else{
                $save=$db->add(array('customer_name'=>'wechat-pay-park-classname','function_name'=>@$params['parkpayclass'],'description'=>"停车支付调用的类"));
            }
            $this->redis->del("admin:default:one:wechat-pay-park-classname:".$admininfo['ukey']);
        }

        //CRM类名
        if(!isset($params['crmclassname'])){
            $params['crmclassname']="";
        }
        if(in_array('crmclassname',$customer_names)){
            $save=$db->where(array('customer_name'=>'crmclassname'))->save(array('function_name'=>@$params['crmclassname']));

        }else{
            $save=$db->add(array('customer_name'=>'crmclassname','function_name'=>@$params['crmclassname'],'description'=>"CRM类名"));
        }
        $this->redis->del("admin:default:one:crmclassname:".$admininfo['ukey']);
        //CRM的市场ID设置
        if(!isset($params['crmmarketid'])){
            $params['crmmarketid']="";
        }
        if(in_array('crmmarketid',$customer_names)){
            $save=$db->where(array('customer_name'=>'crmmarketid'))->save(array('function_name'=>@$params['crmmarketid']));

        }else{
            $save=$db->add(array('customer_name'=>'crmmarketid','function_name'=>@$params['crmmarketid'],'description'=>"CRM的Mrket"));
        }
        $this->redis->del("admin:default:one:crmmarketid:".$admininfo['ukey']);
        //CRM的签名和秘钥
        if(!isset($params['crm_signature'])){
            $params['crm_signature']="";
        }
        if(!isset($params['crm_secretkey'])){
            $params['crm_secretkey']="";
        }
        $secretkeys=array('key'=>@$params['crm_signature'],'secret'=>@$params['crm_secretkey']);
        if(in_array('crmkeyandsecret',$customer_names)){
            $save=$db->where(array('customer_name'=>'crmkeyandsecret'))->save(array('function_name'=>json_encode($secretkeys)));

        }else{
            $save=$db->add(array('customer_name'=>'crmkeyandsecret','function_name'=>json_encode($secretkeys),'description'=>"CRM的签名和秘钥"));
        }
        $this->redis->del("admin:default:one:crmkeyandsecret:".$admininfo['ukey']);
        //ERP操作
        if(!isset($params['erpclassname'])){
            $params['erpclassname']="";
        }
        if(in_array('erpclassname',$customer_names)){
            $save=$db->where(array('customer_name'=>'erpclassname'))->save(array('function_name'=>@$params['erpclassname']));

        }else{
            $save=$db->add(array('customer_name'=>'erpclassname','function_name'=>@$params['erpclassname'],'description'=>"ERP类"));
        }
        $this->redis->del("admin:default:one:erpclassname:".$admininfo['ukey']);
        //停车对接类
        if(!isset($params['parkclassname'])){
            $params['parkclassname']="";
        }
        if(in_array('parkclassname',$customer_names)){
            $save=$db->where(array('customer_name'=>'parkclassname'))->save(array('function_name'=>@$params['parkclassname']));

        }else{
            $save=$db->add(array('customer_name'=>'parkclassname','function_name'=>@$params['parkclassname'],'description'=>"停车对接类"));
        }
        $this->redis->del("admin:default:one:parkclassname:".$admininfo['ukey']);
        $this->redis->del("admin:default:one:public_pay_config:".$admininfo['ukey']);
        //其他支付

        returnjson(array('code'=>200), $this->returnstyle, $this->callback);
    }

    /**
     * 获取子栏目列表(左侧菜单最多二级)sdfsdf
     * @param $pid
     * @return mixed
     */
    public function jurisdiction_list_child(){
        $pid=I('pid');
        if(empty($pid)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $menu_db=M('auth_child','total_');
        $menu_arr=$menu_db->where(array('pid'=>$pid))->order("sid asc,`order` asc")->select();
        $menu_arr_new = array();
        if($menu_arr) {
            foreach ($menu_arr as $key => $val) {
                if ($val['sid'] == 0) {
                    $val['child'] = array();
                    $menu_arr_new[$val['id']] = $val;
                } else {
                    $menu_arr_new[$val['sid']]['child'][] = $val;
                }
            }
            $menu_arr_new = object_to_list($menu_arr_new);
        }
        if(empty($menu_arr_new)){
            $msg=array('code'=>102);
        }else{
            $auth_db=M('auth','total_');
            $auth_arr=$auth_db->where(array('id'=>$pid))->find();
            $msg=array('code'=>200,'data'=>array('column'=>$auth_arr,'menu'=>$menu_arr_new));
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * 编辑或添加菜单
     * @param array
     * @return mixed
     */
    public function jurisdiction_save_child(){
        $param=I('param.');
        $this->emptyCheck($param,array('pid','menu_name','icon_type','link_type'));
        $param['sid'] = empty($param['sid'])?0:(int)$param['sid'];
        $menu_db=M('auth_child','total_');
        if(!empty($param['id'])){
            //编辑菜单a
            $edit_arr = array(
                'menu_name'=> $param['menu_name'],
                'menu_icon'=> $param['menu_icon'],
                'icon_type'=> $param['icon_type'],
                'menu_link'=> html_entity_decode($param['menu_link']),
                'link_type'=> $param['link_type'],
                'sid'      => $param['sid'],
            );
            $check=$menu_db->where(array('id'=>$param['id']))->save($edit_arr);
        }else{
            //添加菜单
            $ts = time();
            $add_arr = array(
                'pid'      => $param['pid'],
                'menu_name'=> $param['menu_name'],
                'menu_icon'=> $param['menu_icon'],
                'icon_type'=> $param['icon_type'],
                'menu_link'=> html_entity_decode($param['menu_link']),
                'link_type'=> $param['link_type'],
                'sid'      => $param['sid'],
                'order'    => $ts
                );
            $check=$menu_db->add($add_arr);
        }
        returnjson(array('code'=>200),$this->returnstyle,$this->callback);
    }

    /**
     * 删除菜单
     * @param array
     * @return mixed
     */
    public function jurisdiction_del_child(){
        $param=I('param.');
        $this->emptyCheck($param,array('id'));
        $id = $param['id'];
        $menu_db=M('auth_child','total_');
        $arr = $menu_db->where(array('id'=>$id))->find();
        $menu_db->where(array('id'=>$id))->delete();
        if($arr['sid']==0){
            $menu_db->where(array('sid'=>$arr['id']))->delete();
        }
        returnjson(array('code'=>200),$this->returnstyle,$this->callback);
    }

    /**
     * 上下移动
     * @param array//id,direction:up,down
     * @return mixed
     */
    public function jurisdiction_move_child(){
        $param=I('param.');
        $this->emptyCheck($param,array('id','direction'));
        $menu_db=M('auth_child','total_');
        $re=$menu_db->where(array('id'=>$param['id']))->find();
        if(!$re){
            returnjson(array('code'=>1082,'msg'=>"未找到记录"),$this->returnstyle,$this->callback);
        }
        //查询将要移动的记录
        list($order,$operator) = $param['direction']=="down"?array("asc","gt"):array("desc","lt");
            $where = array('pid'=>$re['pid'],'sid'=>$re['sid'],'order'=>array($operator,$re['order']));
            $re_move = $menu_db->where($where)->order("`order` $order")->find();

            if(!$re_move){
                returnjson(array('code'=>1082,'msg'=>"官人,移不动啦!"),$this->returnstyle,$this->callback);
            }
            $menu_db->startTrans();
            $up1=$menu_db->where(array('id'=>$re['id']))->save(array('order'=>$re_move['order']));
            $up2=$menu_db->where(array('id'=>$re_move['id']))->save(array('order'=>$re['order']));
            if($up1 && $up2){
                $menu_db->commit();
                $msg = array('code'=>200);
            }else{
                $menu_db->rollback();
                $msg = array('code'=>104);
            }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /*
    *获取其他设置
    */
    public function GetMixConf()
    {
        $admininfo=$this->merInfo;
        $park_pay_config=$this->GetOneAmindefault($admininfo['pre_table'],$this->ukey, 'other_mix_config');
        $park_pay_config = !empty($park_pay_config['function_name'])?json_decode($park_pay_config['function_name'],true):(object)array();
        returnjson(array('code'=>200,'data'=>$park_pay_config), $this->returnstyle, $this->callback);
    }

    /*
     *设置其他配置
     */
    public function SetMixConf()
    {
        $params = I('param.');
        $this->redis->del("admin:default:one:other_mix_config:$this->key_admin");

        $db = M('default', $this->merInfo['pre_table']);
        $sel = $db->where(array('customer_name' => "other_mix_config"))->find();
        $arr = urldecode($params['other_mix_config']);//json
        if (!is_json($arr)) {
            returnjson(array('code' => 1082, 'msg' => "other_mix_config配置不是json串！"), $this->returnstyle, $this->callback);
        }
        if ($sel) {
            $save = $db->where(array('customer_name' => 'other_mix_config'))->save(array('function_name' => ($arr)));
        } else {
            $save = $db->add(array('customer_name' => 'other_mix_config', 'function_name' => ($arr), 'description' => "其他配置"));
        }

        returnjson(array('code' => 200), $this->returnstyle, $this->callback);
    }

    /**
     * 获取所有一级商户的名称和id
     * @param
     * @return mixed
     */
    public function getAllMer(){
        $db=M('admin','total_');
        $arr=$db->field("id,describe")->where(array('pid'=>0))->select();
        $arr = ArrKeyFromId($arr,'id');
        if($arr){
            $msg=array('code'=>200,'data'=>$arr);
        }else{
            $msg=array('code'=>102);
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 获取所有一级商户的名称和id(没有)
     * @param
     * @return mixed
     */
    public function getNoRelationMer(){
        $db=M('admin','total_');
        $arr1=$db->field("pid")->where(array('pid'=>array('neq',0)))->select();
        $w = ArrKeyAll($arr1,'pid',0);
        $where = array('pid' => 0,'id'=>array('notin',$w));
        $arr=$db->field("id,describe")->where($where)->select();
        if($arr){
            $msg=array('code'=>200,'data'=>$arr);
        }else{
            $msg=array('code'=>102);
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


	
}
