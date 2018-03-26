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

class CommonMwee
{
    private $redis;
    private static $appid = 'zhihuitu';
    public static $appkey = 'F29FB8FBE40495EA5A63D4E351E93669';
    static public $apptoken = '5b96435363684a4ec9ccf72ef9ef061f3aaddf1f';
    public function __construct()
    {
//        $redisController = new RedisController;
//        $this->redis = $redisController->connectredis();

    }

    /**
     * @param $admininfo 商户信息
     * @param $array POST要传递的参数
     * @return bool|string
     */
    public static function CreateSn($admininfo, $array)
    {
//        $mwee = $this->getShopMweeSecret($admininfo);
//        if (false == $mwee) {
//            return false;
//        }
        $sn = md5(time() . self::$appkey);
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
//        $data = $this->redis->get('mwee:' . $admininfo['ukey']);
        $data = array('appid'=>self::$appid, 'appkey'=>self::$appkey, 'apptoken'=>self::$apptoken);
        return $data;
//        if ($data){
//            return json_decode($data, true);
//        }else{
//            $db = M('mwee', 'total_');
//            $sel = $db->where(array('adminid'=>$admininfo['id']))->find();
//            if ($sel){
//                $this->redis->set('mwee:' . $admininfo['ukey'], json_encode($sel), array('ex'=>86400));
//                return $sel;
//            }else{
//                return false;
//            }
//        }
    }


    /**
     * 加密方法
     * @param $input
     * @return mixed|string
     */
    public static function encrypt($input){
        $key = self::$appkey;
        $key  = self::reflowNormalBase64($key);
        $size  = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $input = self::pkcs5_pad($input, $size);
        $td    = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv    = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, base64_decode($key), $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        $data = self::reflowURLSafeBase64($data);
        return $data;
    }


    /**
     * 解密函数
     * @param $sStr
     * @return string
     */
    public static function decrypt($sStr){
        $sKey = self::$appkey;
        $sStr = self::reflowNormalBase64($sStr);
        $sKey = self::reflowNormalBase64($sKey);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,
            base64_decode($sKey), base64_decode($sStr),
            MCRYPT_MODE_ECB);
        $dec_s     = strlen($decrypted);
        $padding   = ord($decrypted[$dec_s - 1]);
        $decrypted = substr($decrypted, 0, -$padding);
        return $decrypted;
    }

    private static function reflowNormalBase64($str){
        $str=str_replace("_","/",$str);
        $str=str_replace("-","+",$str);
        return $str;
    }

    private static function pkcs5_pad($text, $blocksize){
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private static function reflowURLSafeBase64($str){
        $str=str_replace("/","_",$str);
        $str=str_replace("+","-",$str);
        return $str;
    }


    /**
     * 店铺号获取商户的美味不用等sid
     * @param $shopnuber
     * @param $admininfo
     * @return bool
     */
    public static function poinumberToSid($shopnuber, $admininfo)
    {
        $db = M('total_mwee_shopid');
        $find = $db->where(array('shopnumber'=>$shopnuber, 'adminid'=>$admininfo['id']))->find();
        if ($find){
            return $find['sid'];
        }else{
            return false;
        }
    }
}