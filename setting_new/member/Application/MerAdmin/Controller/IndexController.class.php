<?php
/**
 * Created by EditPlus.
 * User: zhanghang
 * Date: 6/24/16
 * Time: 10:10 AM
 */


namespace MerAdmin\Controller;

use Common\Controller\CommonController;

class IndexController extends CommonController {
	public function _initialize(){
		parent::__initialize();
        $this->changjk_baseurl = C('CJKDOMAIN');
	}
    /**
	 * 登录
	 * @param $name 
	 * @param $pwd
     * @return mixed
	 */
	public function login(){
		$name=I("name");
		$pwd=I("pwd");
		if(empty($name) || empty($pwd)){
			$msg=array('code'=>1030);	
		}else{
			//查询商户信息
			$db=M('admin','total_');
			$res=$db->where(array('name'=>$name))->find();
			$isChild = false;
			if(empty($res)){
                $db2=M('admin_child','total_');
                $res=$db2->where(array('name'=>$name))->find();
                $isChild = true;
            }
			if(empty($res)){
				$msg=array('code'=>2000);
			}else{
				//验证密码
				if($res['password']!=md5($pwd)){
                    returnjson(array('code'=>500),$this->returnstyle,$this->callback);exit;
				}
                $childAccId = !$isChild?"":$res['id'];
				if($isChild){
				    $res = $db->where(array('id'=>$res['admin_id']))->find();
                    if(empty($res)){ returnjson(array('code'=>500),$this->returnstyle,$this->callback);exit;}
                }
                ini_set('session.gc_maxlifetime', 24*3600);
                //存入session
                session_start();
                
                $_SESSION['MerAdmin_Login'] = 1;
                $_SESSION['MerAdmin_Login_time'] = time();
                unset($res['password']);
                //session($res['ukey'].'zhihuitu',$res['signkey']);
                //session(array('admin_name'=>$res['ukey'],'expire'=>60));
                $a=$this->redis->set($res['ukey'].'MerAdmin'.$childAccId, $res['signkey']);//一小时
                $b=$this->redis->expire($res['ukey'].'MerAdmin'.$childAccId,1800);
                $this->redis->set($res['ukey'].'MerAdmin', $res['signkey']);//一小时
                $this->redis->expire($res['ukey'].'MerAdmin',1800);
                $res_data=array('ukey'=>$res['ukey'],'name'=>$res['describe'],'childid'=>$childAccId);
                $msg=array('code'=>200,'data'=>$res_data);
			}
		}	
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	//退出接口
	public function out(){
	    $childid=I('childid');
	    $reskey = $childid=="ismaster"?"":$childid;
	    $_SESSION['MerAdmin_Login'] = '';
	    $_SESSION['MerAdmin_Login_time'] = '';
// 		$this->redis->del($this->ukey.'MerAdmin'.$reskey);//一小时
        $token = $this->redis->get('changjingke:login:'.$this->ukey);
        if($token){
            $this->redis->del('changjingke:login:'.$this->ukey);
            $url = $this->changjk_baseurl."/rts-mgr-web/api/v1/auth/logout";
            http($url,array('token'=>$token));
        }
		echo returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();
	}


	/**
	 * 获取商户栏目列表
	 * @param $key_admin
     * @return mixed
	 */
	public function jurisdiction_list(){
        $childid=I('childid');
        $reskey = $childid=="ismaster"?"":$childid;
		$admin_arr=$this->getMerchant($this->ukey);
		if(empty($admin_arr)){
			echo returnjson(array('code'=>1001),$this->returnstyle,$this->callback);exit();
		}

		$get=$this->redis->get($this->ukey.'MerAdmin'.$reskey);

		if(empty($get)){
			echo returnjson(array('code'=>502),$this->returnstyle,$this->callback);exit();
		}

		$this->redis->expire($this->ukey.'MerAdmin'.$reskey,1800);
        if(empty($reskey)){
            $sel=$this->getAuthIds($admin_arr['id']);
            $column = !empty($sel)?$sel['check_auth']:"";
        }else{
            $sel=M('admin_child','total_')->field('auth_id,admin_id')->where(array('id'=>$reskey))->find();
            if(!empty($sel) && $sel['admin_id']!=$admin_arr['id']){
                returnjson(array('code'=>502),$this->returnstyle,$this->callback);
            }
            
            $db=M('admin_auth','total_');
            $pre = 'total_admin_auth';
            $auth = 'total_auth';
            
            $data = $this->child_action($reskey,$admin_arr, $db, $pre, $auth, $sel['auth_id']);
            
            $column = json_encode($data);
        }

		if($column){
		    $msg['code']=200;
		    $msg['data']=json_decode($column,true);
		}else{
		    $msg['code']=102;
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	

	/**
	 * 修改密码
	 * @param $pwd $new_pwd $res_pwd
	 * @return mixed
	 */
	public function Modificar_pwd(){
	    //参数为空判断
	    $pwd=I('pwd');
	    $new_pwd=I('new_pwd');
	    if(empty($pwd) || empty($new_pwd)){
	        $msg=array('code'=>1030);
	    }else{
	        	
	        //判断原密码是否正确
	        $admin_arr=$this->getMerchant($this->ukey);
	        
	        if(md5($pwd) != $admin_arr['password']){
	            $msg=array('code'=>500);
	        }else{
	
	            //修改密码
	            $admin_db=M('admin','total_');
	            $re=$admin_db->where(array('id'=>$admin_arr['id']))->save(array('password'=>md5($new_pwd)));
	
	            if($re!==false){
	                //修改成功
	                $m_info = $this->redis->del('member:' . $this->ukey);
	                
	                $msg=array('code'=>200);
	            }else{
	                //修改失败
	                $msg=array('code'=>104);
	            }
	        }
	    }
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	//判断登陆状态
	public function login_status(){
	    $params['key_admin']=I('key_admin');
// 	    $sign=I('sign');
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	        $key_admin=$this->redis->get($this->ukey.'MerAdmin');
	        if(!$key_admin){
	            $msg['code']=502;
	        }else{
	            $this->redis->expire($this->ukey.'MerAdmin',1800);
	            $admin_arr=$this->getMerchant($this->ukey);
// 	            $params['sign_key']=md5(substr($params['key_admin'],-4)."zhihuitu");
// 	            //echo sign($params);
// 	            if($sign!=sign($params)){
// 	                $msg['code']=1002;
// 	            }else{
                    $msg['code']=200;
//                     $arr['pre_table']=$admin_arr['pre_table'];
                    $arr['describe']=$admin_arr['describe'];
                    $arr['sign_key']=$admin_arr['signkey'];
//                     $build_db=M('buildid','total_');
//                     $build_arr=$build_db->field('name,buildid')->where(array('adminid'=>$admin_arr['id']))->select();
//                     $arr['build_list']=$build_arr;

                    $msg['data']=$arr;
// 	            }
	        }
	    }
	    $write_arr['Shop_request']=$params;
	    $write_arr['Shop_reshpone']=$msg;
	    writeOperationLog($write_arr,'Shop');
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	/**
	 *   获取商户栏目列表(常用栏目)
	 */
    public function often_jurisdiction_list(){
	    $childid=I('childid');
	    $reskey = $childid=="ismaster"?"":$childid;
	    
	    $this->is_login($reskey);
	    
	    $admin_arr=$this->getMerchant($this->ukey);
	    $often_db = M('admin_auth','total_');
	    $pre = 'total_admin_auth';
	    $auth = 'total_auth';
	    
	    if($reskey != ''){
	        $return_auth = $this->child_action($reskey,$admin_arr,$often_db,$pre,$auth);
	    }else{
	        $often_data = $often_db->where(array('admin_id'=>$admin_arr['id'],'is_often'=>1,'show_status'=>1))->join($auth.' on '.$auth.'.id='.$pre.'.auth_id')->order($pre.'.often_sort asc')->select();
	         
	        if(!$often_data){
	             
	            $auth_data = $often_db->where(array('admin_id'=>$admin_arr['id'],'show_status'=>1))->join($auth.' on '.$auth.'.id='.$pre.'.auth_id')->order($pre.'.auth_id asc')->select();
	             
	            if(!$auth_data){
	                echo returnjson(array('code'=>200,'data'=>array()),$this->returnstyle,$this->callback);exit();
	            }
	             
	            if(count($auth_data)>=8){
	                 
	                for($i=0;$i<8;$i++){
	                    $return_data[]=$auth_data[$i];
	                }
	                 
	            }else if(count($auth_data) < 8 && count($auth_data)>0){
	                 
	                $return_data = $auth_data;
	                 
	            }
	            
	            $save_often_data = $return_data;
	        }else if(count($often_data) < 8){
	            $num = 8-count($often_data);
	             
	            $auth_data = $often_db->where(array('admin_id'=>$admin_arr['id'],'is_often'=>2,'show_status'=>1))->join($auth.' on '.$auth.'.id='.$pre.'.auth_id')->order($pre.'.auth_id asc')->limit(0,$num)->select();

	            $often_data1 = array_merge($often_data,$auth_data);
	            
	            $return_data = $often_data1;
	            
	            $save_often_data = $auth_data;
	        }else{
	             
	            $return_data = $often_data;
	             
	        }
	        
	        foreach($return_data as $k=>$v){
	             
	            $return_arr['column_name'] = $v['column_name'];
	            $return_arr['column_api'] = $v['column_api'];
	            $return_arr['column_html'] = $v['column_html'];
	            $return_arr['id'] = $v['auth_id'];
	             
	            $return_auth[]=$return_arr;

	        }
	        
	        if($save_often_data){
	            if(!$often_data){
	                $i = 0;
	            }else if(count($often_data) < 8){
	                $i = 8-count($save_often_data);
	            }

	            foreach($save_often_data as $k=>$v){
	                $often_db->where(array('admin_id'=>$admin_arr['id'],'auth_id'=>$v['auth_id']))->save(array('is_often'=>1,'often_sort'=>$i));
	                $i++;
	            }
	        }
	        
	    }
	    
	    echo returnjson(array('code'=>200,'data'=>$return_auth),$this->returnstyle,$this->callback);exit();
	    
	}
	
	/**
	 *  修改常用栏目
	 */
	public function save_often_column(){
	    $childid=I('childid');
	    $reskey = $childid=="ismaster"?"":$childid;
	    
	    $this->is_login($reskey);
	    $params['often_column']=html_entity_decode(I('often_column'));
	    if(!$params['often_column']){
	        returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
	    }

	    $auth_arr = json_decode($params['often_column'],true);
	     
	    //常用栏目不能大于8个
	    if(count($auth_arr) > 8){
	        returnjson(array('code'=>1009),$this->returnstyle,$this->callback);exit();
	    }

	    foreach($auth_arr as $k=>$v){
	        $auth_id[]=$v['id'];
	    }
	    
	    $admin_arr=$this->getMerchant($this->ukey);
	    $often_db = M('admin_auth','total_');
	    $child_db = M('admin_child','total_');
	    
	    //是否子账号
	    if($reskey != ''){
	        $auth_str = implode(',', $auth_id);
	        $often_res = $child_db->where(array('id'=>$reskey))->save(array('auth_often'=>$auth_str));
	    }else{
	        $often_res = $often_db->where(array('admin_id'=>$admin_arr['id'],'is_often'=>1))->save(array('is_often'=>2,'often_sort'=>0));
	        
	        $i = 0;
// 	        print_r($auth_id);die;
	        foreach($auth_id as $k=>$v){
	            
	            $often_res = $often_db->where(array('admin_id'=>$admin_arr['id'],'auth_id'=>$v))->save(array('is_often'=>1,'often_sort'=>$i));
	            $i++;
	        }
	        
	    }
	    
	    if($often_res === false){
	        returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
	    }
	    
	    returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();	
	}
	
	
	/**
	 *  获取配置过后的栏目列表
	 */
	public function filter_often_column(){
	    $childid=I('childid');
	    $reskey = $childid=="ismaster"?"":$childid;
	    
	    $this->is_login($reskey);
	    $admin_arr=$this->getMerchant($this->ukey);
	    $often_db = M('admin_auth','total_');
	    $pre = 'total_admin_auth';
	    $auth = 'total_auth';
	    
	    if($reskey != ''){
	        $child_db = M('admin_child','total_');
	        $sel=$child_db->field('auth_id,admin_id,auth_often')->where(array('id'=>$reskey))->find();
	        $child_data_often = explode(',', $sel['auth_often']);
	        $child_data_auth = explode(',', $sel['auth_id']);
	        if($child_data_often != ''){
	            foreach($child_data_auth as $k=>$v){
	                if(in_array($v, $child_data_often)){
	                    unset($child_data_auth[$k]);
	                }
	            }
	             
	        }
	        
	        if(empty($child_data_auth)){
	            returnjson(array('code'=>200,'data'=>array()),$this->returnstyle,$this->callback);exit();
	        }
	        
	        $child_auth_str = implode(',', $child_data_auth);
	        $data = $this->child_action($reskey,$admin_arr, $often_db, $pre, $auth, $child_auth_str);
	        
	    }else{
	        $often_data = $often_db->where(array('admin_id'=>$admin_arr['id'],'is_often'=>1,'show_status'=>1))->join($auth.' on '.$auth.'.id='.$pre.'.auth_id')->order($pre.'.auth_id asc')->select();
	         
	        $sel=$this->getAuthIds($admin_arr['id']);
	        $column = !empty($sel)?$sel['check_auth']:"";
	        $column = json_decode($column,true);
	        
	        if($often_data){
	             
	            foreach($often_data as $k=>$v){
	                $column_data_id[]=$v['auth_id'];
	            }
	             
	            foreach($column as $k=>$v){
	                if(!in_array($v['id'], $column_data_id)){
	                    $data[] = $v;
	                }
	            }
	        
	        }else{
	            $data = $column;
	        }
	    }
	    returnjson(array('code'=>200,'data'=>$data),$this->returnstyle,$this->callback);exit();
	    
	}
	
	public function is_login($reskey = ''){
        session_start();
        $admin_arr=$this->getMerchant($this->ukey);
        if(empty($admin_arr['changjk_id']) || $_SESSION['MerAdmin_Login'] == 1) {
            $time = time();
            if (!$_SESSION['MerAdmin_Login'] || ($time - $_SESSION['MerAdmin_Login_time']) > 1800) {
                echo returnjson(array('code' => 502), $this->returnstyle, $this->callback);
                exit();
            }

            $get = $this->redis->get($this->ukey . 'MerAdmin' . $reskey);

            if (empty($get)) {
                echo returnjson(array('code' => 502), $this->returnstyle, $this->callback);
                exit();
            }
        }else {
            $token = $this->redis->get('changjingke:login:' . $admin_arr['ukey']);
            if (empty($token)) {
                returnjson(array('code' => 502), $this->returnstyle, $this->callback);
            }
            $url = $this->changjk_baseurl . "/rts-mgr-web/user/info";
            $cjkInfo = http($url, array('v' => "1.0.0", 'token' => $token));
            $cjkInfo = json_decode($cjkInfo, true);
            if (empty($cjkInfo['data']['id'])) {
                returnjson(array("code" => 502), $this->returnstyle, $this->callback);
                exit();
            }
        }
	}
	
    //子账号栏目处理方法
	public function child_action( $reskey = '',$admin_arr,$db,$pre,$auth,$auth_str = ''){
	    
	    if($auth_str == ''){
    	    $sel=M('admin_child','total_')->field('auth_id,admin_id,auth_often')->where(array('id'=>$reskey))->find();
    	    if(!empty($sel) && $sel['admin_id']!=$admin_arr['id']){
    	        returnjson(array('code'=>502),$this->returnstyle,$this->callback);
    	    }
    	    
    	    $auth_arr = explode(',', $sel['auth_id']);
//     	    echo $sel['auth_often'];die;
    	    if($sel['auth_often'] == ''){
    	        
    	        $data = array_slice($auth_arr,0,8);

    	        $sel['auth_often'] = implode(',', $data);
    	        $status = 1;
    	    }else{
    	        
    	        $often_arr = explode(',', $sel['auth_often']);
    	        
    	        if(count($often_arr)<8){
    	            $num = 8-count($often_arr);
    	            
    	            foreach($auth_arr as $k=>$v){
    	                if(in_array($v, $often_arr)){
    	                    unset($auth_arr[$k]);
    	                }
    	            }
    
    	            $data = array_slice($auth_arr,0,$num);
    	            $often_data = array_merge($often_arr,$data);
    	            
    	            $sel['auth_often'] = implode(',', $often_data);
    	            
    	            $status = 1;
    	        }
    	    }
	    }else{
	        $sel['auth_often'] = $auth_str;
	    }
// 	    $where['admin_id'] = array('eq',$admin_arr['id']);
// 	    $where['auth_id'] = array('in',$sel['auth_often']);
// 	    $where['show_status'] = array('eq',1);
// 	    $where['_logic'] = 'and';
	    $sel_often_data = explode(',', $sel['auth_often']);
	    $res = array();
 	    foreach($sel_often_data as $k=>$v){
 	        $res1 = $db->where(array('admin_id'=>$admin_arr['id'],'auth_id'=>$v,'show_status'=>1))->join($auth.' on '.$auth.'.id='.$pre.'.auth_id')->select();
 	        
 	        $res = array_merge($res,$res1);
 	    }
// 	    print_r($res);die;
	    foreach($res as $k=>$v){
	        $return_data['column_name'] = $v['column_name'];
	        $return_data['column_api'] = $v['column_api'];
	        $column = $v['show_status'] == 2?'':$v['column_html'];
	        $return_data['column_html'] = $column;
	        $return_data['id'] = $v['id'];
	        $auth_id[] = $v['id'];
	        $return_arr[]=$return_data;
	    }
	    $save_auth = implode(',', $auth_id);
	    
	    if($status == 1){
	        M('admin_child','total_')->where(array('id'=>$reskey))->save(array('auth_often'=>$save_auth));
	    }else{
	        if($save_auth != $sel['auth_often']){
	            M('admin_child','total_')->where(array('id'=>$reskey))->save(array('auth_often'=>$save_auth));
	        }
	    }
	    return $return_arr;
	}

    /**
     * 获取单个菜单下子栏目
     * @param $column_id,key_admin
     * @param $childid
     * @return mixed
     */
    public function getChildColumn(){
        $childid=I('childid');
        $column_id=I('column_id');
        $reskey = $childid=="ismaster"?"":$childid;
        $this->is_login($reskey);
        $admin_arr=$this->getMerchant($this->ukey);
        $menu_arr_new = array();
        $CMdb =  M('auth_child','total_');
        if($reskey != ''){
            //获取子帐号二级菜单
            $db=M('admin_child',"total_");
            $arr1=$db->where(array('id'=>(int)$reskey))->find();
            $cloumn23 = empty($arr1['auth_childid'])?array():json_decode($arr1['auth_childid'],true);
            $menu_arr2=$CMdb->where(array('pid'=>(int)@$column_id))->order("sid asc,`order` asc")->select();
            if($menu_arr2) {
                foreach ($menu_arr2 as $key => $val) {
                    if(in_array($val['id'],$cloumn23)){
                        if($val['sid'] == 0){
                            $val['child'] = array();
                            $menu_arr_new[$val['id']] = $val;
                        }else{
                            if(isset($menu_arr_new[$val['sid']])) {
                                $menu_arr_new[$val['sid']]['child'][] = $val;
                            }
                        }

                    }
                }
            }
        }else{
            //获取主帐号二级菜单
            $menu_arr2=$CMdb->where(array('pid'=>(int)@$column_id))->order("sid asc,`order` asc")->select();
            if($menu_arr2) {
                foreach ($menu_arr2 as $key => $val) {
                    if ($val['sid'] == 0) {
                        $val['child'] = array();
                        $menu_arr_new[$val['id']] = $val;
                    } else {
                        $menu_arr_new[$val['sid']]['child'][] = $val;
                    }
                }
            }
        }
        $menu_arr_new = object_to_list($menu_arr_new);
        $msg = array('code'=>200,'data'=>$menu_arr_new);
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * 检查商户是否绑定了场景客Id
     * @param $key_admin
     * @return mixed
     */
    public function checkUserIsBind(){
        $admininfo=$this->getMerchant($this->ukey);
        $token = $this->redis->get('changjingke:login:'.$admininfo['ukey']);
        $url = $this->changjk_baseurl."/rts-mgr-web/user/info";
        if($token) {
            $cjkInfo = http($url, array('v' => "1.0.0", 'token' => $token));
            $cjkInfo = json_decode($cjkInfo, true);
            if(empty($cjkInfo['data']['id'])){
                $isout = 1;
            }else{
                $isout = 0;
            }
        }else{
            $isout = 1;
        }
        if(!empty($admininfo['changjk_id'])){
            $msg['code']=200;
            $msg['data']=array('isBind'=>1,'isout'=>$isout);
        }else{
            $msg['code']=200;
            $msg['data']=array('isBind'=>0,'isout'=>$isout);
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 记录token
     * @param $key_admin
     */
    public function getMerByToken(){
        //根据token获取信息
        $token = I('token');
        $url = $this->changjk_baseurl."/rts-mgr-web/user/info";
        $cjkInfo = http($url,array('v'=>"1.0.0",'token'=>$token));
        $cjkInfo = json_decode($cjkInfo,true);
        if(empty($cjkInfo['data']['id'])){
            returnjson(array("code"=>502,'msg'=>"登录已失效！"),$this->returnstyle,$this->callback);exit();
        }
        $merchant = M('total_admin');
        $re = $merchant->where(array('changjk_id' => $cjkInfo['data']['id']))->find();
        if(empty($re)){
            returnjson(array("code"=>1082,'msg'=>"该商户还未绑定！"),$this->returnstyle,$this->callback);exit();
        }
        $this->redis->set('changjingke:login:'.$re['ukey'], $token);
        $this->redis->expire('changjingke:login:'.$re['ukey'], 86400*30);
        $msg['code']=200;
        $msg['data']=$re;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /*
    *获取其他设置
    */
    public function GetMixConf()
    {
        $admininfo=$this->getMerchant($this->ukey);
        $park_pay_config=$this->GetOneAmindefault($admininfo['pre_table'],$this->ukey, 'other_mer_config');
        $park_pay_config = !empty($park_pay_config['function_name'])?json_decode($park_pay_config['function_name'],true):(object)array();
        returnjson(array('code'=>200,'data'=>$park_pay_config), $this->returnstyle, $this->callback);
    }

    /*
     *设置其他配置
     */
    public function SetMixConf()
    {
        $params = I('param.');
        $this->redis->del("admin:default:one:other_mer_config:$this->ukey");
        $this->merInfo=$this->getMerchant($this->ukey);
        $db = M('default', $this->merInfo['pre_table']);
        $sel=$db->where(array('customer_name'=>"other_mer_config"))->find();
        $arr = urldecode($params['other_mer_config']);//json
        if(!is_json($arr)){
            returnjson(array('code'=>1082,'msg'=>"other_mer_config配置不是json串！"), $this->returnstyle, $this->callback);
        }
        if ($sel) {
            $save=$db->where(array('customer_name'=>'other_mer_config'))->save(array('function_name'=>($arr)));
        }else{
            $save=$db->add(array('customer_name'=>'other_mer_config','function_name'=>($arr),'description'=>"商户端其他配置"));
        }

        returnjson(array('code'=>200), $this->returnstyle, $this->callback);
    }

}