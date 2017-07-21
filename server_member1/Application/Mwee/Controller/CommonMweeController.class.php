<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/6/13
 * Time: 11:49
 */

namespace Mwee\Controller;


use Common\Controller\RedisController;
use Think\Controller;

class CommonMweeController extends Controller
{
    private $redis;
    public function _initialize()
    {
        $redisController = new RedisController;
        $this->redis = $redisController->connectredis();
    }

    /**
     * @param $admininfo 商户信息
     * @param $array POST要传递的参数
     * @return bool|string
     */
    public function CreateSn($admininfo, $array)
    {
        $mwee = $this->getShopMweeSecret($admininfo);
        if (false == $mwee) {
            return false;
        }
        $sn = md5(time() . $mwee['appkey']);
//        ksort($array);//array 按照 key 进行排序
//        $querystring = '';
//        foreach ($array as $key=>$value) {//字符串拼接
//            $querystring .= "{$key}={$value}&";
//        }
//        $querystring .= "sk={$mwee['appkey']}";//{$mwee['appkey']}
//        echo 'MD5字符串：'.$querystring.'<br>';
//        $sn = md5($querystring);//md5 hash
//        echo 'md5值（sn）:'.$sn.'<br>';
////        $mwee['sn'] = $sn;
        return $sn;
    }


    /**
     * 获取商户的美味不用等秘钥配置信息
     * @param $admininfo
     * @return mixed
     */
    public function getShopMweeSecret($admininfo)
    {
        $data = $this->redis->get('mwee:' . $admininfo['ukey']);
        if ($data){
            return json_decode($data, true);
        }else{
            $db = M('mwee', 'total_');
            $sel = $db->where(array('adminid'=>$admininfo['id']))->find();
            if ($sel){
                $this->redis->set('mwee:' . $admininfo['ukey'], json_encode($sel), array('ex'=>86400));
                return $sel;
            }else{
                return false;
            }
        }
    }
}