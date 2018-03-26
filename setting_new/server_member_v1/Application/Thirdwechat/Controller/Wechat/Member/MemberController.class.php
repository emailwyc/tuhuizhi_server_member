<?php
namespace Thirdwechat\Controller\Wechat\Member;

use Thirdwechat\Controller\Wechat\WechatcommonController;
use Thirdwechat\Controller\Thirdwechat\EventsController;
use Curl\MultiCurl;
class MemberController extends WechatcommonController
{
    // TODO - Insert your code here

    /**
     * 获取微信公众号openid列表
     * @param string $appid
     * @param string $next_openid
     * @return
     */
    protected function get_wechat_user_openid(string $appid,string $next_openid){
        $event=new EventsController;
        $authorizer_access_token=$event->authorizer_access_token($appid);
        if (false == $authorizer_access_token){
            return false;
        }else{
            $url=$this->get_user_openid;
            $url=str_replace('[ACCESS_TOKEN]',$authorizer_access_token,$url);
            $url=$url.$next_openid;
            $result=curl_https($url,array(),array(),600);
            return $result;
        }
    }

    /**
     * 获取openid信息，批量
     * @param string $appid
     * @param string $openid_json
     */
    protected function get_wechat_user_info(string $appid,string $openid_json){
        $event=new EventsController;
        $authorizer_access_token=$event->authorizer_access_token($appid);
        if (false == $authorizer_access_token){
            return false;
        }else{
            $url=$this->get_user_info_by_onenid;
            //$url=str_replace('[ACCESS_TOKEN]',$authorizer_access_token,$url);
            $url=$url.$authorizer_access_token;
            $result=curl_https($url, $openid_json, array('Accept-Charset: utf-8'), 600, true);
            return $result;
        }
    }

    protected function get_wechat_user_one(string $appid,string $openid){
        $event=new EventsController;
        $authorizer_access_token=$event->authorizer_access_token($appid);
        if (false == $authorizer_access_token){
            return false;
        }else{
            $url=$this->get_user_info_one;
            $url=str_replace('[ACCESS_TOKEN]',$authorizer_access_token,$url);
            $url=str_replace('[OPENID]',$openid,$url);
            $result=curl_https($url, array(), array(), 100);
            return $result;
        }
    }



    /**
     * 多线程获取用户信息
     * @param string $appid
     * @param array $openid_json_list
     * @param unknown $table_pre
     * @return boolean
     */
    protected function get_wechat_user_info_mulit(string $appid,array $openid_json_list,$table_pre) {
        $event=new EventsController;
        $authorizer_access_token=$event->authorizer_access_token($appid);
        $url=$this->get_user_info_by_onenid.$authorizer_access_token;

        if (false == $authorizer_access_token){
            return false;
        }else{
            require  './vendor/autoload.php';
            $multi_curl=new MultiCurl();
            foreach ($openid_json_list as $key => $val){//将数据插入多线程curl
                $multi_curl->addPost($url, $val);
            }
            $multi_curl->setTimeout(600);
            $multi_curl->setOpt('table_pre', $table_pre);
            session('table_pre',$table_pre);//回调函数里面获取不到表前缀，设置一个session
            $multi_curl->success(function ($instance){
                if (isset($instance->response->errcode)){
                    return true;
                }else if (isset($instance->response->user_info_list)){

                    $array=json_decode($instance->rawResponse,true);
                    foreach ($array['user_info_list'] as $key => $val){
                        $user['openid']=array_key_exists('openid',$val)?$val['openid'] : '';
                        $user['nickname']=array_key_exists('nickname',$val)?$val['nickname'] : '';
                        $user['sex']=array_key_exists('sex',$val)?(int)$val['sex'] : '';
                        $user['country']=array_key_exists('country',$val)?$val['country'] : '';
                        $user['province']=array_key_exists('province',$val)?$val['province'] : '';
                        $user['city']=array_key_exists('city',$val)?$val['city'] : '';
                        $user['language']=array_key_exists('language',$val)?$val['language'] : '';
                        $user['headimgurl']=array_key_exists('headimgurl',$val)?$val['headimgurl'] : '';
                        $user['unionid']=array_key_exists('unionid',$val)?$val['unionid'] : '';
                        $user['remark']=array_key_exists('remark',$val)?$val['remark'] : '';
                        $user['groupid']=array_key_exists('groupid',$val)?(int)$val['groupid'] : '';
                        $user['subscribe']=array_key_exists('subscribe',$val)?(int)$val['subscribe'] : '';
                        $user['subscribe_time']=array_key_exists('openid',$val)?(int)$val['subscribe_time'] : '';
                        $user['tagid_list']=array_key_exists('tagid_list',$val)?json_encode($val['tagid_list']) : '';
                        $userarray[]=$user;
                        unset($user);
                    }
                    writeOperationLog($userarray,'wechatuserlook');
                    $db=M('wechat_openid',session('table_pre'), 'DB_CONFIG4');
                    try {
                        $addall[]=$db->addAll($userarray);
                    } catch (Exception $e) {
                        print_r($e);
                        echo $db->_sql();
                    }

                    unset($userarray);
                    unset($db);
                    return true;
                }else {
                    return true;;
                }
            });
            $multi_curl->error(function($instance) {
//                 dump($instance);
                writeOperationLog(array('where'=>'multi_curl', 'data'=>$instance), 'getallwechatflowers');
            });
            //$multi_curl->complete($this->aaa());
            $multi_curl->start();
        }
    }


}

?>