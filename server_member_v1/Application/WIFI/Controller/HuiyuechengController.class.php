<?php
/**
 * Created by PhpStorm.
 * User: jaleel
 * Date: 2017/3/10
 * Time: 下午2:22
 */

namespace WIFI\Controller;


use Common\Controller\JaleelController;
use Thirdwechat\Controller\Wechat\ServicemessageController;

class HuiyuechengController extends JaleelController
{
    protected static $appid = 'wx7214fd8ce12d788b';

    public static function sendwifimsg($key_admin, $openid)
    {
        $wifi_info = self::getwifi();

        $json_arr = array(
            'touser' => $openid,
            'msgtype' => 'news',
            'news' => array(
                'articles' => array(
                    array(
                        'title' => '一键WIFI',
                        'description' => $wifi_info['function_name']['txt'],
                        'url' => 'https://mem.rtmap.com/WIFI/Wifi/gowifi/key_admin/' . $key_admin . '/openid/' . $openid,
                        'picurl' => $wifi_info['function_name']['img']
                    ),
                ),
            ),
        );

        self::sendmsg($json_arr, self::$appid);
    }

    protected static function sendmsg($data, $appid)
    {
        $msg = new ServicemessageController();
        $re = $msg->inside_send_service_message($data, $appid);
    }


    public static function gowifi($openid)
    {
        $data['touser'] = $openid;
        $data['msgtype'] = 'text';

        if (stripos($_SERVER['HTTP_USER_AGENT'], 'nettype/wifi') === false) {
            $data['text']['content'] = '您现在用的是手机3G/4G网络，请先连上免费WIFI再进行认证上网！';
            self::sendmsg($data, self::$appid);
            return false;
        }

        $clientIp = $_SERVER['REMOTE_ADDR'];

        $wifi_info = self::getwifi();

        if ($wifi_info['function_name']['is_mem'] == 1) {
            $ckuser = self::checkUser($openid);

            if ($ckuser === false) {
                $data['text']['content'] = '您目前还不是我们我的尊贵会员，请先注册会员再进行认证上网！';
                self::sendmsg($data, self::$appid);
                return false;
            }
        }

        $conf_ip_arr = explode(',', $wifi_info['function_name']['wifi_ip']);
        $check_ip = 0;

        foreach ($conf_ip_arr as $v) {
            if (strpos($clientIp, $v) === 0) {
                $check_ip = 1;
                break;
            }
        }

        if ($check_ip == 0) {
            $data['text']['content'] = "请连接商场免费wifi进行认证上网！";
            self::sendmsg($data, self::$appid);
            return false;
        }

        // 查询用户是否认证
        $result = self::checkuserauth($openid);
        $result_arr = json_decode($result, true);
        if ($result_arr['status'] != 1) {
            $add_re = self::addwifiauth($clientIp, $openid);
            $add_arr = json_decode($add_re, true);

            if ($add_arr['status'] != 1) {
                $data['text']['content'] = '认证失败，请重试！';
                self::sendmsg($data, self::$appid);
                return false;
            } else {
                $data['text']['content'] = '您好，一键上网成功！';
                self::sendmsg($data, self::$appid);
                return false;
            }
        } else {
            $data['text']['content'] = '您好，一键上网成功！';
            self::sendmsg($data, self::$appid);
        }
    }

    /**
     * 查询一键wifi相关配置
     */
    protected static function getwifi()
    {
        $obj = M('huiyuecheng_default');
        $wifi_info = $obj->where(array('customer_name' => 'wifi'))->find();

        if (is_array($wifi_info)) {
            $wifi_info['function_name'] = json_decode($wifi_info['function_name'], true);
            return $wifi_info;
        } else {
            return false;
        }
    }

    protected static function checkuserauth($openid)
    {
        $data['openId'] = $openid;
        $url = 'http://218.14.150.132:8000/index.php/portal/getAuthUserList';
        $re = curl_https($url, $data);
        writeOperationLog(array('huiyuecheng get user wifi auth ' => json_encode($re)), 'jaleel_logs');
        return $re;
    }

    protected static function addwifiauth($ip, $openid)
    {
        $data['ip'] = $ip;
        $data['openId'] = $openid;
        $url = 'http://218.14.150.132:8000/index.php/portal/setAuthUserList';
        $re = curl_https($url, $data);
        writeOperationLog(array('huiyuecheng add user wifi auth ' => json_encode($re)), 'jaleel_logs');
        return $re;
    }

    public static function checkUser($openid) {
        $obj = M('huiyuecheng_mem');
        $re = $obj->where(array('openid' => $openid))->find();

        if (is_array($re) && count($re) > 0) {
            return true;
        }
        return false;
    }
}

