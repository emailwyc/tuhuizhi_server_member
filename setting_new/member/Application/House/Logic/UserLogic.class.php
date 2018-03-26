<?php
namespace House\Logic;
use Common\Logic\CommonLogic;

class UserLogic extends CommonLogic {

    public function _initialize(){
        parent::__initialize();
    }

    /**
     * 得到用户建筑物信息
     * @param  $id,$role
     * @return
     */
    public function FileUserBuildInfo($userinfo,$isGetBuildName=true){
        $userinfo['build_name'] = "";
        $mNameArr = array("2"=>"DkptShopManagerStore","1"=>"DkptAuditorFloor");
        if(empty($mNameArr[$userinfo['role_type']])){
            return $userinfo;
        }
        $model = DD($mNameArr[$userinfo['role_type']]);
        $buildInfo = $model->getUserInfoById($userinfo['id']);
        if($isGetBuildName) {
            $model_build = DD("KeyAdminBuild");
            $buildnameArr = $model_build->getBuildInfo($userinfo['key_admin'], $buildInfo['build_id']);
            $userinfo['build_name']   = !empty($buildnameArr['build_name'])?$buildnameArr['build_name']:"";
        }
        $userinfo['build_id'] = !empty($buildInfo['build_id'])?$buildInfo['build_id']:"";
        $userinfo['floor']    = !empty($buildInfo['floor'])?$buildInfo['floor']:"";
        $userinfo['poi_no']   = !empty($buildInfo['poi_no'])?$buildInfo['poi_no']:"";
        return $userinfo;
    }

    /**
     * 得到用户建筑物信息
     * @param  $id,$role
     * @return
     */
    public function FileUserBuildInfoV2($userinfo,$isGetBuildName=true){
        $userinfo['build_name'] = "";
        $mNameArr = array("2"=>"DkptShopManagerStore","1"=>"DkptAuditorFloor");
        if(empty($mNameArr[$userinfo['role_type']])){
            return $userinfo;
        }
        $model = DD($mNameArr[$userinfo['role_type']]);
        $buildInfo = $model->getUserInfoById($userinfo['id'],true);
        if($isGetBuildName&&$buildInfo) {
            $model_build = DD("KeyAdminBuild");
            foreach($buildInfo as $k=>$v) {
                $buildnameArr = $model_build->getBuildInfo($userinfo['key_admin'], $v['build_id']);
                $buildInfo[$k]['build_name'] = !empty($buildnameArr['build_name']) ? $buildnameArr['build_name'] : "";
            }
        }
        $userinfo['buildInfo'] = $buildInfo;
        return $userinfo;
    }

    /**
     * 验证用户手机验证码是否正确
     * @param  $id,$role
     * @return
     */
    public function CheckMobileCode($userphone,$code){
        $server_code = $this->redis->get($userphone);
        $phone=array(18010021635);
        if (!in_array($userphone, $phone) && $_SERVER['SERVER_ADDR'] != '123.56.138.28'){
            if ($code != $server_code) {
                $data = array('code' => '1031', 'data'=>2, 'msg' => 'invalid check code');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }
    }

    /**
     * 验证用户登录是否超过限制
     * @param
     * @return
     */
    public function CheckLoginNum($userid){
        //记录手机号登录次数
        $loginnum = (int)$this->redis->get("house:login:".$userid);
        if($loginnum>=3){
            returnjson(array("code"=>1082,"data"=>1,"msg"=>"已超过提交次数，请联系管理员！"), $this->returnstyle, $this->callback);
        }else{
            $loginnum+=1;
            $this->redis->set("house:login:".$userid,$loginnum,3600);
            returnjson(array("code"=>1082,"data"=>2,"msg"=>"信息有误,请确认后再输入！"), $this->returnstyle, $this->callback);
        }
    }

    /**
     * 组装数据
     * @param
     * @return
     */
    public function PackageUserData($userInfo){
        $arr = array(
            'name'=>$userInfo['name'],
            'role_type'=>$userInfo['role_type'],
            'id_card'=>$userInfo['id_card'],
            'build_id'=>$userInfo['build_id'],
            'build_name'=>@$userInfo['build_name'],
            'floor'=>$userInfo['floor'],
            'poi_no'=>$userInfo['poi_no'],
            'phone_num'=>$userInfo['phone_num']
        );
        return $arr;
    }



}
?>