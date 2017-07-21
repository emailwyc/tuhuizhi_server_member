<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/4/23
 * Time: 13:52
 * http://qydev.weixin.qq.com/wiki/index.php?title=OAuth验证接口
 */

namespace EnterpriseWechat\Controller\Oauth;


use EnterpriseWechat\Controller\EnterprisewConfigController;

class OauthController extends EnterprisewConfigController
{


    public function getUserInfo()
    {
        //https://open.weixin.qq.com/connect/oauth2/authorize?
        //appid=CORPID&
        //redirect_uri=REDIRECT_URI——传递
        //response_type=code&
        //scope=SCOPE&
        //agentid=AGENTID&
        //state=STATE#
        //wechat_redirect
//        $redirect = I( 'get.jumpurl' );
//        $scope = I( 'scope' );
        $get = I('get.');
        $scope = array('snsapi_base', 'snsapi_userinfo', 'snsapi_privateinfo');
        if ( $get['scope'] == false || !in_array($get['scope'], $scope)){//scope格式错误
            $this->assign('errorcode',4001)->display('getuserinfo_error');die;exit;
        }

        if( $get['scope'] == 'snsapi_privateinfo' && $get['agentid'] == false ){
            $this->assign('errorcode',4011)->display('getuserinfo_error');die;exit;
        }

        if (!isset($get['agentid'])) {
            $this->assign('errorcode',4011)->display('getuserinfo_error');die;exit;
        }

//        $keyadmin=$get['key_admin'];
//        $CorpID = $this->redis->get('enterprise:corpid:'.$keyadmin);
//        if($CorpID == false) {
//            $db = M('admin', 'total_');
//            $cid = $db->where(array('key_admin'=>$keyadmin))->find();
//            if($cid){
//                $CorpID = $cid['corpid'];
//            }else{
//                $this->assign('errorcode',4002)->display('getuserinfo_error');die;exit;
//            }
//        }
        $agentid = $get['agentid'];
        $scope = $get['scope'];
        $id=$get['id'];
        $CorpId = 'wx5ff36e7f35988f80';
        $jumpurl = $_GET['jumpurl'];
        unset($get['jumpurl']);
        unset($get['scope']);
        unset($get['agentid']);
        $params=http_build_query($get);
        $url=urldecode($jumpurl);//回调URL
        if ($params != false){
            $jumpurl= stripos($url,'?') ? $jumpurl . '%26' . $params : $jumpurl . '%3f' . $params;//组合jumpurl
        }

        $hosttype=!is_https()?'https':'https';//判断当前域名是否是https链接
        $jumpurl=base_encode($jumpurl);
        $backurl=$hosttype.'://'.$_SERVER['HTTP_HOST'].U('/EnterpriseWechat/Oauth/oauth/getCode/jumpurl/'.$jumpurl.'/scope/'.$scope.'/id/'.$id,'','');
        $url = $this->oauthcodeurl;
        $url = str_replace('[CORPID]', $CorpId, $url);
        $url = str_replace('[REDIRECT_URI]', urlencode($backurl), $url);
        $url = str_replace('[SCOPE]', $scope, $url);
        $url = str_replace('[AGENTID]', $agentid, $url);
        header('Location:' . $url);
//member.rtmap.com/EnterpriseWechat/Oauth/oauth/getUserInfo?scope=snsapi_privateinfo&jumpurl=http%3a%2f%2fwww.baidu.com%3fa%3d1%26b%3d2&agentid=1&abc=1&def=2
    }

//$suiteId, $authCorpid, $permanentCode
    public function getCode()
    {
        $jumpurl = $_GET['jumpurl'];
        $code = $_GET['code'];
        $url = $this->oauthinfo;
        $scope = $_GET['scope'];
        $id = $_GET['id'];
        $token = $this->redis->get('enterprise:suite:corpid:id:'.$id);
        if (!$token){
            $db = M('enterprise_corp_info', 'total_');
            $find = $db->where(array('id'=>$id))->find();
            if ($find){
                $token = json_encode(array('suiteid'=>$find['suiteid'], 'corpid'=>$find['corpid']));
                $this->redis->set('enterprise:suite:corpid:id:'.$id, $token);
            }else{
//                $this->assign('errorcode',4011)->display('getuserinfo_error');die;exit;
            }
        }
        $token = json_decode($token, true);
        $accesstoken = $this->getCorpAccessToken($token['suiteid'], $token['corpid']);
        $url = str_replace('[ACCESS_TOKEN]', $accesstoken, $url);
        $url = str_replace('[CODE]', $code, $url);
        $userinfo = curl_https($url);
        $jumpurl=base_decode($_GET['jumpurl']);
        $jumpurl=htmlspecialchars_decode(urldecode($jumpurl));
        if (is_json($userinfo)){
            $array = json_decode($userinfo, true);
            if (!isset($array['errcode'])) {
                if ($scope=='snsapi_base'){
                    if (false==strpos($jumpurl,'?')){
                        header('Location:'.$jumpurl.'?userid='.$array['UserId'] . '&deviceid=' . $array['DeviceId']);//跳转到指定url
                    }else{
                        header('Location:'.$jumpurl.'&userid='.$array['UserId'] . '&deviceid=' . $array['DeviceId']);//跳转到指定url
                    }
                }else{
                    $return=$this->getEnterpriserByuser_ticket($array['user_ticket'],$accesstoken);
                    if ($return!=false){
                        if (false==strpos($jumpurl,'?')){
                            header('Location:'.$jumpurl.'?userid='.$return['userid'].'&name='.$return['name'].'&department='.json_encode($return['department']).'&position='.$return['position'].'&mobile='.$return['mobile'].'&gender='.$return['gender'].'&email='.$return['email'].'&avatar='.$return['avatar'] );//跳转到指定url
                        }else{
                            header('Location:'.$jumpurl.'&userid='.$return['userid'].'&name='.$return['name'].'&department='.json_encode($return['department']).'&position='.$return['position'].'&mobile='.$return['mobile'].'&gender='.$return['gender'].'&email='.$return['email'].'&avatar='.$return['avatar'] );//跳转到指定url
                        }
                    }else{
                        $this->assign('errorcode',4002);
                        $this->display('getuserinfo_error');
                    }
                }
            }else{

            }

        }else{
            $this->assign('errorcode',4003);
            $this->display('getuserinfo_error');
        }
    }


    /**
     * 如果是scope为snsapi_userinfo或snsapi_privateinfo，则调用此方法查询用户的详细相信
     * @param $user_ticket
     */
    private function getEnterpriserByuser_ticket($user_ticket, $accesstoken)
    {
        $url = $this->getuserinfobyticket;
        $url = str_replace('[ACCESS_TOKEN]', $accesstoken, $url);
        $res = curl_https($url, json_encode(array('user_ticket'=>$user_ticket)), array(), 60, true);
        if (is_json($res)){
            $array = json_decode($res, true);
            if (!isset($array['errcode'])) {
                return $array;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}
