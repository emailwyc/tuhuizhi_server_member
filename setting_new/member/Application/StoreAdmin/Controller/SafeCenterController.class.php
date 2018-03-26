<?php
/**
 * Created by PhpStorm.
 * User: soone
 * Date: 17-12-5
 * Time: 下午2:46
 */
namespace StoreAdmin\Controller;


class SafeCenterController extends AuthController
{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 修改密码(token,old_pwd,pwd1,pwd2); 如果old_pwd为空则认为是首次设置密码
     */
    public function editPassWord() {
        $params = $this->params;
        $this->emptyCheck($params,array('pwd1','pwd2'));
        //根据手机号查询账户信息
        $db = M( 'dkpt_user_acc' , '', 'DB_CONFIG2');
        $dkptAcc = $db->where(array('mobile'=>$this->mobile))->find();
        if($params['pwd1']!=$params['pwd2']){
            returnjson(array('code' =>1082,'msg'=>"登录密码和确认密码不一致，请重新修改！"), $this->returnstyle, $this->callback);
        }
        $pwd = md5(md5($params['pwd1']));
        if(empty($params['old_pwd'])){
            //首次设置密码
            if(isset($dkptAcc['password'])){
                returnjson(array('code' =>1082,'msg'=>"已设置过密码，请填写旧密码修改！"), $this->returnstyle, $this->callback);
            }
            if(empty($dkptAcc)) {
                $addArr = array('mobile'=>$this->mobile,'password'=>$pwd);
                $db->add($addArr);
            }
        }else{
            //修改密码
            $old_pwd = md5(md5($params['old_pwd']));
            if(empty($dkptAcc)){
                returnjson(array('code' =>1082,'msg'=>"首次设置密码请进入（初次设置）进行修改！"), $this->returnstyle, $this->callback);
            }
            if($old_pwd!=$dkptAcc['password']){
                returnjson(array('code' =>1082,'msg'=>"旧密码错误！"), $this->returnstyle, $this->callback);
            }
            $saveArr = array('password'=>$pwd);
            $db->where(array('mobile'=>$this->mobile))->save($saveArr);
        }
        returnjson(array('code'=>200), $this->returnstyle, $this->callback);
    }





}