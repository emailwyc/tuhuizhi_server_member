<?php
/**
 * Created by PhpStorm.
 * User: jaleel
 * Date: 2017/3/10
 * Time: 下午2:04
 */

namespace WIFI\Controller;


use Common\Controller\JaleelController;

class WifiController extends JaleelController
{
    protected $merchant;
    protected $className;
    public function _initialize() {
        parent::_initialize();

        $this->merchant = $this->getMerchant($this->ukey);
    }

    public function gowifi()
    {
        $className = 'WIFI\Controller\\' . ucfirst(rtrim($this->merchant['pre_table'], '_')) . 'Controller';

        $className::gowifi($this->user_openid);
        $this->display('wechat');
    }

    public function sendwifimsg()
    {
        $class_name = 'WIFI\Controller\\' . ucfirst(rtrim($this->merchant['pre_table'], '_')) . 'Controller';
        $class_name::sendwifimsg($this->ukey, $this->user_openid);
        $data = array('code' => 200, 'msg' => 'success');
        returnjson($data,$this->returnstyle,$this->callback);
    }

    public function getLogStatus() {
        $class_name = 'WIFI\Controller\\' . ucfirst(rtrim($this->merchant['pre_table'], '_')) . 'Controller';

        if (!$this->user_openid) {
             $data = array('code' => 1030);
            returnjson($data,$this->returnstyle,$this->callback);
        }

        if ($class_name::checkUser($this->user_openid)) {
            $data = array('code' => 200, 'msg' => 'success');
        } else {
            $data = array('code' => 1011, 'msg' => 'sorry, u should login now!');
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }
}