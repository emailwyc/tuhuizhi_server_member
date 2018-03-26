<?php
/**
 * 用户登录类，该类不继承Auth
 * User: soone
 * Date: 17-7-29
 * Time: 下午12:04
 */
namespace House\Controller;
use Common\Controller\CommonController;

class LoginController extends CommonController {

    public function _initialize(){
        parent::__initialize();
        $this->userM = DD("DkptUser");
        $this->userL = DD("User","",'Logic');
        $this->params = I('param.');
        $admin_arr=$this->getMerchant($this->ukey);
        $this->admin_arr = $admin_arr;
        $this->emptyCheck($this->params,array('openid'));
    }

    public function login_check(){
        $userInfo = $this->userM->getUserByOpenid($this->ukey,$this->userucid);
        if(!$userInfo){
            //需要去登录
            $msg = array('code'=>2000);
        }
        returnjson(array("code"=>200), $this->returnstyle, $this->callback);
    }

    public function sign_out(){
        $userInfo = $this->userM->getUserByOpenid($this->ukey,$this->userucid);
        if($userInfo) {
            $this->redis->del("house:dkptuser:$this->ukey:" . $this->userucid);
            $this->userM->where(array('id'=>$userInfo['id']))->save(array('open_id'=>"sign_out"));
        }
        returnjson(array("code"=>200), $this->returnstyle, $this->callback);
    }

    /**
     * 获取商户信息
     */
    public function getMerInfo() {
        $arr = array('describe'=>$this->admin_arr['describe']);
        $msg = array('code'=>200,'data'=>$arr);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    /**
     * 获取用户登录信息
     */
    public function getUserInfo() {
        $userInfo = $this->userM->getUserByOpenid($this->ukey,$this->userucid);
        if(!$userInfo){
            //需要去登录
            $msg = array('code'=>2000);
        }else{
            //返回200,并返回用户身份信息;
            $userInfo = $this->userL->FileUserBuildInfo($userInfo);
            $arr = $this->userL->PackageUserData($userInfo);
            $msg = array('code'=>200,'data'=>$arr);
        }
        returnjson($msg, $this->returnstyle, $this->callback);

    }

    /**
     * 获取用户登录状态
     */
    public function LoginOauth() {
        //获取检查参数
        $this->emptyCheck($this->params,array('mobile','code','name'));
        //检查验证码是否正确
        $this->userL->CheckMobileCode($this->params['mobile'],$this->params['code']);
        //根据手机号查询用户信息，并且更新openid
        $where = array('key_admin'=>$this->ukey,'phone_num'=>trim($this->params['mobile']),'name'=>trim($this->params['name']));
        $userInfo = $this->userM->getUserByWhere($where);
        if($userInfo){
            //清除该用户以前登录缓存信息
            if($userInfo['open_id']) {
                $this->redis->del("house:dkptuser:$this->ukey:" . $userInfo['open_id']);
            }
            //更新openid
            $this->userM->where(array('id'=>$userInfo['id']))->save(array('open_id'=>$this->userucid));
        }else{
            $this->userL->CheckLoginNum($this->userucid);
        }
        $userInfo = $this->userL->FileUserBuildInfo($userInfo);
        $arr = $this->userL->PackageUserData($userInfo);
        $msg = array('code'=>200,'data'=>$arr);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    /**
     * 根据poi获取poi_name字段
     */
    public function getPoiName(){
        $params = $this->params;
        $db = M( 'key_admin_table' , '', 'DB_CONFIG2');
        $this->emptyCheck($params,array("buildid","floor","poi"));
        $merTabInfo = $db->where(array("key_admin"=>$this->ukey))->find();
        if(!$merTabInfo){
            returnjson(array("code"=>1082,"msg"=>"未找到该商户"), $this->returnstyle,$this->callback);
        }
        $db1 = M( $merTabInfo['prefix'].'_map_poi_'.$params['buildid'] , '', 'DB_CONFIG2');
        $where = array("id_build"=>$params['buildid'],'floor'=>$params['floor'],'poi_no'=>$params['poi']);
        $sel = $db1->field('poi_name')->where($where)->find();
        $poi_name = !empty($sel['poi_name'])?$sel['poi_name']:"";
        returnjson(array("code"=>200,"data"=>$poi_name), $this->returnstyle,$this->callback);
    }


}
?>
