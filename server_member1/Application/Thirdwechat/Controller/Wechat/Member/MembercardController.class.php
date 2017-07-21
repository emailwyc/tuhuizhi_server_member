<?php
namespace Thirdwechat\Controller\Wechat\Member;

use Thirdwechat\Controller\Thirdwechat\EventsController;
use Curl\MultiCurl;

/**
 * 获取微信会员卡数据
 * @author kaifeng
 *
 */
class MembercardController extends MemberController
{
    // TODO - Insert your code here
    
    /**
     * 批量查询卡券列表
     */
    protected function BatchGetCard(string $table_pre, string $appid, $post_data='{"offset":0,"count":50,"status_list":["CARD_STATUS_DISPATCH"]}'){
        $db=M('','','DB_CONFIG1');
        $c=$db->execute('SHOW TABLES like "'.$table_pre.'wechat_card_info"');
        if (1 === $c){
            $db->execute('TRUNCATE '.  $table_pre.'wechat_card_info');
        }else{
            $sql="
DROP TABLE IF EXISTS  `".$table_pre."wechat_card_info`;
CREATE TABLE `".$table_pre."wechat_card_info` (
  `id` int(200) NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL DEFAULT '',
  `nickname` varchar(200) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `sex` varchar(10) NOT NULL DEFAULT '',
  `USER_FORM_INFO_FLAG_NAME` varchar(50) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `USER_FORM_INFO_FLAG_IDCARD` varchar(20) NOT NULL DEFAULT '' COMMENT '身份证号',
  `USER_FORM_INFO_FLAG_EDUCATION_BACKGROUND` varchar(50) NOT NULL DEFAULT '' COMMENT '教育背景',
  `USER_FORM_INFO_FLAG_INDUSTRY` varchar(50) NOT NULL DEFAULT '' COMMENT '行业',
  `USER_FORM_INFO_FLAG_INCOME` varchar(20) NOT NULL DEFAULT '' COMMENT '收入',
  `USER_FORM_INFO_FLAG_MOBILE` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `USER_FORM_INFO_FLAG_EMAIL` varchar(50) NOT NULL DEFAULT '' COMMENT '邮箱',
  `USER_FORM_INFO_FLAG_LOCATION` varchar(200) NOT NULL DEFAULT '' COMMENT '详细地址',
  `USER_FORM_INFO_FLAG_HABIT` varchar(200) NOT NULL DEFAULT '' COMMENT '兴趣爱好',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COMMENT='微信会员卡数据表';
        
SET FOREIGN_KEY_CHECKS = 1;";
            $db->execute($sql);
        }
        
        $event=new EventsController();
        $authorizer_access_token=$event->authorizer_access_token($appid);
        $logs['appid']=$appid;
        $logs['xinde']="++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n\r";
        writeOperationLog($logs,'wechatcard');
        if (false == $authorizer_access_token){
            $log['msg']=$authorizer_access_token;
            $log['code']='未获取$authorizer_access_token';
            writeOperationLog($log,'wechatcard');
            return false;
        }else{//抓取卡券列表
            $url=$this->batch_get_card.$authorizer_access_token;
            $result=curl_https($url,$post_data,array(),600,true);
            if (is_json($result)){
                $result_array=json_decode($result,true);
                if (0 != $result_array['errcode']){
                    $log['code']=$result_array['errcode'];
                    writeOperationLog($log,'wechatcard');
                    return false;
                }else{
                    if (null == $result_array['card_id_list']){
                        $log['code']='card_list数据为空，appid：'.$appid;
                        $log['msg']=$result_array;
                        
                        writeOperationLog($log,'wechatcard');
                        return false;
                    }else {
                        session('wechat:appid',$appid);
                        //查询卡券是否是会员卡类型
                        $card_id_list=$this->CheckCardType($appid,$result_array['card_id_list']);echo 111;
                        $wechat_batchger_card_list = $this->redis->get('wechat:batchger:card_list:'.$appid);
//                         $this->redis->del('wechat:batchger:card_list:'.$appid);//已经在入口出删除
                        if (null != $wechat_batchger_card_list){
                            $wechat_batchger_card_list=rtrim($wechat_batchger_card_list,'()()');
                            $wechat_batchger_card_list=explode('()()',$wechat_batchger_card_list);
                            $this->redis->set('wechat:batchger:card_list:'.$appid,json_encode($wechat_batchger_card_list));
//                             dump($this->redis->get('wechat:batchger:card_list:'.$appid));
//                             dump($appid);
                            //获取openid列表，并存入redis缓存
                            $re=$this->GetOpenidListForCard($table_pre, $appid);
                            if (0 < $this->redis->get('wechat:card:openidlist:number:'.$appid)){//将openid和cardid组合成json，并请求接口，返回code和card_id列表
                                
                                $return=$this->cardid_openid_json($appid);
                            
//                                 //拉取会员卡信息
                                $this->GetMemberCardUserinfo($appid,$table_pre);
                                $l['end']='完成一个'.$appid;
                                writeOperationLog($l,'wechatcard');
                            }
                        }else{echo 1;
                            return false;
                        }
                    }
                }
            }else{
                $log['code']='；批量获取卡券列表结果，不是json数据';
                writeOperationLog($log,'wechatcard');
                return false;
            }
        }
    }
    
    
    
    /**
     * 查询卡券类型
     * @param unknown $appid
     * @param unknown $cardlit
     * @param unknown $length 几张卡券
     * @param unknown $num    当前是卡券列表中的第几个
     * @param unknown $vip      会员卡列表
     */
    protected function CheckCardType($appid,$cardlit){
        $event=new EventsController();
        $authorizer_access_token=$event->authorizer_access_token($appid);
        if (false == $authorizer_access_token){
            $log['msg']=$authorizer_access_token;
            $log['code']='查询卡券类型未获取$authorizer_access_token';
            writeOperationLog($log,'wechatcard');
            return false;
        }else{//多线程抓取卡券列表
            require  './vendor/autoload.php';
            $multi_curl=new MultiCurl();
            $url=$this->get_card_type.$authorizer_access_token;
            foreach ($cardlit as $key => $val){//将数据插入多线程curl
                $pd=array('card_id'=>$val);
                $multi_curl->addPost($url, json_encode($pd));
            }
            $multi_curl->setTimeout(600);
            
            $multi_curl->success(function ($instance){
                $mappid=session('wechat:appid');
                if (0 !==$instance->response->errcode || !isset($instance->response->errcode)){
                    $log['code']='获取卡券类型接口,返回的参数errcode不是0或没有errcode参数';
                    $log['msg']=$instance->rawResponse;
                    writeOperationLog($log,'wechatcard');
                    return true;
                }else {
                    $array=json_decode($instance->rawResponse,true);
                    if ('MEMBER_CARD'==$array['card']['card_type']){
                        $this->redis->append('wechat:batchger:card_list:'.$mappid,$array['card']['member_card']['base_info']['id'].'()()');
                    }
                    //return true;
                }
            });
            $multi_curl->error(function($instance) {
                dump($instance);
            });
            $multi_curl->start();
            
            
            
            
            
            
            
            
            
            
            
            
        }
    }
    
    
    
    
    
    /**
     * 获取关注传入appid的openid列表信息，存入redis
     * @param string $table_pre
     * @param string $appid
     * @param string $next_openid
     * @return boolean
     */
    protected function GetOpenidListForCard(string $table_pre,string $appid,$next_openid=''){
    
        //此方法不能有任何删除redis key的代码，否则，会造成redis存储不完整
    
        $result=$this->get_wechat_user_openid($appid,$next_openid);
        if (false != $result){
            if (is_json($result)){
                $array=json_decode($result,true);
                if (!array_key_exists('errcode',$array)){
                    $openid_list_json=json_encode($array['data']['openid']);
                    $number=$this->redis->get('wechat:openid:number:'.$appid);//获取本次redis有序集合的序号
                    $number=null==$number ? 1 : $number;
                    $this->redis->zadd('wechat:card:openidlist:'.$appid,$number,$openid_list_json);
                    $this->redis->set('wechat:card:openidlist:number:'.$appid,$number);//设置保存了几个有序集合
                    
                    $localtotal=$this->redis->incrBy('wechat:card:openid:localtotal:'.$appid,$array['count']);//记录本地总数
                    if ($localtotal < $array['total']){//如果当前本地更新量小于全部
                        $this->GetOpenidListForCard($table_pre, $appid,$array['next_openid']);
                    }elseif ($localtotal >= $array['total']){//如果当前本地更新量等于或大于全部更新量，不会出现大于的情况，但防止意外情况，把大于的判断写上
                        //删除记录的本地获取openid总数
                        $this->redis->del('wechat:card:openid:localtotal:'.$appid);
                        return true;
                    }
                }else{
                    $log['code']='批量获取openid列表，返回错误码';
                    $log['msg']=$array;
                    writeOperationLog($log,'wechatcard');
                    return false;
                }
            }else{
                $log['code']='批量获取openid列表结果，不是json数据';
                $log['msg']=$result;
                writeOperationLog($log,'wechatcard');
                return false;
            }
        }else {
        $log['code']='批量获取openid列表，返回结果错误，是false';
        writeOperationLog($log,'wechatcard');
        return false;
        }
    }
    
    
    //把openid和cardid一一对应，组合json，用组合的json获取用已领去卡券接口
    protected function cardid_openid_json($appid){
        $number = $this->redis->get('wechat:card:openidlist:number:'.$appid);//读取保存了几个openid有序集合
        $card_list=$this->redis->get('wechat:batchger:card_list:'.$appid);//获取卡券列表
        $card_list=json_decode($card_list,true);
        $no=0;
        $cardlistarray=null;
        foreach ($card_list as $key => $val){//循环卡券列表
            for ($i=0;$i<=$number-1; $i++){//一共有多少个有序集合
                $user_json=$this->redis->zRange('wechat:card:openidlist:'.$appid,$i,$i);//返回的是一个数组
                $user_array=json_decode($user_json[0],true);
                foreach ($user_array as $k => $v){
                    $cardlistarray[]=json_encode(array('openid'=>$v,'card_id'=>$val));
                    if ($no>=499){//如果个数等于500个，应该还要加上当前for循环数量小于总数量时
                        $re=$this->GetCardList($appid,$cardlistarray);
                        $return[]=$return;
                        $log['number']=$no;
                        $log['msg']='拉取用户已领取卡券openid=xxxxx,card_id=xxxxxx';
                        $log['return']=$re;
                        writeOperationLog($log,'wechatcard');
                        $cardlistarray=null;
                        $no=0;
                    }else{
                        $no++;
                    }
                }
            }
        }
        //不管怎样，都返回true
        if ($return ){
            return true; 
        }else{
            return true;
        }
    }
    


    /**
     * 获取用户已领取卡券接口
     */
    protected function GetCardList($appid,$cardlistarray){
        session('wechat:appid',$appid);
        echo session('wechat:appid');
        $event=new EventsController();
        $authorizer_access_token=$event->authorizer_access_token($appid);
        if (false == $authorizer_access_token){
            return false;
        }else{
            require  './vendor/autoload.php';
            $multi_curl=new MultiCurl();
            $url=$this->get_card_list.$authorizer_access_token;
            foreach ($cardlistarray as $key => $val){//将数据插入多线程curl
                $multi_curl->addPost($url, $val);
            }
            $multi_curl->setTimeout(600);
            
            $multi_curl->success(function ($instance){
                $mappid=session('wechat:appid');
                $l['code']='最后一步拉取微信会员卡数据'.$mappid;
                $l['msg']=$instance->rawResponse;
                writeOperationLog($l,'wechatcard');
                if (0 !==$instance->response->errcode || !isset($instance->response->errcode)){
                    $log['code']='获取用户已领取卡券接口,返回的参数errcode不是0或没有errcode参数';
                    $log['msg']=$instance->rawResponse;
                    writeOperationLog($log,'wechatcard');
                    return true;
                }else {
                    $array=json_decode($instance->rawResponse,true);//dump($array);
//                     $a['card_list']=$array['card_list'];
//                     writeOperationLog($a,'carddata');
                    if (null != $array['card_list']){dump($array);
                        foreach ($array['card_list'] as $key => $val){
                            $this->redis->append('wechat:card_list:'.$mappid,json_encode($val).'<><>');
                        }
                    }
                    //session('wechat:appid',null);
                    return true;
                }
            });
            $multi_curl->error(function($instance) {
                dump($instance);
            });
            $multi_curl->start();
        }
    }
    
    
    
    
    
    /**
     * 批量拉取会员信息，调取多线程方法
     * @param string $appid
     */
    protected function GetMemberCardUserinfo(string $appid,$table_pre){
        $wechat_card_list=$this->redis->get('wechat:card_list:'.$appid);
        //$this->redis->del('wechat:card_list:'.$appid);
//         dump($wechat_card_list);return true;
        if (null != $wechat_card_list){
            $wechat_card_list=rtrim($wechat_card_list,'<><>');
            $wechat_card_array=explode('<><>',$wechat_card_list);
            $i=0;
            $wechat_card_array_multi=null;
            $count=count($wechat_card_array);
            foreach ($wechat_card_array as $key => $val){
                $wechat_card_array_multi[]=$val;
                if ($i>=499 || $key >= $count-1){
                    $this->MultiGetMemberCard($appid, $wechat_card_array_multi,$table_pre);
                    $wechat_card_array_multi=null;
                    $i=0;
                }else{
                    $i++;
                }
            }
        }else {
            return true;
        }
    }
    
    
    
    /**
     * 多线程拉取微信会员卡数据
     * @param string $appid
     * @param unknown $openid_list
     * @return boolean
     */
    protected function MultiGetMemberCard(string $appid,$openid_list,$table_pre){
        $event=new EventsController();
        $authorizer_access_token=$event->authorizer_access_token($appid);
        if (false == $authorizer_access_token){
            $log['appid']=$appid;
            $log['code']='拉取微信会员卡数据接口未获取$authorizer_access_token';
            $log['$authorizer_access_token']=$authorizer_access_token;
            writeOperationLog($log,'wechatcard');
            return false;
        }else{
            require  './vendor/autoload.php';
            $multi_curl=new MultiCurl();
            $url=$this->get_membercard.$authorizer_access_token;
            foreach ($openid_list as $key => $val){//将数据插入多线程curl
                $multi_curl->addPost($url, $val);
            }
            echo "+++++++++++++++++++++++++++++++\r\n";
            dump($openid_list);
            $multi_curl->setTimeout(600);
            session('table_pre',$table_pre);
            $multi_curl->success(function ($instance){
                if (0 != $instance->response->errcode || !isset($instance->response->errcode)){
                    $log['msg']=$instance->rawResponse;
                    $log['code']='拉取会员卡信息接口返回的参数errcode不是0或没有errcode参数';
                    writeOperationLog($log,'wechatcard');
                    return true;
                }else {
                    $array=json_decode($instance->rawResponse,true);
                    dump($array);
                    $data['openid']=$array['openid'];
                    $data['nickname']=$array['nickname'];
                    $data['sex']=$array['sex'];
                    foreach ($array['user_info']['common_field_list'] as $key => $val){
                        if ('USER_FORM_INFO_FLAG_NAME'==$val['name']){$data['USER_FORM_INFO_FLAG_NAME']=$val['value'];}
                        if ('USER_FORM_INFO_FLAG_IDCARD'==$val['name']){$data['USER_FORM_INFO_FLAG_IDCARD']=$val['value'];}
                        if ('USER_FORM_INFO_FLAG_EDUCATION_BACKGROUND'==$val['name']){$data['USER_FORM_INFO_FLAG_EDUCATION_BACKGROUND']=$val['value'];}
                        if ('USER_FORM_INFO_FLAG_INDUSTRY'==$val['name']){$data['USER_FORM_INFO_FLAG_INDUSTRY']=$val['value'];}
                        if ('USER_FORM_INFO_FLAG_INCOME'==$val['name']){$data['USER_FORM_INFO_FLAG_INCOME']=$val['value'];}
                        if ('USER_FORM_INFO_FLAG_MOBILE'==$val['name']){$data['USER_FORM_INFO_FLAG_MOBILE']=$val['value'];}
                        if ('USER_FORM_INFO_FLAG_EMAIL'==$val['name']){$data['USER_FORM_INFO_FLAG_EMAIL']=$val['value'];}
                        if ('USER_FORM_INFO_FLAG_LOCATION'==$val['name']){$data['USER_FORM_INFO_FLAG_LOCATION']=$val['value'];}
                        if ('USER_FORM_INFO_FLAG_HABIT'==$val['name']){$data['USER_FORM_INFO_FLAG_HABIT']=$val['value'];}
                    }
                    $db=M('wechat_card_info',session('table_pre'),'DB_CONFIG1');
                    $db->add($data);
                    echo $db->_sql();
                    return true;
                }
            });
            $multi_curl->error(function($instance) {
                //dump($instance);
            });
            $multi_curl->start();
        }
    }
    
    
    
    
    
}

?>