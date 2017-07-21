<?php
namespace Thirdwechat\Controller\Wechat\Message\Passive;

use Thirdwechat\Controller\Wechat\WechatcommonController;
use Thirdwechat\Controller\Thirdwechat\EndecryptionController;
use Thirdwechat\Controller\Thirdwechat\EventsController;

class AutoreplyController extends WechatcommonController
{
	private $auto_replay=null;

    public function messageType($type, $data, $get_data, $xml)
    {
        //为了微信发布验证，单独做一个判断，发布以后注释掉看看
        /**
         * 注意,如果要发布验证,请将下面三行代码打开注释,通过后可重新注释掉,减少代码运行量
         */
//         if ('wx570bc396a51b8ff8'==$get_data['appid']){
//             writeOperationLog(array('local'=>'xml','data'=>$xml),'verify');
//             $this->testmuban($data, $get_data, $xml);exit;
//         }
        //如果用户在客户端发送的消息是openid
        if ( 'openid' == trim(strtolower($data['Content'])) ){
            $this->textMessage($data, $get_data);exit;
        }
        //判断消息类型,根据消息类型做不同的返回处理
        if ('text' == $data['MsgType']){
            //如果不是发送的消息不是openid,则判断此appid是否需要自动回复
            $isauto_reply=$this->redis->get('wechat:'.$get_data['appid'].':isauto_reply');
            if (false == $isauto_reply ){
                //如果没有设置自动回复，则退出
                echostr('success');
            }
            
            //如果设置了自动回复，则获取自动回复列表
            $auto_reply=$this->redis->get('wechat:'.$get_data['appid'].':auto_reply:list');
            if (false != $auto_reply){
                $auto_replay=json_decode($auto_reply, true);
            }else{
                $auto_replay=false;
            }
            $this->auto_replay=$auto_replay;
            switch ($type){
                case 'text':
                    $array=$this->textMessage($data, $get_data);
                    break;
                case 'image':
                    $array=$this->imageMessage($data, $get_data);
                    break;
                case 'voice':
                    $array=$this->voiceMessage($data, $get_data);
                    break;
                case 'video':
                    $array=$this->videoMessage($data, $get_data);
                    break;
                case 'shortvideo':
                    $array=$this->shotvideoMessage($data, $get_data);
                    break;
                case 'location':
                    $array=$this->locationMessage($data, $get_data);
                    break;
                case 'link':
                    $array=$this->linkMessage($data, $get_data);
                    break;
                default:
                    echostr('success');
            }
        }elseif ('event' == $data['MsgType']){//如果是事件类型
            $data['Event']=strtolower($data['Event']);
            /**
             * 事件类型比较多,现阶段项目只需要"关注事件"
             */
            if ('subscribe' == $data['Event']){//如果事件是关注事件
                print str_repeat(' ', 4096);
                echo 'success';
                ob_flush();
                flush();
                //判断当前appid是否需要像红包部分发消息
                $isrequest=$this->redis->get('wechat:issendrequest:'.$get_data['appid']);
                if ('yes' == $isrequest){
                    $url='http://101.201.209.118:8080/redpack/push_follow.do';
                    http($url, array('openid'=>$data['FromUserName'],'createtime'=>$data['CreateTime'],'appid'=>$get_data['appid']),'POST');
				}
				/******关注送Ｙ币*******/
				$subParams = array('app_id'=>$get_data['appid'],'openid'=>$data['FromUserName'],'event'=>'follow','sign_key'=>'ycoin');
				$subParams['sign']= @sign($subParams);unset($subParams['sign_key']);
				$url = @C('DOMAIN')."/ClientApi/Inside/addYcoinMem";
				@curl_https($url, $subParams, array('Accept-Charset: utf-8'), 600, true);
				/******关注送Ｙ币*******/

                /*******************************优美的分割线***********************************/
                $this->EventReplayMessage($data, $get_data, $data['Event']);
            }elseif ('location' == $data['Event']){
                $this->EventReplayMessage($data, $get_data, $data['Event']);
            }elseif ('scan' == $data['Event']){
                $this->scanQrcode($data, $get_data, $data['Event'], $xml);
            }else{
                echo echostr('success');
            }
        }else {//如果是其它暂时没有考虑到的消息类型,为了防止出意外,直接返回success
            echo echostr('success');
        }
    }


    /**
     * 事件自动回复
     * 关注、进入公众号
     */
    private function EventReplayMessage($data, $get_data, $eventtype)
    {
        //判断此appid是否有发送关注推送的需求
        $is_subscribe_message=$this->redis->get('wechat:'.$eventtype.':ismessage:'.$get_data['appid']);
        if ('yes' == $is_subscribe_message){
            //如果要发送关注消息,查询要发送的消息类型:文本?图文?模板消息?还是其他,等等等等......做判断
            $messagtype=$this->redis->get('wechat:'.$eventtype.':message:type:'.$get_data['appid']);
            if ('template' ==$messagtype){//模板消息
                $template=$this->redis->get('wechat:'.$eventtype.':message:type:content:'.$get_data['appid']);//从redis中读取模板消息json
                $templateclass=new \Thirdwechat\Controller\Wechat\TemplateController();
                //也不判断有没有值了,如果获取的模板消息是空的,让微信报错把
                $template=json_decode($template, true);
                $template['touser']=$data['FromUserName'];
                $templatemsg[]=$template;
                $a=$templateclass->insideSendMessage($templatemsg,'', $get_data['appid']);
                unset($a);
            }elseif ('text' == $messagtype || 'news' == $messagtype){//文本或图文消息消息，客服消息
                //查看今天是否已经推送过，记录redis，不查询mysql，因量大不能保证速度
                $rediskey=$this->redis->get('wechat:'.$messagtype.':todaymessageissended:'.$get_data['appid'].$eventtype.':'.$data['FromUserName']);//查询出此openid是否有值

                if ($rediskey == false){
                    $text=$this->redis->get('wechat:'.$eventtype.':message:type:content:'.$get_data['appid']);//从redis中读取模板消息json
                    $textarray=json_decode($text, true);
                    $textarraya=$textarray;
                    $textarray['touser']=$data['FromUserName'];
//                writeOperationLog(array('json'=>$text, 'jsonarray'=>$textarraya,'c'=>$textarray), 'testtest');
                    $servicecontroller=new \Thirdwechat\Controller\Wechat\ServicemessageController();
                    $servicecontroller->inside_send_service_message($textarray, $get_data['appid']);
                    $losttime = strtotime(date('Y-m-d', time())) + 86400 - time();
                    $this->redis->set('wechat:'.$messagtype.':todaymessageissended:'.$get_data['appid'].$eventtype.':'.$data['FromUserName'], 'yes', array('ex'=>$losttime));//设置到今天晚上24（明天凌晨零点）点结束
                }
            }
        }
    }


    /**
     * 扫描二维码事件
     */
    private function scanQrcode($data, $get_data, $eventtype, $xml)
    {
        /**
         * 判断我方有没有需要二维码事件的地方
         */






        //输出给微信，避免curl请求超过5秒
        ob_start();
        print str_repeat(' ', 4096);
        echo 'success';
        ob_flush();
        flush();


        //判断第三方有没有需要推送xml消息的需求
        /**
         * url格式是没有问号的url，传参时，下面代码用问号传参
         */
        $is_pushxml=$this->redis->get('wechat:'.$eventtype.':ispushxml:'.$get_data['appid']);
        if ('yes' == $is_pushxml){
            //获取appid对应的商户信息，以此验证签名
            $appidInfo = $this->getAppidKeyAdmin($get_data['appid']);
            if ($appidInfo){
                $params['timestamp']=$get_data['timestamp'];
                $params['nonce']= $get_data['nonce'];
                $params['key_admin']=$appidInfo['ukey'];
                $params['sign_key']=$appidInfo['signkey'];
                $params['appid']=$get_data['appid'];

                $sign = sign($params);


                $url=$this->redis->get('wechat:'.$eventtype.':ispushxml:'.$get_data['appid'].':url');
                $url = $url.'?sign='.$sign.'&signature='.$get_data['signature'].'&timestamp='.$get_data['timestamp'].'&nonce='.$get_data['nonce'].'&appid='.$get_data['appid'];
                http($url, $xml,'POST', array('Content-Type:application/xml'), true);
            }
            //http://sit.ampmore.com/amp-svr-emall-swire/wechat/578db530e4b0fe348cf4deeb?signature={signature}&timestamp={timestamp}&nonce={nonce}
        }
//        writeOperationLog(array('data'=>$data, 'get_data'=>$get_data, 'eventtype'=>$eventtype,'xml'=>$xml), 'eventtest');
    }



    /**
     * 自动回复text类型
     * @param unknown $data
     * @param unknown $get_data
     */
    private function textMessage($data, $get_data)
    {
        $auto_reply=$this->auto_replay;//自动回复列表
        $msg=false;
        if ('openid' == trim(strtolower($data['Content'])) ){
            $msg=$data['FromUserName'];
        }else{
            //从自动回复列表中查看对应的回复
            if (false != $auto_reply){
                foreach ($auto_reply as $key => $val){
                    if ($val['name'] == $data['Content']){
                        $msg=false != $val['message'] ? $val['message'] : '您好，欢迎光临。';
                        break;
                    }
                }
            }
            //如果自动回复里面没有设置这一条，或着商户没有设置自动回复
            if (false == $msg || false == $auto_reply){
                $tuling_api_url=C('TULING_API_URL');
                $tuling_api_key=C('TULING_API_KEY');
                $array=array('key'=>$tuling_api_key,'info'=>$data['Content']);
                $header=array('Content-Type:application/json;charset=UTF-8');
                $return=http($tuling_api_url, json_encode($array), 'POST', $header, true);
                if (is_json($return)){
                    $returnarray=json_decode($return, true);
                    if ($returnarray['code'] == 100000){
                        $msg=$returnarray['text'];
                    }else{
                        echostr('success');
                    }
                }else{
                    echostr('success');
                }
            }
        }
        if (false == $msg){
            echostr('success');
        }
        
        $timestamp=time();
        $xml='<xml><ToUserName><![CDATA['.$get_data['openid'].']]></ToUserName><FromUserName><![CDATA['.$data['ToUserName'].']]></FromUserName><CreateTime>'.$timestamp.'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.$msg.']]></Content></xml>';
        writeOperationLog(array('str'=>$xml),'sendmessage');
        $endecryption= new EndecryptionController();
        $encryptionmsg=$endecryption->encryption($xml, $timestamp, $get_data['nonce']);
        
        echostr($encryptionmsg);
    }
    
    
    private function imageMessage($data, $get_data) {
        echo 'success';
    }
    
    private function voiceMessage($data, $get_data) {
        echo 'success';
    }
    
    private function videoMessage($data, $get_data) {
        echo 'success';
    }
    
    private function shotvideoMessage($data, $get_data) {
        echo 'success';
    }
    
    private function locationMessage($data, $get_data) {
        echo 'success';
    }
    
    private function linkMessage($data, $get_data) {
        echo 'success';
    }
    
    //微信发布验证，一个大大的    W T F 
    private function testmuban($data, $get_data, $xml)
    {
        if (isset($data['Event']) || $data['MsgType']=='event'){
            $msg=$data['Event'].'from_callback';
            $timestamp=time();
            $xml='<xml><ToUserName><![CDATA['.$get_data['openid'].']]></ToUserName><FromUserName><![CDATA['.$data['ToUserName'].']]></FromUserName><CreateTime>'.$timestamp.'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.$msg.']]></Content></xml>';
            writeOperationLog(array('str'=>$xml),'sendmessage');
            $endecryption= new EndecryptionController();
            $encryptionmsg=$endecryption->encryption($xml, $timestamp, $get_data['nonce']);
            
            echostr($encryptionmsg);
            
            
            
        }else if ('TESTCOMPONENT_MSG_TYPE_TEXT' == $data['Content']){
            $timestamp=time();
            $xml='<xml><ToUserName><![CDATA['.$get_data['openid'].']]></ToUserName><FromUserName><![CDATA['.$data['ToUserName'].']]></FromUserName><CreateTime>'.$timestamp.'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[TESTCOMPONENT_MSG_TYPE_TEXT_callback]]></Content></xml>';
            writeOperationLog(array('str'=>$xml),'sendmessage');
            $endecryption= new EndecryptionController();
            $encryptionmsg=$endecryption->encryption($xml, $timestamp, $get_data['nonce']);
            echostr($encryptionmsg);
            writeOperationLog(array('local'=>'textContent','data'=>$encryptionmsg),'verify');
            exit();
        }else{
            //echo 'success';//exit;
            ob_start();//打开缓冲区
//             echo 'success';
//             ob_flush();//送出当前缓冲内容，不会输出
//             flush();//输出送出的缓冲内容
/**
 * proxy_buffering off;
gzip off;
fastcgi_keep_conn on;
 */
            print str_repeat(' ', 4096);
            echo 'success';
            ob_flush();
            flush();
            
//             $b= (mb_strpos($xml,"[QUERY_AUTH_CODE:"));
//             $c= (mb_strpos($xml,"]]>"));
//             //echo mb_substr($msg,$b+17,$c-$b-17);
//             $query_auth_code=mb_substr($xml,$b,$c-$b);
//             $str=str_replace('[QUERY_AUTH_CODE:','',$query_auth_code);
            
            $xmlobj = new \DOMDocument();
            $xmlobj->loadXML($xml);
            
            //获取MsgType，根据MsgType处理消息
            $array_e = $xmlobj->getElementsByTagName('Content');
            $content = $array_e->item(0)->nodeValue;//获取xml里面的CDATA[QUERY_AUTH_CODE
            $content = str_replace('QUERY_AUTH_CODE:', '', $content);
            
            //“使用授权码换取公众号的授权信息”API
            $events=new EventsController();
            $component_access_token=$events->component_access_token();
            $auth_url='https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token='.$component_access_token;
            $arr=array('component_appid'=>$this->appId,'authorization_code'=>$content);
            writeOperationLog(array('str'=>$content),'auth_query_code_content');
            $return=curl_https($auth_url, json_encode($arr), array(), 30, true);
            writeOperationLog(array('local'=>'auth','data'=>json_decode($return, true)),'verify');
            if (is_json($return)){
                $array=json_decode($return,true);
                if (!isset($array['errcode'])){
                    $data['wechat_appid']=$get_data['appid'];//授权方的appid
                    $data['wechat_authorizer_refresh_token']=null != $array['authorization_info']['authorizer_refresh_token'] ? $array['authorization_info']['authorizer_refresh_token'] : '';//设置刷新token
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
                    //设置选项值，要请求的东西过多
                    //$this->set_authorizer_option($array['authorization_info']['authorizer_appid']);
                    if (null != $sel){
                        $savedata=$db->where(array('wechat_id'=>$sel['wechat_id']))->save($data);
                    }else{
                        $savedata=$db->add($data);echo $db->_sql();
                    }
                    if ($savedata !== false){
                        //设置authorizer_access_token
                        $this->redis->set('wechat:authorizer_access_token:'.$array['authorization_info']['authorizer_appid'],$array['authorization_info']['authorizer_access_token'],$array['authorization_info']['expires_in']);
                        //设置刷新token,很重要，微信方只给一次，一次性的!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!必须同时保存到mysql和redis
                        $this->redis->set('wechat:authorizer_refresh_token:'.$array['authorization_info']['authorizer_appid'],$array['authorization_info']['authorizer_refresh_token']);
                        //$back=$events->get_authorizer_info($array['authorization_info']['authorizer_appid']);
                    }
                }else{
                    
                }
                ////////////////////请求授权消息结束//////////////////////
                $sign=sign(array('key_admin'=>'9365b89b4b20d3ce5063e6f318824404','sign_key'=>'1f0b444b4420ff64b55987a8871e122b'));
                $url='https://mem.rtmap.com/Thirdwechat/Wechat/Servicemessage/send_service_message?key_admin=9365b89b4b20d3ce5063e6f318824404&sign='.$sign;
                
                $msg=array('touser'=>$get_data['openid'],'msgtype'=>'text','text'=>array('content'=>$content.'_from_api'));
                
                $verify=curl_https($url,json_encode($msg),array(), 30, true);
                writeOperationLog(array('local'=>'sendmessage','data'=>json_decode($verify, true)),'verify');
                exit;
                
            }
        
        }
    }
}

?>
