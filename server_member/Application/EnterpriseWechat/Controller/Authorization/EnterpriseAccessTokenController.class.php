<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/4/10
 * Time: 15:56
 * http://qydev.weixin.qq.com/wiki/index.php?title=主动调用
 */

namespace EnterpriseWechat\Controller\Authorization;


use Common\Controller\RedisController;
use Think\Controller;

class EnterpriseAccessTokenController extends Controller
{

    private static $accesstoken;
    private static $corpId;
    private static $secrect;
    private  static $redis;
    protected static $getaccessTokenurl;
    public function CheckAccessToken()
    {
        $rediss = new RedisController();
        self::$redis = $rediss->connectredis(1);
        $accesstoken = self::$redis->get('EnterpriseWechat:' . self::$secrect . ':accesstoken');echo $accesstoken;
        if (false != $accesstoken){
            self::$accesstoken = $accesstoken;
        }else{
            $accesstoken = self::setAccessToken();
            self::$accesstoken = $accesstoken;
        }
    }

    public static function getAccessToken($corpId, $secure, $getaccessTokenurl)
    {
        self::$corpId = $corpId;
        self::$secrect = $secure;
        self::$getaccessTokenurl = $getaccessTokenurl;
        self::CheckAccessToken();
        return self::$accesstoken;
    }


    /**
     * @param
     */
    private static function setAccessToken()
    {
        $url=self::$getaccessTokenurl;
        $url = str_replace('[corpid]', self::$corpId, $url);
        $url = str_replace('[secrect]', self::$secrect, $url);
        $return = curl_https($url);dump($return);echo $url;

        if (is_json($return)){
            $rearray = json_decode($return, true);
            if (is_array($rearray)){
                if (!isset($rearray['errcode'])){//如果没有errrcode则成功
                    self::$redis->set('EnterpriseWechat:' . self::$secrect . ':accesstoken', $rearray['access_token'], array('ex'=>$rearray['expires_in']));
                    return array('code'=>200, 'data'=>$rearray['access_token']);
                }else{//失败
                    return array('code'=>104, 'data'=>$rearray);
                }
            }else{//解析后不是数组
                return array('code'=>1000, 'data'=>$return);
            }
        }else{//返回的不是json
            return array('code'=>4000);
        }
    }
}