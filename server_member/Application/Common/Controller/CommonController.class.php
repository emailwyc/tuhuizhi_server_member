<?php
namespace Common\Controller;
use Think\Controller;

/**
 * 所有的公共操作
 * @author 凯锋
 *
 */
class CommonController extends Controller{
    public $returnstyle;
    public $callback;
    public $from;//从什么客户端来的
    //public $type;//用什么参数判断什么，如openid，userid，phone
    public $user_openid;//用户openid
    public $user_userid;//用户支付宝userid
    public $user_phone;//用户手机号
    public $ukey;//后台管理账户key
    public $userucid;
    public $redis;
    
    public function __initialize(){
        //header ( "Content-Type:application/json;charset=UTF-8" );
        //header("Access-Control-Allow-Origin: http://res.rtmap.com");
        header("Access-Control-Allow-Credentials: true");
        //判断请求是以什么方式发起的
        if (I('callback','','htmlspecialchars')){
            $this->callback=I('callback','','htmlspecialchars');
            $this->returnstyle=false;
        }else{
            $this->callback='';
            $this->returnstyle=true;
        }
//        $this->from=I('from');

//        $this->type=I('type');

        $this->ukey=I('key_admin');
        $this->user_openid = I('openid');
        $this->user_userid = I('userid');
        $this->userucid = $this->user_openid ? $this->user_openid : $this->user_userid;//全部将微信或支付宝的用户id转为统一的id，（user-client-id）

        if ($this->user_openid){
            $this->from='wechat';
        }elseif ($this->user_userid){
            $this->from= 'alipay';
        }else{
            $this->from= 'wechat';//默认微信
        }


        $redis_con = new RedisController();
        $this->redis = $redis_con->connectredis();
//         if (IS_AJAX){
            $this->interfacelog();
        //}
        $this->writelog();
    }

    /**
     * 按商户密钥查询商户配置信息
     * @param $key_admin 商户密钥
     * @return bool
     */
    protected function getMerchant($key_admin) {
        if (!$key_admin) {
            returnjson(array('code'=>1001), $this->returnstyle, $this->callback);
        }
        $m_info = $this->redis->get('member:' . $key_admin);
        if ($m_info) {
            //writeOperationLog(array('get merchant' => $m_info), 'jaleel_logs');
            return json_decode($m_info, true);
        } else {
            $merchant = M('total_admin');
            $re = $merchant->where(array('ukey' => $key_admin))->find();

            if ($re) {
                //writeOperationLog(array('get merchant' => $re), 'jaleel_logs');
                $this->redis->set('member:' . $key_admin, json_encode($re),array('ex'=>86400));//一天
            }else {
                $data['code']=1001;
                echo returnjson($data,$this->returnstyle,$this->callback);exit();
            }

            return $re;
        }
    }

    /**
     * 按子商户id查询子商户配置信息
     * @param $id 商户id
     * @return bool
     */
    protected function getChildMerchant($id) {
        if (!$id) {
            returnjson(array('code'=>1001), $this->returnstyle, $this->callback);
        }
        $m_info = $this->redis->get('member:child:' . $id);
        if ($m_info) {
            return json_decode($m_info, true);
        } else {
            $merchant = M('total_admin_child');
            $re = $merchant->where(array('id' => $id))->find();
            if ($re) {
                $this->redis->set('member:child:' . $id, json_encode($re),array('ex'=>3600));//一天
            }else {
                $data['code']=1001;
                echo returnjson($data,$this->returnstyle,$this->callback);exit();
            }
            return $re;
        }
    }
    
    /**
     * 获取admin默认配置
     */
    public function GetdAmindefault($table_pre,$key_admin){
        if (!$key_admin) {
            returnjson(array('code'=>1001), $this->returnstyle, $this->callback);
        }
        $default=$this->redis->get('admin:default:'.$key_admin);
        if ($default){
            return json_decode($default,true);
        }else{
            $db=M('default',$table_pre);
            $select=$db->select();
            if ($select) {
                $this->redis->set('admin:default:' . $key_admin, json_encode($select),array('ex'=>86400));//一天
            }else {
                $data['code']=102;
                echo returnjson($data,$this->returnstyle,$this->callback);exit();
            }
            return $select;
        }
    }
    
    
    
    
    /**
     * 获取admin单条配置
     */
    protected function GetOneAmindefault($table_pre,$key_admin,$function_name){
        if (!$key_admin) {
            returnjson(array('code'=>1001), $this->returnstyle, $this->callback);
        }
        $default=$this->redis->get('admin:default:one:'.$function_name.':'. $key_admin);
        if ($default){
            return json_decode($default,true);
        }else{
            $dbm=M();
            $c=$dbm->execute('SHOW TABLES like "'.$table_pre.'default"');
            if (1 !== $c){
                $sql="CREATE TABLE `".$table_pre."default`  (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '索引id',
  `customer_name` varchar(50) NOT NULL COMMENT '用途',
  `function_name` text NOT NULL COMMENT '用途属性',
  `description` varchar(150) DEFAULT '' COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='商户常量配置表'";
                $dbm->execute($sql);
                return null;
            }else{
                $db=M('default',$table_pre);
                $select=$db->where(array('customer_name'=>$function_name))->find();
                if ($select) {
                    $this->redis->set('admin:default:one:'.$function_name.':'. $key_admin, json_encode($select),array('ex'=>86400));//一天
                    /*}else {
                    $data['code']=102;
                    returnjson($data,$this->returnstyle,$this->callback);exit();*/
                }
                return $select;
            }
        }
    }
    
    
    
    /**
     * 记录接口日志
     */
    public function interfacelog(){
        $log = date('Y-m-d').'--'.MODULE_NAME.':'.CONTROLLER_NAME.':'.ACTION_NAME;
        $log=strtolower($log);
        $this->redis->incr($log);
    }
    

    //获取商户权限列表
    protected function getAuthId($admin_id) {
        $db=M('auth_admin','total_');
        $sel=$db->field('check_auth')->where(array('admin_id'=>$admin_id))->find();

		if(empty($sel)){

			return false;
		}else {
			return $sel;
        }
    }


	//权限控制
	protected function Auth_Admin($admin_id){
		$sel=$this->getAuthId($admin_id);

		if(false == $sel){
			return false;
		}

		$sel=json_decode($sel['check_auth'],true);

		foreach($sel as $k=>$v){
			$arr[]=$v['column_api'];
		}

		if(!in_array(MODULE_NAME.'/'.CONTROLLER_NAME,$arr)){
			return false;
		}else{
			return true;
		}
	}
	
	
	
	/**
	 * 验证接收到的参数是否符合规则
	 * @param array $params
	 * @param string $key_admin
	 * @param string $sign
	 */
	public function checkParams(array $params, string $key_admin, string $sign)
	{
	    if (!is_array($params)){
	        $code=1051;
	    }else{
	        if (in_array('', $params)){
	            $code=100;
	        }else {
	            $admininfo=$this->getMerchant($key_admin);
	            $params['sign_key']=$admininfo['signkey'];
	            if (sign($params) == $sign){
	                $code= true;
	            }else{
	                $code=1002;
	            }
	        }
	    }
	    return $code;
	}
	
	
	
	/**
	 * 文件上传
	 */
	protected function uploadfile($file, int $maxsize, array $exts, $path){
	    $upload = new \Think\Upload();// 实例化上传类
	    $upload->maxSize   =     $maxsize ;// 设置附件上传大小
	    $upload->exts      =     $exts;// 设置附件上传类型
	    $upload->rootPath  =      './Uploads/'.$path; // 设置附件上传根目录
	    // 上传单个文件
	    $info   =   $upload->uploadOne($file);
	    if(!$info) {// 上传错误提示错误信息
	        return array('code'=>false, 'msg'=>$info);
	    }else{// 上传成功 获取上传文件信息
	        return array('code'=>true, 'msg'=>$info['savepath'].$info['savename']);
	    }
	}
	
	
	/**
	 * 通过商户的key_admin获取商户的建筑物id
	 * @param $key_admin
	 * @return mixed
	 */
	protected function getBuildIdByKey($key_admin) {
	    $mer_info = $this->getMerchant($key_admin); // 查询商户信息
	    $build_info = $this->redis->get('buildid:' . $key_admin);
	
	    if ($build_info) {
	        return json_decode($build_info, true);
	    } else {
	        $merchant = M('total_buildid');
	        $re = $merchant->where(array('adminid' => $mer_info['id']))->find();
	
	        if ($re) {
	            $this->redis->set('buildid:' . $key_admin, json_encode($re),array('ex'=>86400));//一天
	        }else {
	            $data['code']=1001;
	            echo returnjson($data,$this->returnstyle,$this->callback);exit();
	        }
	
	        return $re;
	    }
	}
	
	/**
	 * 获取商户会员等级
	 */
	public function get_mem_level($key_admin){
	    $mer_info = $this->getMerchant($key_admin); // 查询商户信息
	    $level_info = $this->redis->get('level_member:' . $key_admin);
	    
	    if ($level_info) {
	        return json_decode($level_info, true);
	    } else {
	        $merchant = M('total_static');
	        $re = $merchant->where(array('admin_id'=>$mer_info['id'],'tid'=>5))->find();
	
	        if ($re) {
	            $this->redis->set('level_member:' . $key_admin, $re['content'],array('ex'=>86400));//一天
	        }else {
	            $data['code']=1001;
	            echo returnjson($data,$this->returnstyle,$this->callback);exit();
	        }
	
	        return json_decode($re['content'],true);
	    }
	}


    /**
     * 获取商户的ping++配置
     * @param array $admininfo
     * @param $buildid
     * @return mixed
     */
	protected function getPingxxConfig(array $admininfo, $buildid)
    {
        $m_info = $this->redis->get('member:pingxx:' . $admininfo['ukey'] . ':' . $buildid);
        if ($m_info) {
            return json_decode($m_info, true);
        } else {
            $merchant = M('total_pingxx');
            $re = $merchant->where(array('adminid' => $admininfo['id'], 'buildid'=>$buildid))->find();
            if ($re) {
                //writeOperationLog(array('get merchant' => $re), 'jaleel_logs');
                $this->redis->set('member:pingxx:' . $admininfo['ukey'] . ':' . $buildid, json_encode($re),array('ex'=>86400));//一天
            }else {
                $data['code']=102;
                $data['data']='build & key_admin';
                returnjson($data,$this->returnstyle,$this->callback);exit();
            }
            return $re;
        }
    }

    /**
     * 记录日志
     */
	private function writelog()
    {
        $get=I('get.');
        $post=I('post.');
        $file=file_get_contents('php://input');
        $array=array('get'=>$get, 'post'=>$post);
        $array['fileinput'] = IS_POST ? $file : '';
        $logpath=MODULE_NAME.'-'.CONTROLLER_NAME.'-'.ACTION_NAME;
        $logpath=str_replace('/','-', $logpath);
        $logpath=strtolower($logpath);
        $logpath='paramslog/'.date('Y-m-d').'/'.$logpath;
//        echo $logpath;
        writeOperationLog($array, $logpath);
    }
    /**
     * curl请求
     * POST数据为JSON数据
     * @param $url
     * @param $data_string
     * @return mixed
     */
    protected function curl_json($url, $data_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        $curl_re = curl_exec($ch);
        curl_close($ch);

        return $curl_re;
    }

    /**
     * 不允许此方法"输出"任何json或任何字符串，只能用"return"返回
     * @param $appid
     * @return bool|mixed
     */
    public function getAppidKeyAdmin($appid)
    {
        if (!$appid) {
            return false;
        }
        $default=$this->redis->get('admin:info:one:'. $appid);
        if ($default){
            return json_decode($default,true);
        }else{
            $db=M('admin','total_');
            $select=$db->where(array('wechat_appid'=>$appid))->find();
            if ($select) {
                $this->redis->set('admin:info:one:'. $appid, json_encode($select),array('ex'=>86400));//一天
                return $select;
            }else {
                return false;
            }
        }
    }


}

?>