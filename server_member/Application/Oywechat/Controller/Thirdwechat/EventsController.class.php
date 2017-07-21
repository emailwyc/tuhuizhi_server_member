<?php
namespace Oywechat\Controller\Thirdwechat;
use Common\Controller\RedisController;
use Oywechat\Controller\Thirdwechat\ThirdwechatcommonController;

/**
 * @警告：Coentent-Type不可设置为multipart/form-data
 * @desc    第三方公众号授权相关
 * @author ut
 *
 */
class EventsController extends ThirdwechatcommonController{
    // TODO - Insert your code here
    
    
    public function mpEvent(){
        ob_clean();
        echo 'success';
        exit;
        die;
        $appid=$_GET['appid'];
        $signature=$_GET['signature'];
        $timestamp=$_GET['timestamp'];
        $nonce=$_GET['nonce'];
        $openid=$_GET['openid'];
        $encrypt_type=$_GET['encrypt_type'];
        $msg_signature=$_GET['msg_signature'];
        $postData=file_get_contents('php://input');
        $array=array(
            'appid'=>$appid,
            'signature'=>$signature,
            'timestamp'=>$timestamp,
            'nonce'=>$nonce,
            'openid'=>$openid,
            'encrypt_type'=>$encrypt_type,
            'msg_signature'=>$msg_signature,
            'mpEvent'=>$postData
        );
        writeOperationLog($array,'oysdmpEvent');
        $endecryption=new EndecryptionController();
        $endecryption->decryption($msg_signature, $timestamp, $nonce, $postData, $appid, $array);
        
        
//         dump($_GET);
        //echo 'success';
    }
    
    
    
    /**
     * 这个方法要接收多种类型的数据，要做好判断
     */
    public function authEvents(){
//         $msgSignature='56e84dc95dd5162ca6709034408560a75a2d52a1';//
//         $timestamp='1466046747';//I('timestamp');
//         $nonce='914214711';//I('nonce');
//         $postData='<xml>
//     <AppId><![CDATA[wxcdf6eb2f473255d5]]></AppId>
//     <Encrypt><![CDATA[9kWCW67VT/vrJJ6XjV/sCXac9JVHISccCTuuJ0tmRMjwCo0asi4914hhWxrkE2iCWmDlIUmYnjkYGsjeJAG5Y+wrO6nEux2lbmopuQ3LL5o53b3lIeQVQc5VXqBOo6k+UeiIWw68ExnKGD/RX4h8lMYHwl00b14i2ayYDKHVh9RYMLmaJnPrpLIZOFmwU03W0DRkwrasFPEF9GhduG8am8BajnceCPGPhPGP+cncGjag+WQXPOqYnPIiILiTe49dh/4XUf1zThrRuVTbrJpTWTQdZi2wKdBkfbwlle21kE9/r4VHXq3AYW6fVjQU36TdP+Up54b1PUTK/fj0jNmUvb+eHoGT5a8/zG0CkLZceXBlb5VoLV1ySMsn2e01W6t33LvX44HI9DhOIMCGVHkw95mZmPBAIEw3LkEwr2FCOZ54AK6WN4b4ByA+jlqlIuJwJxlEddsAPUqGTJWvXrG9Sw==]]></Encrypt>
// </xml>';//file_get_contents('php://input');
//         //ticket@@@HND9m0OqRhK-dPWsL2O4P3XfNOk-7dl_A9bXc6aXSg6ELLEYIl1fIxbuOO9XyYxXGer23_viW1XHdd1UR_vIuw
        
        $msgSignature=$_GET['msg_signature'];//I('msg_signature');
        $timestamp=$_GET['timestamp'];
        $nonce=$_GET['nonce'];
        $postData=file_get_contents('php://input');
        

        //通过解密方法获取ticket
        $component_verify_ticket=$this->get_component_verify_ticket($postData,$msgSignature,$timestamp,$nonce);
        writeOperationLog(array('xml_encrpty'=>$postData,'xml_json'=>json_encode($component_verify_ticket),'msg_signature'=>$msgSignature,'timestamp'=>$timestamp,'nonce'=>$nonce),'oysdauthEvents');
        
        
        if (0==$component_verify_ticket['code']){
            $redis = new RedisController();
            $rediss=$redis->connectredis();
            if ('component_verify_ticket'==$component_verify_ticket['infotype']){//每十分钟推送ticket事件
                //如果解密成功则设置ticket
                $rediss->set('oysd:wechat:component_verify_ticket',$component_verify_ticket['component_verify_ticket']);//保存ticket，因为微信每隔十分钟请求一次，所以无所谓设置时长
                //$rediss->close();
                echo 'success';
            }elseif ('authorized'==$component_verify_ticket['infotype']){//授权成功通知
                echo 'success';
            }elseif ('updateauthorized'==$component_verify_ticket['infotype']){//授权更新通知
                echo 'success';
            }elseif ('unauthorized'==$component_verify_ticket['infotype']){//取消授权通知
                $db=M('thirdwechatinfo','total_');
                $db->where(array('wechat_appid'=>$component_verify_ticket['appid']))->save(array('wechat_isauth'=>0));
                echo 'success';
            }
        }else{
            //错误处理
            echo 'success';//必须给微信返回success
        }
        
        
        //echo 'success';
    }
    
    /**
     * @desc    解密获取component_verify_ticket
     * @param unknown $postData xml数据体
     * @param unknown $msgSignature    签名串，对应URL参数的msg_signature
     * @param unknown $timestamp    时间戳，对应URL参数的timestamp
     * @param unknown $nonce    随机串，对应URL参数的nonce
     * @return unknown    从xml数据体里面解密出来的component_verify_ticket
     */
    private function get_component_verify_ticket($postData,$msgSignature,$timestamp,$nonce) {
        //引入微信提供的解密类
        require_once 'Class/Wechatthird/wxBizMsgCrypt.php';
        $wx=new \WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appId);
        
        //开始解密
        $msg='';
        $decryptmsg=$wx->decryptMsg($msgSignature, $timestamp, $nonce, $postData, $msg);
        $ticket='';
        if ($decryptmsg == 0) {
            $xml = new \DOMDocument();
            $xml->loadXML($msg);
            //获取ticket，可能没有
            $array_e = $xml->getElementsByTagName('ComponentVerifyTicket');
            $component_verify_ticket = $array_e->item(0)->nodeValue;
            //获取infotype
            $infotypex=$xml->getElementsByTagName('InfoType');
            $infotype= $infotypex->item(0)->nodeValue;
            //获取appid
            $appidx=$xml->getElementsByTagName('AuthorizerAppid');
            $appid=$appidx->item(0)->nodeValue;
            
            
            $ticket=array('code'=>0,'component_verify_ticket'=>$component_verify_ticket,'infotype'=>$infotype,'appid'=>$appid);
        } else {
            $ticket=array('code'=>$decryptmsg);
        }
        return $ticket;
    }
    
    
    
    /**
     * @desc    获取component_access_token
     * @param unknown $component_verify_ticket
     */
    private function get_component_access_token($component_verify_ticket){
        
        $params['component_appid']=$this->appId;
        $params['component_appsecret']=$this->appsecret;
        $params['component_verify_ticket']=$component_verify_ticket;
        $header=array('Content-Type:application/json;charset=UTF-8');
        $paramss=json_encode($params);
        $token=curl_https($this->component_access_token_url,$paramss,$header,30,true);
        if (is_json($token)){
            $token=json_decode($token,true);
            //dump(array_key_exists('errcode',$token));
            if (!array_key_exists('errcode',$token)){//如果没有errorcode
                $redis = new RedisController();
                $rediss=$redis->connectredis();
                $rediss->set('oysd:wechat:component_access_token',$token['component_access_token'],array('ex'=>$token['expires_in']-120));
                return $token['component_access_token'];
            }else{
                echo 2;
                dump($token);
            }
        }else{
            echo 3;
        }
    }
    
    
    /**
     * @desc    获取预授权码，预授权码用于公众号授权时的第三方平台方安全验证。
     */
    public function get_pre_auth_code(){
        $redis = new RedisController();
        $rediss=$redis->connectredis();
        $component_access_token=$rediss->get('oysd:wechat:component_access_token');
        if (empty($component_access_token)){//如果token不存在，则重新获取
            $component_access_token=$this->get_component_access_token($rediss->get('oysd:wechat:component_verify_ticket'));
        }
        $url=$this->pre_auth_code_url.$component_access_token;//拼接url
        $params['component_appid']=$this->appId;
        $header=array('Content-Type:application/json;charset=UTF-8');
        $code=curl_https($url,json_encode($params),$header,30,true);
        if (is_json($code)){
            $code=json_decode($code,true);
            if (!array_key_exists('errcode',$code)){
                $rediss->set('oysd:wechat:pre_auth_code',$code['pre_auth_code'],array('ex'=>$code['expires_in']));
//                 echo $code['pre_auth_code'];
                return $code['pre_auth_code'];
            }else{
                
            }
        }else{
            
        }
        
    }
    
    
    /**
     * @desc    使用授权码换取公众号的接口调用凭据和授权信息，授权方在腾讯网站扫描二维码、选择权限并点击授权后，会跳转到此方法
     */
    public function getauthorizer(){
        $redis = new RedisController();
        $rediss=$redis->connectredis();
        
        //保存code，不知道保存后有啥用,每次请求这个地址，重新获取公众号信息，实在不知道这个code用有效期干嘛，既然有有效期，就保存吧
        $rediss->set('oysd:wechat:auth_code',$_GET['auth_code'],$_GET['expires_in']-20);
        
        //获取token
        $component_access_token=$rediss->get('oysd:wechat:component_access_token');
        if (empty($component_access_token)){//如果token不存在，则重新获取
            $component_access_token=$this->get_component_access_token($rediss->get('oysd:wechat:component_verify_ticket'));
        }
        //下面组合请求条件
        $url=$this->gettoken_url.$component_access_token;
        $params['component_appid']=$this->appId;
        $params['authorization_code']=$_GET['auth_code'];
        $header=array('Content-Type:application/json;charset=UTF-8');
        $return=curl_https($url,json_encode($params),$header,30,true);
        if (is_json($return)){
            $array=json_decode($return,true);
            
            $data['wechat_appid']=$array['authorization_info']['authorizer_appid'];//授权方的appid
            $data['wechat_authorizer_refresh_token']=$array['authorization_info']['authorizer_refresh_token'];//设置刷新token
            //循环获取被授权的权限集，全是数字
            $str='';
            foreach ($array['authorization_info']['func_info'] as $key => $val){
                $str.=$val['funcscope_category']['id'].',';
            }
            $str=substr($str,0,-1);
            $data['wechat_funcscope_categorys']=$str;
            $data['wechat_isauth']=1;
            $data['wechat_createtime']=date('Y-m-d H:i:s');
            $db=M('thirdwechatinfo','total_');
            $sel=$db->where(array('wechat_appid'=>$data['wechat_appid']))->find();
            //设置选项值，要请求的东西过多，放在后台手动请求,今天腾讯好像抽风了
            //$this->set_authorizer_option($array['authorization_info']['authorizer_appid']);
            if (null != $sel){
                $savedata=$db->where(array('wechat_id'=>$sel['wechat_id']))->save($data);
            }else{
                $savedata=$db->add($data);
            }
            if ($savedata !== false){
                //设置authorizer_access_token
                $rediss->set('oysd:wechat:authorizer_access_token:'.$array['authorization_info']['authorizer_appid'],$array['authorization_info']['authorizer_access_token'],$array['authorization_info']['expires_in']);
                //设置刷新token,很重要，微信方只给一次，一次性的!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!必须同时保存到mysql和redis
                $rediss->set('oysd:wechat:authorizer_refresh_token:'.$array['authorization_info']['authorizer_appid'],$array['authorization_info']['authorizer_refresh_token']);
                $back=$this->get_authorizer_info($array['authorization_info']['authorizer_appid']);
                if ($back==true)
                    $this->display('getauthorizer_success');
                    //echo '<div style="margin:0 auto; text-align:center">授权成功页面，需要设计</div>';
                else 
                    $this->display('getauthorizer_error');
                    //echo '<div style="margin:0 auto; width:50%;">授权失败页面，需要设计</div>';
            }
        }else{
            $this->display('getauthorizer_error');
            //echo '<div style="margin:0 auto; width:50%;">授权失败页面，需要设计</div>';
        }
    }
    
    
    
    /**
     * @desc    6、获取授权方的公众号帐号基本信息
     * @param unknown $authorizer_appid
     */
    public function get_authorizer_info($authorizer_appid){
        $redis = new RedisController();
        $rediss=$redis->connectredis();
        //获取token
        $component_access_token=$rediss->get('oysd:wechat:component_access_token');
        if (empty($component_access_token)){//如果token不存在，则重新获取
            $component_access_token=$this->get_component_access_token($rediss->get('oysd:wechat:component_verify_ticket'));
        }
        $url=$this->get_authorizer_info_url.$component_access_token;
        $params['component_appid']=$this->appId;
        $params['authorizer_appid']=$authorizer_appid;
        $header=array('Content-Type:application/json;charset=UTF-8');
        $return=curl_https($url,json_encode($params),$header,30,true);
        if (is_json($return)){
            $array=json_decode($return,true);
            //dump($array);//die;
            $db=M('third_authorizer_info','total_');
            $data['nick_name']=$array['authorizer_info']['nick_name'];
            $data['head_img']=$array['authorizer_info']['head_img'];
            $data['service_type_info']=$array['authorizer_info']['service_type_info']['id'];
            $data['verify_type_info']=$array['authorizer_info']['verify_type_info']['id'];
            $data['user_name']=$array['authorizer_info']['user_name'];
            $data['business_info_open_store']=$array['authorizer_info']['business_info']['open_store'];
            $data['business_info_open_scan']=$array['authorizer_info']['business_info']['open_scan'];
            $data['business_info_open_pay']=$array['authorizer_info']['business_info']['open_pay'];
            $data['business_info_open_card']=$array['authorizer_info']['business_info']['open_card'];
            $data['business_info_open_shake']=$array['authorizer_info']['business_info']['open_shake'];
            $data['alias']=$array['authorizer_info']['alias'];
            $data['qrcode_url']=$array['authorizer_info']['qrcode_url'];
            //循环获取被授权的权限集，全是数字
            $str='';
            foreach ($array['authorization_info']['func_info'] as $key => $val){
                $str.=$val['funcscope_category']['id'].',';
            }
            $str=substr($str,0,-1);
            $data['authorization_info']=$str;
            $data['appid']=$array['authorization_info']['authorizer_appid'];
            $data['createtime']=date('Y-m-d H:i:s');
            
            $sel=$db->where(array('appid'=>$data['appid']))->find();
            if ($sel!=null){
                $savedata=$db->where(array('appid'=>$data['appid']))->save($data);
            }else{
                $savedata=$db->add($data);
            }
            if ($savedata !== false){
                return true;
            }else{
                return false;
            }
        }else{
            //不是标准的json，格式错误，可能是网络原因没有获取到信息，也可能是别的原因
        }
    }
    
    
    
    
    
    
    
    /**
     * @desc    5、获取（刷新）授权公众号的接口调用凭据（令牌），两小时到期后，此授权方的token要重新获取
     * @param unknown $authorizer_appid
     */
    public function authorizer_refresh_token($authorizer_appid){
        $redis = new RedisController();
        $rediss=$redis->connectredis();
        //获取token
        $component_access_token=$rediss->get('oysd:wechat:component_access_token');
        if (empty($component_access_token)){//如果token不存在，则重新获取
            $component_access_token=$this->get_component_access_token($rediss->get('oysd:wechat:component_verify_ticket'));
        }
        $db=M('thirdwechatinfo','total_');
        //组合请求条件
        $url=$this->authorizer_refresh_token_url.$component_access_token;
        $params['component_appid']=$this->appId;
        $params['authorizer_appid']=$authorizer_appid;
        $params['authorizer_refresh_token']=$rediss->get('oysd:wechat:authorizer_refresh_token:'.$authorizer_appid);//获取这个授权方的刷新token
        if (null == $params['authorizer_refresh_token']){
            $wechatinfo=$db->where(array('wechat_appid'=>$authorizer_appid))->find();
            $params['authorizer_refresh_token']=$wechatinfo['wechat_authorizer_refresh_token'];
        }
        $header=array('Content-Type:application/json;charset=UTF-8');
        $return=curl_https($url,json_encode($params),$header,30,true);
        if (is_json($return)){
            $log['appid']=$authorizer_appid;
            $log['returndata']=$return;
            writeOperationLog($log,'oysdauthorizer_access_token');
            $array=json_decode($return,true);
            if (isset($array['errcode'])){
                return false;
            }else{
                //设置authorizer_access_token
                $rediss->set('oysd:wechat:authorizer_access_token:'.$authorizer_appid,$array['authorizer_access_token'],$array['expires_in']);
                //设置刷新token
                $rediss->set('oysd:wechat:authorizer_refresh_token:'.$authorizer_appid,$array['authorizer_refresh_token']);
                
                $data['wechat_authorizer_refresh_token']=$array['authorizer_refresh_token'];
                $db->where(array('wechat_appid'=>$authorizer_appid))->save($data);//重置数据库的刷新token
                return $array['authorizer_access_token'];
            }
        }else return false;
    }
    
    
    
    /**
     * @desc    7、获取授权方的选项设置信息，根据获取到的结果，可以进行第8步
     * @param unknown $option_name
     * @param unknown $authorizer_appid
     */
    private function get_authorizer_option($option_name,$authorizer_appid){
        $redis = new RedisController();
        $rediss=$redis->connectredis();
        //获取token
        $component_access_token=$rediss->get('oysd:wechat:component_access_token');
        if (empty($component_access_token)){//如果token不存在，则重新获取
            $component_access_token=$this->get_component_access_token($rediss->get('oysd:wechat:component_verify_ticket'));
        }
        echo $rediss->get('oysd:wechat:component_access_token');
        $urls=$this->get_authorizer_option_url.$component_access_token;
        echo $urls;
        $params['component_appid']=$this->appId;
        $params['authorizer_appid']=$authorizer_appid;
        $params['option_name']=$option_name;
        $header=array('Content-Type:application/json;charset=UTF-8');
        $return=curl_https($urls,json_encode($params),$header,30,true);
        dump($return);
        if (is_json($return)){
            $array=json_decode($return,true);
            if (array_key_exists('option_value',$array)){
                return $array['option_value'];
            }else{echo 'erroraa<br>';
                return false;
            }
            
        }else{
            echo 'error<br>';
            
        }
        
        
        
        
        
        
    }
    
    
    
    /**
     * @desc    8、设置授权方的选项信息，第一步先请求get_authorizer_option($option_name,$authorizer_appid);方法，获取选项状态，如果条件符合，则不需要设置
     * @param unknown $authorizer_appid
     * location_report(地理位置上报选项)	   0	无上报；1	进入会话时上报；2	每5s上报
     * voice_recognize（语音识别开关选项）	0	关闭语音识别；1	开启语音识别
     * customer_service（多客服开关选项）	0	关闭多客服；1	开启多客服
     */
    private function set_authorizer_option($authorizer_appid){
        $redis = new RedisController();
        $rediss=$redis->connectredis();
        //获取token
        $component_access_token=$rediss->get('oysd:wechat:component_access_token');
        if (empty($component_access_token)){//如果token不存在，则重新获取
            $component_access_token=$this->get_component_access_token($rediss->get('oysd:wechat:component_verify_ticket'));
        }
        
        $option_names=$this->option_names;//选项名和选项值数组（选项名和我们想要的选项值）
        $data=null;
        foreach ($option_names as $key => $val){echo $key.'<br>';
            //第一步先去get_authorizer_option($option_name,$authorizer_appid);方法，获取状态，符合条件则continue
            $option_status=$this->get_authorizer_option($key, $authorizer_appid);
            if ($option_status==$val){
                continue;
            }else {//如果不符合想要设置的值，则设置
                $url=$this->get_authorizer_option_url.$component_access_token;
                $params['component_appid']=$this->appId;
                $params['authorizer_appid']=$authorizer_appid;
                $params['option_name']=$key;
                $params['option_value']=$val;
                $header=array('Content-Type:application/json;charset=UTF-8');
                $return=curl_https($url,json_encode($params),$header,30,true);
                if (is_json($return)){
                    $data[$key]=$val;//特别注意，这里的$key和数据库里面的字段名是一样的
                }
            }break;
        }
        //如果设置项有东西
        if (count($data) > 0){
            $db=M('third_authorizer_info','total_');
            $sel=$db->where(array('appid'=>$authorizer_appid))->select();
            if ($sel!=null){
                $savedata=$db->where(array('appid'=>$authorizer_appid))->save($data);
            }else{
                $savedata=$db->add($data);
            }echo 1;
            return true;
        }else{echo 2;
            return true;
        }
        
        
        
        
        
    }
    
    
    
    /**
     * @desc    第三方微信平台的token
     */
    public function component_access_token(){
        $rediss = new RedisController();
        $redis=$rediss->connectredis();
        $component_access_token=$redis->get('oysd:wechat:component_access_token');
        if (empty($component_access_token)){//如果token不存在，则重新获取
            $component_access_token=$this->get_component_access_token($redis->get('oysd:wechat:component_verify_ticket'));
        }
        return $component_access_token;
    }
    
    
    /**
     * @desc    刷新第5步
     * @param unknown $appid
     * @return boolean|Ambigous <boolean, unknown>
     */
    public function authorizer_access_token($appid){
        $rediss = new RedisController();
        $redis=$rediss->connectredis();
        $authorizer_access_token=$redis->get('oysd:wechat:authorizer_access_token:'.$appid);
        if (empty($authorizer_access_token)){
            $authorizer_access_token=$this->authorizer_refresh_token($appid);
        }
        if (empty($authorizer_access_token)){
            return false;
        }else{
            return $authorizer_access_token;
        }
    }
    
    
}

?>