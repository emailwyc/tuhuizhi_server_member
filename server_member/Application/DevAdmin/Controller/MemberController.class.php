<?php
/**
 * Created by PhpStorm.
 * User: jaleel and zhanghang
 * Date: 5/30/16
 * Time: 10:10 AM
 */

namespace DevAdmin\Controller;
use Think\Controller;
use Common\Controller\CommonController;
use Common\Controller\RedisController as A;

class MemberController extends CommonController
{
	public $key_admin;
	public $merInfo;

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
        if(!empty($search)){
			
			$where['describe']=array('like','%'.$search.'%');
            $count=$db->where($where)->count();
			//查询搜索后的数据
			$res=$db->where($where)->limit($start, $lines)->select();
		}else{
            $count=$db->count();
			//查询全部数据
			$res=$db->field('id,describe,pre_table,ukey')->limit($start, $lines)->select();
			
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
		//$appid=I('appid');
		if(empty($username) || empty($password) || empty($re_pwd) || empty($pre_table) || empty($describe)){
			$msg=array('code'=>1030,'msg'=>'ERROR','data'=>'未获取到必要的信息');
		}else{
			if($password!=$re_pwd){
				$msg=array('code'=>3000,'msg'=>'ERROR','data'=>'两次密码不一致');
			}else{
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
				$data['enable']=0;
				//$data['wechat_appid']=$appid;
				$db=M('admin','total_');
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
		//$appid=I('appid');
		if(empty($username) || empty($pre_table) || empty($describe) || empty($id)){
			$msg=array('code'=>1030,'msg'=>'ERROR','data'=>'未获取到必要的信息');
		}else{
			$data['name']=$username;
			$data['pre_table']=$pre_table;
			$data['describe']=$describe;
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
			$quan_db=M('auth','total_');
			$quan_arr=$quan_db->where(array('id'=>$id))->find();
			$data['column_api']=$column_api;
			$data['column_html']=$column_html;
			$data['column_name']=$column_name;
		    if($column_html == $quan_arr['column_html']){
		        $res=$quan_db->where(array('id'=>$id))->save($data);
		        if($res === false){
		            $msg=array('code'=>104);
		        }else{
		            $msg=array('code'=>200);
		        }
		    }else{
		        $html_arr=$quan_db->where(array('column_html'=>$column_html))->find();
		        if($html_arr){
		            $msg['code']=1008;
		        }else{
		            $res=$quan_db->where(array('id'=>$id))->save($data);
		            if($res === false){
		                $msg=array('code'=>104);
		            }else{
		                $msg=array('code'=>200);
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
	        $data['column_html']=$column_html;
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
			$quan_admin_db=M('auth_admin','total_');
			
			$quan_arr=$quan_admin_db->where(array('admin_id'=>$admin_id))->find();
			if($quan_arr){
                $this->child_jurisdiction($admin_id,$quan_id);
				$re=$quan_admin_db->where(array('admin_id'=>$admin_id))->save($data);
			}else{
				$data['admin_id']=$admin_id;
				$re=$quan_admin_db->add($data);
			}
			//创建微商城相关表
			$merInfo = M('admin','total_')->where(array('id'=>$admin_id))->find();
			if(strstr($quan_id,"icromall")!==false && !empty($merInfo['pre_table'])){ $this->micromall_table($merInfo['pre_table']); }
			if(strstr($quan_id,"valuate")!==false && !empty($merInfo['pre_table'])){ $this->AuthCreateTableEvaluate($merInfo['pre_table']); }

			if($re === false){
				$msg=array('code'=>104);
			}else{
				$msg=array('code'=>200);
			}
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
			$res=$this->getAuthId($admin_id);

			if($res){
                
				$msg=array('code'=>200,'data'=>json_decode($res['check_auth'],true));
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
					$arr['op']=$op_arr['function_name'];
					$arr['subpayacc']=$sup_arr['function_name'];
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
		if(empty($admin_id)){
			$msg = array('code'=>1030);
		}else{
			$db = M('admin','total_');
			$arr = $db->field('id,name,describe,pre_table')->where(array('id'=>$admin_id))->find();
			if($arr){
				$build_db = M('buildid','total_');
				$build_info = $build_db->where(array('adminid'=>$arr['id']))->save(array('is_del'=>1));
				//更新appid
                $updateArr = array(
                    'wechat_appid'=>$appid,
                    'alipay_appid'=>$alipay_appid,
                    'applet_appid'=>$applet_appid
                );
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
					if($arr1){
						//更新
						$res2=$childDb->where(array('customer_name'=>'subpayacc'))->save(array('function_name'=>$payAccount));
					}else{
						//添加
						$res2=$childDb->add(array('customer_name'=>"subpayacc",'function_name'=>$payAccount,'description'=>"微信支付子商户号"));
					}
					if($arr2){
					    //更新
					    $res3=$childDb->where(array('customer_name'=>'op'))->save(array('function_name'=>$op_id));
					}else{
					    //添加
					    $res3=$childDb->add(array('customer_name'=>"op",'function_name'=>$op_id,'description'=>"营销平台op"));
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
	        $db=M('catalog','total_');
	        $data['name']=$name;
	        $data['status']=1;
	        $data['datetime']=date('Y-m-d H:i:s');
	        $data['url']=$url;
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
            $ver_col_arr=$vers_column_db->where($where)->find();
            if($ver_col_arr){
                $data['url']=html_entity_decode($params['url']);
                $res=$vers_column_db->where($where)->save($data);
                if($res !== false){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
            }else{
                $res=$vers_column_db->add($params);
                if($res !== false){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
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


}
