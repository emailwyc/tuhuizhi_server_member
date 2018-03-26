<?php
namespace StoreAdmin\Controller;

use Common\Controller\CommonController;
use PublicApi\Service\SendMessageService;

class IndexController extends CommonController {
	public function _initialize(){
		parent::__initialize();
	}

    /**
     * 发送验证码接口(mobile)
     */
    public function sendMsg() {
        $phone = I('mobile');
        //根据手机号查询商户信息
        if (!$phone){
            returnjson(array('code' =>1030), $this->returnstyle, $this->callback);
        }
        $db = M( 'dkpt_user' , '', 'DB_CONFIG2');
        $dkptUser = $db->where(array('phone_num'=>$phone))->find();
        if (empty($dkptUser)){
            returnjson(array('code' =>1082,'msg'=>"手机号不存在,请重新输入！"), $this->returnstyle, $this->callback);
        }
        $adminInfo = $this->getMerchant($dkptUser['key_admin']);
        if (empty($adminInfo)){
            returnjson(array('code' =>1082,'msg'=>"未找到对应商户信息！"), $this->returnstyle, $this->callback);
        }
        $sendmsg = $this->GetOneAmindefault($adminInfo['pre_table'], $dkptUser['key_admin'], 'sendmsg');
        if (empty($sendmsg['function_name'])){
            returnjson(array('code' =>1082,'msg'=>"未找到对应商户短信配置信息！"), $this->returnstyle, $this->callback);
        }
        $sendmsg = $sendmsg['function_name'];
        $send = SendMessageService::sendMessages($adminInfo, $sendmsg, array('mobile'=>$phone));
        if (is_array($send)){
            returnjson($send, $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
    }

    /**
     * 验证码登录(mobile,code)
     */
    public function login_code()
    {
        $params = I('param.');
        $this->emptyCheck($params,array('mobile','code'));
        $db = M( 'dkpt_user' , '', 'DB_CONFIG2');
        $dkptUser = $db->where(array('phone_num'=>$params['mobile']))->find();
        if (empty($dkptUser)){
            //查看账户表是否有记录，如果有则删除
            $db->where(array('mobile'=>$params['mobile']))->delete();
            returnjson(array('code' =>1082,'msg'=>"手机号不存在,请重新输入！"), $this->returnstyle, $this->callback);
        }
        $adminInfo = $this->getMerchant($dkptUser['key_admin']);
        if (empty($adminInfo)){
            returnjson(array('code' =>1082,'msg'=>"未找到对应商户信息！"), $this->returnstyle, $this->callback);
        }

        $server_code = $this->redis->get($params['mobile']);
        if ($params['code'] != $server_code) {
            returnjson(array('code'=>1082,'msg'=>'登录失败，账号或密码错误！'),$this->returnstyle,$this->callback);
        }
        //验证通过，（生成token并存储）
        $token = strtoupper(md5(uniqid(mt_rand(), true)));
        $this->redis->setex("StoreLogin:".$token,7200, $params['mobile']);//缓存两小时
        $res = array(
            'token'=>$token,
            'mobile'=>$params['mobile'],
            'key_admin'=>$dkptUser['key_admin'],
            'name'  =>$dkptUser['name'],
            'ismaster'=>1
        );
        returnjson(array('code'=>200,'data'=>$res),$this->returnstyle,$this->callback);
    }

    /**
     * 密码登录(mobile,pwd)(不需要清除验证码缓存，５分钟内都可以使用它登录)
     */
    public function login_pwd()
    {
        $params = I('param.');
        $this->emptyCheck($params,array('mobile','pwd'));
        $db = M( 'dkpt_user' , '', 'DB_CONFIG2');
        $db1 = M( 'dkpt_user_acc' , '', 'DB_CONFIG2');
        $dkptUser = $db->where(array('phone_num'=>$params['mobile']))->find();
        if (empty($dkptUser)){
            //查看账户表是否有记录，如果有则删除
            $db->where(array('mobile'=>$params['mobile']))->delete();
            returnjson(array('code' =>1082,'msg'=>"手机号不存在,请重新输入！"), $this->returnstyle, $this->callback);
        }
        $adminInfo = $this->getMerchant($dkptUser['key_admin']);
        if (empty($adminInfo)){
            returnjson(array('code' =>1082,'msg'=>"未找到对应商户信息！"), $this->returnstyle, $this->callback);
        }
        $dkptAcc = $db1->where(array('mobile'=>$params['mobile'],'password'=>md5(md5($params['pwd']))))->find();
        if(empty($dkptAcc)){
            returnjson(array('code'=>1082,'msg'=>'登录失败，账号或密码错误！'),$this->returnstyle,$this->callback);
        }
        //验证通过，（生成token并存储）
        $token = strtoupper(md5(uniqid(mt_rand(), true)));
        $this->redis->setex("StoreLogin:".$token,7200, $params['mobile']);//缓存两小时
        $res = array(
            'token'=>$token,
            'mobile'=>$params['mobile'],
            'key_admin'=>$dkptUser['key_admin'],
            'name'  =>$dkptUser['name'],
            'ismaster'=>1
        );
        returnjson(array('code'=>200,'data'=>$res),$this->returnstyle,$this->callback);
    }

    /**
     * 退出登录(token)
     */
	public function out(){
        $params = I('param.');
        $this->emptyCheck($params,array('token'));
		$this->redis->del("StoreLogin:".$params['token']);//一小时
		returnjson(array('code'=>200),$this->returnstyle,$this->callback);
	}

    /**
     * 手机号码身份验证(mobile,code)
     */
    public function check_code()
    {
        $params = I('param.');
        $this->emptyCheck($params,array('mobile','code'));
        $login_num = (int)$this->redis->get("StoreLoginNum:".$params['mobile']);
        if($login_num>5){
            returnjson(array('code' =>1082,'msg'=>"验证码错误次数过多！"), $this->returnstyle, $this->callback);
        }
        $db = M( 'dkpt_user' , '', 'DB_CONFIG2');
        $dkptUser = $db->where(array('phone_num'=>$params['mobile']))->find();
        if (empty($dkptUser)){
            //查看账户表是否有记录，如果有则删除
            $db->where(array('mobile'=>$params['mobile']))->delete();
            returnjson(array('code' =>1082,'msg'=>"手机号不存在,请重新输入！"), $this->returnstyle, $this->callback);
        }

        $server_code = $this->redis->get($params['mobile']);
        if ($params['code'] != $server_code) {
            $login_num+=1;
            $this->redis->setex("StoreLoginNum:".$params['mobile'],60, $login_num);//缓存两小时
            returnjson(array('code'=>1082,'msg'=>'验证码错误，请重新输入！'),$this->returnstyle,$this->callback);
        }
        returnjson(array('code'=>200),$this->returnstyle,$this->callback);
    }

    /**
     * 忘记密码时重设密码接口(mobile,code,pwd)
     */
    public function set_pwd()
    {
        $params = I('param.');
        $this->emptyCheck($params,array('mobile','code','pwd'));
        $login_num = (int)$this->redis->get("StoreLoginNum:".$params['mobile']);
        if($login_num>5){
            returnjson(array('code' =>1082,'msg'=>"验证码错误次数过多！"), $this->returnstyle, $this->callback);
        }
        $db = M( 'dkpt_user' , '', 'DB_CONFIG2');
        $dkptUser = $db->where(array('phone_num'=>$params['mobile']))->find();
        if (empty($dkptUser)){
            //查看账户表是否有记录，如果有则删除
            $db->where(array('mobile'=>$params['mobile']))->delete();
            returnjson(array('code' =>1082,'msg'=>"手机号不存在,请重新输入！"), $this->returnstyle, $this->callback);
        }

        $server_code = $this->redis->get($params['mobile']);
        if ($params['code'] != $server_code) {
            $login_num+=1;
            $this->redis->setex("StoreLoginNum:".$params['mobile'],60, $login_num);
            returnjson(array('code'=>1082,'msg'=>'验证码错误，请重新输入！'),$this->returnstyle,$this->callback);
        }
        //设置密码
        $db1 = M( 'dkpt_user_acc' , '', 'DB_CONFIG2');
        $dkptAcc = $db1->where(array('mobile'=>$params['mobile']))->find();
        if($dkptAcc){
            $db1->where(array('mobile'=>$params['mobile']))->save(array('password'=>md5(md5($params['pwd']))));
        }else{
            $db1->add(array('mobile'=>$params['mobile'],'password'=>md5(md5($params['pwd']))));
        }
        returnjson(array('code'=>200),$this->returnstyle,$this->callback);
    }


}