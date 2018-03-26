<?php
/**
 * Created by PhpStorm.
 * https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489140610_Uavc4&token=&lang=zh_CN
 * 1、为授权的小程序帐号上传小程序代码
 * 2、获取体验小程序的体验二维码
 * 3、获取授权小程序帐号的可选类目
 * 4、获取小程序的第三方提交代码的页面配置（仅供第三方开发者代小程序调用）
 * 5、将第三方提交的代码包提交审核（仅供第三方开发者代小程序调用）
 * 6、获取审核结果——此结果在微信的回调接口里面，xml
 * 7、查询某个指定版本的审核状态（仅供第三方代小程序调用）
 * 8、查询最新一次提交的审核状态（仅供第三方代小程序调用）
 * 9、发布已通过审核的小程序（仅供第三方代小程序调用）
 * 9、发布已通过审核的小程序（仅供第三方代小程序调用）
 * User: zhang
 * Date: 01/09/2017
 * Time: 16:21
 */

namespace Thirdwechat\Service\MiniProgram\Open;


use Thirdwechat\Controller\Wechat\WechatcommonController;

class MiniProgramManagerCodeService
{
    /**
     * 1、为授权的小程序帐号上传小程序代码，比较悲催
     * @param $appid
     * @param $id 数据库id
     * @param $ext_array array，格式自定义，
     * @param $user_version 顶一个的一个版本号
     * @param $user_desc 本个版本的简介
     * @return array
     */
    public static function codeCommit($appid, $id, $ext_array, $user_version,  $user_desc)
    {
        if (!is_array($ext_array) && $ext_array)
        {
            return array('code'=>1051, 'data'=>1);
        }
        $db = D('TotalMiniprogramTemplate');
        $find = $db->where(array('id'=>$id))->find();
        if (!$find) {
            return array('code'=>102);
        }
        //应该先验证一下数据格式和$templateid是否存在，然后再提交，但是懒……
        //验证是否有值，没有默认空
        /*****************ext_json组合******************/
        $find['extpages'] = $find['extpages'] ? : array();
        $find['pages'] = $find['pages'] ? : array();
        $find['window'] = $find['window'] ? : array();
        $find['networktimeout'] = $find['networktimeout'] ? : array();
        $find['tabbar'] = $find['tabbar'] ? : array();
        $ext_arrays = array(
            'extAppid'=>$appid,
            'ext'=>$ext_array ? : '',
            'extPages'=>json_decode($find['extpages'], true),
            'pages'=>"",//pages在下面替换
            'window'=>json_decode($find['window'], true),
            'networkTimeout'=>json_decode($find['networktimeout'], true),
            'tabbar'=>json_decode($find['tabbar'], true),
        );

        //转json，第二个参数必须写这个，不能写别的，目的是要json里面都是大括号，不能有中括号
        $ext_arrays_json = json_encode($ext_arrays,JSON_FORCE_OBJECT);

        //josn_encode写了上面的参数一维数组会带key0、1、2，所以用这个不伦不类的方法替换掉吧
        $ext_arrays_json = str_replace('"pages":""', '"pages":'.$find['pages'], $ext_arrays_json);

        //不要问为什么，删了就报错：some pages in ext_json not exits（如果我没记错的话，应该这样写，因为我不想改回去看错误提示信息了）
        $ext_arrays_json = json_encode($ext_arrays_json);

        /******************************ext_json结束****************************/

        /*******************************postjson 开始*******************************/
        $array = array(
            'template_id'=>(int)$find['templateid'],
            'ext_json'=>"[aaaaaaaaaa]",//下面要替换掉
            'user_version'=>$user_version,
            'user_desc'=>$user_desc,
        );
        if (in_array('', $array, true)) {
            return array('code'=>1051, 'data'=>2);
        }
        $string = json_encode($array, JSON_UNESCAPED_UNICODE);
        $ext_json = str_replace('"[aaaaaaaaaa]"', $ext_arrays_json, $string);//文档，ext_json的值必须是string
        /*******************************postjson 结束*******************************/

        $url = WechatcommonController::$wechatMiniProgramCodeCommit;
        $url = str_replace('[TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);
        $ret = curl_https($url, $ext_json, array('Content-Type:application/json; charset=utf-8'), 20, true, 'POST');
        if (is_json($ret)) {
            $arr = json_decode($ret, true);
            if ($arr['errcode'] == 0) {
                return array('code'=>200, 'data'=>$arr);
            }else{
                return array('code'=>104, 'data'=>$arr);
            }
        }else{
            return array('code'=>104, 'data'=>$ret);
        }
    }


    /**
     * 只是单纯的获取token
     * @param $appid
     * @return bool|\Thirdwechat\Controller\Thirdwechat\Ambigous
     */
    public static function getAuthorizerAccessToken($appid)
    {
        return WechatcommonController::getAuthorizerAccessToken($appid);
    }


    /**
     * 3.获取授权小程序帐号的可选类目
     * @param $id self::templateLibraryList返回的列表中的id
     * @param $appid
     */
    public static function getCategory($appid)
    {
        $url = WechatcommonController::$wechatMiniProgramGetCategory;
//        $url = str_replace('[TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);
        $ret = curl_https($url, array('access_token'=>WechatcommonController::getAuthorizerAccessToken($appid)), array(), 20, false, 'GET');
        if (is_json($ret)) {
            $arr = json_decode($ret, true);
            if ($arr['errcode'] == 0) {
                unset($arr['errcode']);
                unset($arr['errmsg']);
                return array('code'=>200, 'data'=>$arr);
            }else{
                return array('code'=>104, 'data'=>$arr);
            }
        }else{
            return array('code'=>104, 'data'=>$ret);
        }
    }


    /**
     * 4.获取小程序的第三方提交代码的页面配置（仅供第三方开发者代小程序调用）
     * @param $appid
     * @return array
     */
    public static function getMiniProgramPage($appid)
    {
        $url = WechatcommonController::$wechatMiniProgramGetPage;
        $ret = curl_https($url, array('access_token'=>WechatcommonController::getAuthorizerAccessToken($appid)), array(), 20, false, 'GET');
        if (is_json($ret)) {
            $arr = json_decode($ret, true);
            if ($arr['errcode'] == 0) {
                unset($arr['errcode']);
                unset($arr['errmsg']);
                return array('code'=>200, 'data'=>$arr);
            }else{
                return array('code'=>104, 'data'=>$arr);
            }
        }else{
            return array('code'=>104, 'data'=>$ret);
        }
    }


    /**
     * 5.将第三方提交的代码包提交审核（仅供第三方开发者代小程序调用）
     * @param $appid
     * @param array $params
     * @return array
     */
    public static function submitAudit($appid, array $params)
    {

        foreach ($params as $key => $val) {
            if (isset($val['first_id']))$params[$key]['first_id'] = (int)$val['first_id'];
            if (isset($val['second_id']))$params[$key]['second_id'] = (int)$val['second_id'];
            if (isset($val['third_id']))$params[$key]['third_id'] = (int)$val['third_id'];
        }
        $json = json_encode(array('item_list'=>$params), JSON_UNESCAPED_UNICODE);
        if (count($params) >5 || count($params) < 1) {
            return array('code'=>1051);
        }
        $url = WechatcommonController::$wechatMiniProgramSubmitAudit;
        $url = str_replace('[TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);
        $ret = curl_https($url, $json, array('Content-Type:application/json; charset=utf-8'), 20, true, 'POST');
        if (is_json($ret)) {
            $arr = json_decode($ret, true);
            if ($arr['errcode'] == 0) {
                return array('code'=>200, 'data'=>$arr, 'item'=>$json);
            }else{
                return array('code'=>104, 'data'=>$arr);
            }
        }else{
            return array('code'=>104, 'data'=>$ret);
        }

    }


    /**
     * 7.查询某个指定版本的审核状态（仅供第三方代小程序调用）
     * @param $appid
     * @param $auditid
     */
    public static function getAuditStatus($appid, $auditid)
    {
        $url = WechatcommonController::$wechatMiniProgramGetAuditStatus;
        $url = str_replace('[TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);
        $ret = curl_https($url, json_encode(array('auditid'=>$auditid)), array('Content-Type:application/json; charset=utf-8'), 20, true, 'POST');
        if (is_json($ret)) {
            $arr = json_decode($ret, true);
            if ($arr['errcode'] == 0) {
                return array('code'=>200, 'data'=>$arr);
            }else{
                return array('code'=>104, 'data'=>$arr);
            }
        }else{
            return array('code'=>104, 'data'=>$ret);
        }
    }


    /**
     * 8.查询最新一次提交的审核状态（仅供第三方代小程序调用）
     */
    public static function getLastAuditStatus($appid)
    {
        $url = WechatcommonController::$wechatMiniProgramGetLastAudioStatus;
        $ret = curl_https($url, array('access_token'=>WechatcommonController::getAuthorizerAccessToken($appid)), array(), 20, false, 'GET');
        if (is_json($ret)) {
            $arr = json_decode($ret, true);
            if ($arr['errcode'] == 0) {
                unset($arr['errcode']);
                unset($arr['errmsg']);
                return array('code'=>200, 'data'=>$arr);
            }else{
                return array('code'=>104, 'data'=>$arr);
            }
        }else{
            return array('code'=>104, 'data'=>$ret);
        }
    }

    /**
     * 9、发布已通过审核的小程序（仅供第三方代小程序调用）
     * @param $appid
     */
    public static function release($appid)
    {
        $url = WechatcommonController::$wechatMiniProgramRelease;
        $url = str_replace('[TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);
        $ret = curl_https($url, json_encode(array(), JSON_FORCE_OBJECT), array('Content-Type:application/json; charset=utf-8'), 20, true, 'POST');
        if (is_json($ret)) {
            $arr = json_decode($ret, true);
            if ($arr['errcode'] == 0) {
                return array('code'=>200, 'data'=>$arr);
            }else{
                return array('code'=>104, 'data'=>$arr);
            }
        }else{
            return array('code'=>104, 'data'=>$ret);
        }
    }


    /**
     * 10、修改小程序线上代码的可见状态（仅供第三方代小程序调用）
     * @param $appid
     * @param $action
     * @return array
     */
    public static function changeVisitStatus($appid, $action)
    {
        if (0 === $action) {
            $array = array('action'=>'close');
        }elseif (1 === $action){
            $array = array('action'=>'open');
        }else{
            return array('code'=>1051, 'data'=>1);
        }
        $url = WechatcommonController::$wechatMiniProgramChangeVisitStatus;
        $url = str_replace('[TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);
        $ret = curl_https($url, json_encode($array, JSON_FORCE_OBJECT), array('Content-Type:application/json; charset=utf-8'), 20, true, 'POST');
        if (is_json($ret)) {
            $arr = json_decode($ret, true);
            if ($arr['errcode'] == 0) {
                return array('code'=>200, 'data'=>$arr);
            }else{
                return array('code'=>104, 'data'=>$arr);
            }
        }else{
            return array('code'=>104, 'data'=>$ret);
        }
    }

























}