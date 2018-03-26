<?php
/**
 * Created by PhpStorm.
 * 小程序模板模块
 * 微信接口地址：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1500465446_j4CgR&token=&lang=zh_CN
 * 目前微信接口有：
 * 1.获取小程序模板库标题列表、2.获取模板库某个模板标题下关键词库、3.组合模板并添加至帐号下的个人模板库
 * 1.获取帐号下已存在的模板列表、2.删除帐号下的某个模板
 * User: zhang
 * Date: 31/08/2017
 * Time: 18:30
 */

namespace Thirdwechat\Service\MiniProgram\Open;


use Thirdwechat\Controller\Wechat\WechatcommonController;

class MiniProgramTemplateService
{


    /**
     * 1.获取小程序模板库标题列表、
     * @param $page 页码
     * @param $line 每页行数
     * @param $appid
     * @return array
     */
    public static function templateLibraryList( $page, $line ,$appid)
    {
        if ($page < 0 || $line < 0 || $line > 20) {
            return array('code'=>1051);
        }
        $start = ($page - 1) * $line;//计算本次应该行数
        $url = WechatcommonController::$wechatMiniProgramtemplatelibrarylist;
        $url = str_replace('[ACCESS_TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);

        $ret = curl_https($url, json_encode(array('offset'=>$start, 'count'=>$line)), array('Content-Type:application/json; charset=utf-8'), 20, true, 'POST');
        if (is_json($ret)) {
            $arr = json_decode($ret, true);
            if ($arr['errcode'] == 0) {
                $data['data'] = $arr['list'];//数据列表
                $data['total'] = $arr['total_count'];//总数量
                $data['totalpage'] = ceil($arr['total_count'] / $line);//总行数
                $data['page'] = $page;
                return array('code'=>200, 'data'=>$data);
            }else{
                return array('code'=>104, 'data'=>$arr);
            }
        }else{
            return array('code'=>104, 'data'=>$ret);
        }
    }


    /**
     * 2.获取模板库某个模板标题下关键词库
     * @param $id self::templateLibraryList返回的列表中的id
     * @param $appid
     */
    public static function templateLibraryWxopen($id, $appid)
    {
        $url = WechatcommonController::$wechatMiniProgramtemplatelibraryword;
        $url = str_replace('[ACCESS_TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);
        $ret = curl_https($url, json_encode(array('id'=>$id)), array('Content-Type:application/json; charset=utf-8'), 20, true, 'POST');
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
     * 3组合模板并添加至帐号下的个人模板库(自行组合模板消息模板)
     * @param array $keyWords
     * @param $id
     * @param $appid
     * @return array
     */
    public static function templateWxopenAdd(array $keyWords, $id, $appid)
    {
        if (!is_array($keyWords)) {
            return array('code'=>1051, 'data'=>1);
        }
        if (count($keyWords) > 10) {
            return array('code'=>1051, 'data'=>21);
        }
        $url = WechatcommonController::$wechatMiniProgramtemplatewxopenadd;
        $url = str_replace('[ACCESS_TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);
        $ret = curl_https($url, json_encode(array('id'=>$id, 'keyword_id_list'=>$keyWords)), array('Content-Type:application/json; charset=utf-8'), 20, true, 'POST');
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
     * 1.获取帐号下已存在的模板列表，模板消息id
     * @param $page
     * @param $line
     * @param $appid
     * @return array
     */
    public static function templateWxopenList($page, $line, $appid)
    {
        if ($page < 0 || $line < 0 || $line > 20) {
            return array('code'=>1051);
        }
        $start = ($page - 1) * $line;//计算本次应该行数
        $url = WechatcommonController::$wechatMiniProgramtemplatewxopenlist;
        $url = str_replace('[ACCESS_TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);

        $ret = curl_https($url, json_encode(array('offset'=>$start, 'count'=>$line)), array('Content-Type:application/json; charset=utf-8'), 20, true, 'POST');
        if (is_json($ret)) {
            $arr = json_decode($ret, true);
            if ($arr['errcode'] == 0) {
                $data['data'] = $arr['list'];//数据列表
                $data['total'] = $arr['total_count'];//总数量
                $data['totalpage'] = ceil($arr['total_count'] / $line);//总行数
                $data['page'] = $page;
                return array('code'=>200, 'data'=>$data);
            }else{
                return array('code'=>104, 'data'=>$arr);
            }
        }else{
            return array('code'=>104, 'data'=>$ret);
        }
    }


    /**
     * 2.删除帐号下的某个模板
     * @param $templateId
     * @param $appid
     */
    public static function templateWxopenDel($templateId, $appid)
    {
        $url = WechatcommonController::$wechatMiniProgramtemplatewxopendel;
        $url = str_replace('[ACCESS_TOKEN]', WechatcommonController::getAuthorizerAccessToken($appid), $url);
        $ret = curl_https($url, json_encode(array('template_id'=>$templateId)), array('Content-Type:application/json; charset=utf-8'), 20, true, 'POST');
        if (is_json($ret)) {
            $arr = json_decode($ret, true);
            if ($arr['errcode'] == 0) {
                return array('code'=>200);
            }else{
                return array('code'=>104, 'data'=>$arr);
            }
        }else{
            return array('code'=>104, 'data'=>$ret);
        }
    }

























}