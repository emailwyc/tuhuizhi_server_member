<?php
namespace Thirdwechat\Controller\Wechat;

use Thirdwechat\Controller\Wechat\Member\MembercardController;
class GetwechatusercardController extends MembercardController
{
    // TODO - Insert your code here
    
    
    
    /**
     * 每晚定时cli请求
     */
    public function getallwechatcard(){
        echo 'start:'.date('Y-m-d H:i:s')."\r\n";
        $ip=$_SERVER["SSH_CONNECTION"];
        $serverip=$_SERVER['SERVER_ADDR'];
        $db=M('third_authorizer_info','total_','DB_CONFIG1');
        $wechatlist=$db->join(' `total_admin` on `total_admin`.`wechat_appid` = `total_third_authorizer_info`.`appid`')->field('authorization_info,appid,pre_table')->select();
        $wechat=null;
        foreach ($wechatlist as $key => $val){
            $authorization=explode(',', $val['authorization_info']);
            if (in_array('8', $authorization)){
                $wechat[]=$val;
            }
//             if (strpos($val['authorization_info'],',8,')){
//                 $wechat[]=$val;
//             }
        }
        //dump($wechat);
        $dbdmp=M('date','dmp_','DB_CONFIG1');
        foreach ($wechat as $k => $v){
            //防止意外情况，先删除redis key
            $this->redis->del('wechat:card:openidlist:number:'.$v['appid']);//删除计数的redis key，按100个计算
            $this->redis->del('wechat:card:openid:localtotal:'.$v['appid']);//删除本地一共获取了多少个openid
            $this->redis->del('wechat:card:openidlist:'.$v['appid']);//删除有序集合key
            $this->redis->del('wechat:card_list:'.$v['appid']);//cardid 和codejson
            $this->redis->del('wechat:batchger:card_list:'.$v['appid']);//卡券列表
            
            $result=$this->BatchGetCard($v['pre_table'], $v['appid']);
            
            $this->redis->del('wechat:card:openidlist:number:'.$v['appid']);//删除计数的redis key，按100个计算
            $this->redis->del('wechat:card:openid:localtotal:'.$v['appid']);//删除本地一共获取了多少个openid
            $this->redis->del('wechat:card:openidlist:'.$v['appid']);//删除有序集合key
            $this->redis->del('wechat:card_list:'.$v['appid']);//cardid 和codejson
            $this->redis->del('wechat:batchger:card_list:'.$v['appid']);//卡券列表
            echo 'endtime:'.date('Y-m-d H:i:s')."\r\n";
        }
        
        
        
    }
    
    
    
    
    
    
    /**
     * 全量获取微信会员卡信息
     */
    public function getall(){
        writeOperationLog($_POST,'wexinall');
        $msg=$this->commonerrorcode;
        $starttime=mktime(23,30,0);
        $endtime=mktime(8,0,0);
        $now=time();
        //         if ($now < $endtime || $now > $starttime){
        if (1==1){
    
            if (IS_POST){
                $key_admin=I('post.key_admin');
                $sign=I('post.sign');
                $page=I('post.page');
                $lines=I('post.lines');
                if (null == $key_admin || null == $sign || null == $page || 1>$page){
                    $msg['code']=100;
                }else{
    
                    $db=M('admin','total_','DB_CONFIG1');
                    $sel=$db->where(array('ukey'=>$key_admin))->select();
                    if (1<count($sel)){
                        $msg['code']=3000;
                    }else{
                        //验证签名是否成功
                        $params['page']=$page;
                        if (isset($_POST['lines'])){
                            $params['lines']=$lines;
                        }
                        $params['key_admin']=$key_admin;
                        $params['sign_key']=$sel[0]['signkey'];
                        $versign=sign($params);
                        //echo $versign;
                        if ($versign==$sign){
                            $lines=null==$lines?5000:$lines;//默认每页5000
                            $lines=$lines >= 10000 ? 10000 : $lines;//如果大于10000，则最多10000条
                            $dbwechat=M('wechat_card_info',$sel[0]['pre_table'],'DB_CONFIG1');
                            $start=(int)($page-1) * (int)$lines;//计算每页条数
                            $count=$dbwechat->count();
                            if ($page > ceil($count/$lines)){
                                $msg['code']=112;
                            }else{
                                $list=$dbwechat->order('openid')->limit($start,$lines)->select();
                                $msg['code']=200;
                                $l=array('total'=>$count,'page'=>$page,'data'=>$list);
                                $msg['data']=$l;
                            }
                        }else{
                            $msg['code']=1002;
                        }
                    }
                }
            }else{
                $msg['code']=111;
            }
        }else{
            $msg['code']=1013;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
}

?>