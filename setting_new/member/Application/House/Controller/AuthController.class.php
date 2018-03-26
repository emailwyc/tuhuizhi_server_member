<?php
/**
 * 接口授权类
 * 需要验证登录需要继承Auth
 */
namespace House\Controller;
use Common\Controller\CommonController;

class AuthController extends CommonController
{
    public $params;
    public $admin_arr;
    public $userInfo;
    public function _initialize(){
        parent::__initialize();
        $userM = DD("DkptUser");
        $this->params = I('param.');
        $admin_arr = $this->getMerchant($this->ukey);
        $this->admin_arr = $admin_arr;
        $this->emptyCheck($this->params, array('openid'));//目前只支持微信
        $this->userInfo = $userM->getUserByOpenid($this->ukey, $this->userucid);
        if (!$this->userInfo) {
            echo returnjson(array('code' => 2000), $this->returnstyle, $this->callback);
        }
        $userL = DD("User", "", 'Logic');
        if ($this->params['version'] && $this->params['version'] == "v2") {
            $this->userInfo = $userL->FileUserBuildInfoV2($this->userInfo, true);
        } else {
            $this->userInfo = $userL->FileUserBuildInfo($this->userInfo, false);
        }
        //判断用户是否登录超时

    }


}

?>