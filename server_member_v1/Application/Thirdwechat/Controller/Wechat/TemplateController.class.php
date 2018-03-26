<?php
/**
 * 模板消息对外接口
 */

namespace Thirdwechat\Controller\Wechat;

use Thirdwechat\Controller\Wechat\Message\Tmplate\WechattemplateController;

class TemplateController extends WechattemplateController
{
    
    public function getAlltemplate()
    {
        $key_admin=I('key_admin');
        $time=I('timestamp');
        $sign=I('sign');
        $msg=$this->commonerrorcode;
        if ('' == $key_admin || '' == $time){
            $msg['code']=100;
        }else{
            $admininfo=$this->getMerchant($key_admin);
            $params=I('param.');
            unset($params['sign']);
            $wetime=time();
            $ct=time()-$params['timestamp'];
            //如果计算得出来的秒数大于正负60秒，则判定请求方的服务器时间不对
            if ($ct > 60 || $ct < -60){
                returnjson(array('code'=>1056), $this->returnstyle, $this->callback);
            }
            if (sign($params) != $sign){//签名不对
                returnjson(array('code'=>1002),$this->returnstyle,$this->callback);
            }
            if (isset($admininfo['wechat_appid']) && !empty($admininfo['wechat_appid'])){
                $result=$this->get_all_private_template($admininfo['wechat_appid']);
                if (is_json($result)){
                    $array=json_decode($result,true);
                    if (!isset($array['errcode'])){
                        //存入数据库
                        if (null != $array['template_list']){
                            M('','','DB_CONFIG1')->execute('TRUNCATE '.$admininfo['pre_table'].'template_message');
                            $db=M('template_message',$admininfo['pre_table']);
                            $db->addAll($array['template_list']);
                        }
                        $msg['code']=200;
                        $msg['data']=$array['template_list'];
                    }else{
                        $msg['code']=101;
                    }
                }else {
                    $msg['code']=101;
                }
            }else{
                $msg['code']=102;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }


    /**
     * 内部调用，获取全部模板消息类型
     * @param $appid
     */
    public function InsideGetAlltemplate($appid)
    {
        $result=$this->get_all_private_template($appid);
        if (is_json($result)){
            $array=json_decode($result,true);
            if (!isset($array['errcode'])){
                //存入数据库
                return array('code'=>200, 'data'=>$array);
            }else{
                return array('code'=>104, 'data'=>$array);
            }
        }else {
            return array('code'=>104, 'data'=>$result);
        }
    }





    
    /**
     * 保存
     */
    public function addTemplate()
    {
        
    }
    
    /**
     * 外部接口调用发送模板消息
     */
    public function outsideSendMessage()
    {
        $send=file_get_contents('php://input');
        $key_admin=$_GET['key_admin'];
        $sign=$_GET['sign'];
        $msg=$this->commonerrorcode;
        if (false==$send || empty($key_admin) || !is_json($send)){
            $msg['code']=100;
        }else{
            $array=json_decode($send,true);
            $nums=count($array);
            $isdo=$this->redis->get('wechat:'.$key_admin.':issendmessage');
            if ($nums > 1 && true == $isdo){
                $msg['code']=1048;
            }else{
                $admininfo=$this->getMerchant($key_admin);
                //验证签名
                $params=array('key_admin'=>$key_admin,'sign_key'=>$admininfo['signkey']);
                if (sign($params) != $sign){
                    $msg['code']=1002;
                }else{
                    if (!isset($admininfo['wechat_appid']) || empty($admininfo['wechat_appid'])){
                        $msg['code']=102;
                    }else{
                        
                        if (isset($array[0]['touser']) || 10000 > count($array)){
                            $return=$this->send_message_template($array,$admininfo['wechat_appid']);
                            //$this->redis->get('wechat:'.$key_admin.':issendmessage');
                            if ((null == $return ||  count($return) < count($array)) && false !== $return){
                                if ($nums > 1){
                                    $this->redis->set('wechat:'.$key_admin.':issendmessage','yes',array('ex'=>300));
                                }
                                $msg['code']=200;
                                $msg['data']=$return;
                            }else{
                                $msg['code']=104;
                                $msg['data']=$return;
                            }
                        }else{
                            $msg['code']=1042;
                        }
                    }
                }
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 内部接口调用发送模板消息
     * @param array $send，模板消息，二位数组
     * @param string $key_admin
     */
    public function insideSendMessage(array $send=array(), string $key_admin=null, $appid=null)
    {
        $msg=$this->commonerrorcode;
        if (null != $appid){
            if (isset($send[0]['touser'])){
                $this->send_message_template($send, $appid);
                $msg=200;
            }else{
                $msg['code']=1042;
            }
        }else{
            if (false==$send || empty($key_admin) || !is_array($send)){
                $msg['code']=100;
            }else{
                $admininfo=$this->getMerchant($key_admin);
                if (!isset($admininfo['wechat_appid']) || empty($admininfo['wechat_appid'])){
                    $msg['code']=102;
                }else{
                    if (isset($send[0]['touser'])){
                        $this->send_message_template($send,$admininfo['wechat_appid']);
                        $msg=200;
                    }else{
                        $msg['code']=1042;
                    }
                }
            }
        }
        
        return $msg;
    }
    
    
    /**
     * 发送模板消息
     * @param unknown $data
     * @param string $appid
     */
    private function send_message_template($send=array(), string $appid)
    {
        if (false == $send || empty($appid)){
            return false;
        }else{
            $return = $this->send_message($send,$appid);
            return $return;
        }
    }
}

?>