<?php
/**
 * 小程序生成二维码
 * 目前有A、B、C三种
 * https://mp.weixin.qq.com/debug/wxadoc/dev/api/qrcode.html
 */
namespace Thirdwechat\Service\MiniProgram\Open;

use Thirdwechat\Controller\Wechat\WechatcommonController;
use PublicApi\Controller\QiniuController;
class MiniProgramQrcodeService
{
    // TODO - Insert your code here
    /**小程序二维码接口第一个接口
     *
     * @param $data|二维码参数，具体按照微信的接口文档来
     * @param $appid|appid
     * @param $type|二维码类型
     * @return array|string
     */
    public static function miniProgramPageQrcode($data, $appid, $type)
    {
        if (!is_array($data)) {
            return ['code'=>1051, 'data'=>['data'=>$data, 'y'=>'error']];
        }
        $type = strtolower($type);//避免意外情况，转小写
        //三种方式判断
        if ($type == 'a'){
            //判断传入的参数是否有多余的
            foreach ($data as $key => $val) {
                if (!in_array($key, ['path','width','auto_color','line_color'])){
                    return ['code'=>1051, 'data'=>['data'=>$key,'type'=>'a', 'y'=>'errorkey']];
                    break;
                }
            }
            //验证数组key是否存在，之验证path，其他没有也可以请求成功
            if (!array_key_exists('path', $data)) {
                return array('code'=>1051, 'data'=>['data'=>$data,'type'=>'a', 'y'=>'noexistkey']);
            }
            $url = WechatcommonController::$wechatMiniProgramQrcodeA;
        }else if ($type == 'b'){
            //判断传入的参数是否有多余的
            foreach ($data as $key => $val) {
                if (!in_array($key, ['scene','page','width','auto_color','line_color'])){
                    return ['code'=>1051, 'data'=>['data'=>$key,'type'=>'b', 'y'=>'errorkey']];
                    break;
                }
            }
            //验证数组key是否存在，只验证scene是否存在，其他值没有也可以成功
            if (!array_key_exists('scene', $data)) {
                return array('code'=>1051, 'data'=>['data'=>$data,'type'=>'b', 'y'=>'noexistkey']);
            }
            $url = WechatcommonController::$wechatMiniProgramQrcodeB;
        }else if ($type == 'c'){
            //判断传入的参数是否有多余的
            foreach ($data as $key => $val) {
                if (!in_array($key, ['path','width'])){
                    return ['code'=>1051, 'data'=>['data'=>$key,'type'=>'c', 'y'=>'errorkey']];
                    break;
                }
            }
            //验证数组key是否存在，只验证scene是否存在，其他值没有也可以成功
            if (!array_key_exists('path', $data)) {
                return array('code'=>1051, 'data'=>['data'=>$data,'type'=>'c', 'y'=>'noexistkey']);
            }
            $url = WechatcommonController::$wechatMiniProgramQrcodeC;
        }else{
            return array('code'=>1051,'data'=>['data'=>$type, 'y'=>'typeerror']);
        }

        //格式转换
        if (array_key_exists('width', $data) && is_numeric($data['width'])) {
            $data['width'] = (int)$data['width'];
        }

        //参数完整判断
        if(in_array('', $data, true)){
            return array('code'=>1030, 'data'=>3);
        }
        if (array_key_exists('auto_color', $data)) {
            $data['auto_color'] = (bool)$data['auto_color'];
        }
        $url = str_replace('[ACCESS_TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);
        $re = curl_https($url, json_encode($data), array('Content-Type:application/json;charset=UTF-8'), 30, true);
        if (!is_json($re)) {
            return $re;
//            $img = file_put_contents(RUNTIME_PATH . 'wechat/miniprogram/pagesqrcode/'.$appid.time().'.jpg', $re);
//            return C('DOMAIN').'/Application/Runtime/' . 'wechat/miniprogram/pagesqrcode/'.$appid.time().'.jpg';
//            return $re;
        }else{
            $array = json_decode($re, true);
            return ['code'=>104, 'data'=>['data'=>$array, 'y'=>'wechatError']];
        }
    }






}

?>