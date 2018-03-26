<?php
namespace Thirdwechat\Controller\Wechat;

use Thirdwechat\Controller\Wechat\Member\MemberioController;
use Curl\MultiCurl;
use Thirdwechat\Controller\Thirdwechat\EventsController;
class GetwechatuserinfoController extends MemberioController
{
    /**
     * 全量获取微信会员信息
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
                $selstarttime = I('post.starttimestamp');
                $selendtime = I('post.endtimestamp');
                if (null == $key_admin || null == $sign || null == $page || 1>$page){
                    $msg['code']=100;
                }else{

//                    $db=M('admin','total_','DB_CONFIG4');
                    $adminInfo = $this->getMerchant($key_admin);
//                    $sel=$db->where(array('ukey'=>$key_admin))->select();
//                    if (1<count($sel)){
//                        $msg['code']=3000;
//                    }else{
                        //验证签名是否成功
                        $params['page']=$page;
                        if (isset($_POST['lines'])){
                            $params['lines']=$lines;
                        }
                        $params['key_admin']=$key_admin;
                        $params['sign_key']=$adminInfo['signkey'];
                        $where = '';
                        if ($selstarttime){
                            $params['starttimestamp'] = $selstarttime;
                            $where = '`subscribe_time` >=' . $selstarttime;
                        }
                        if ($selendtime){
                            $params['endtimestamp'] = $selendtime;
                            if ($where){
                                $where = $where . ' and `subscribe_time` <=' . $selendtime;
                            }else{
                                $where = '`subscribe_time` <=' . $selendtime;
                            }
                        }


                        $versign=sign($params);
//                        echo $versign;
                        if ($versign==$sign){
                            $lines=null==$lines?5000:$lines;//默认每页5000
                            $lines=$lines >= 10000 ? 10000 : $lines;//如果大于10000，则最多10000条
                            $dbwechat=M('wechat_openid',$adminInfo['pre_table'], 'DB_CONFIG5');
                            $start=(int)($page-1) * (int)$lines;//计算每页条数
                            if ($where){
                                $where = $where;
                            }else{
                                $where = '1=1';
                            }
                            $count=$dbwechat->where($where)->count();
                            if ($page > ceil($count/$lines)){
                                $msg['code']=112;
                            }else{
                                $list=$dbwechat->where($where)->order('openid')->limit($start,$lines)->select();
                                $msg['code']=200;
                                $l=array('total'=>$count,'page'=>$page,'data'=>$list,'wechat_appid'=>$adminInfo['wechat_appid']);
                                $msg['data']=$l;
                            }
                        }else{
                            $msg['code']=1002;
                        }
//                    }
                }
            }else{
                $msg['code']=111;
            }
        }else{
            $msg['code']=1013;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


    /**
     * 已废弃
     * 全量获取微信会员信息
     */
    public function getalldata(){

        echo 'start:'.date('Y-m-d H:i:s')."\r\n";
        $ip=$_SERVER["SSH_CONNECTION"];
        $serverip=$_SERVER['SERVER_ADDR'];
//         if (strpos($ip,'123.56.138.28') || '123.56.138.28'==$serverip){
        $db=M('third_authorizer_info','total_');
        $wechatlist=$db->join(' `total_admin` on `total_admin`.`wechat_appid` = `total_third_authorizer_info`.`appid`')->field('authorization_info,appid,pre_table')->select();
        $wechat=null;
        foreach ($wechatlist as $key => $val){
            $authorization=explode(',', $val['authorization_info']);
            if (in_array('2', $authorization)){
                $wechat[]=$val;
            }
//                 if (strpos($val['authorization_info'],',2,')){
//                     $wechat[]=$val;
//                 }
        }
        $dbdmp=M('date','dmp_');
        foreach ($wechat as $k => $v){
            //防止意外情况，先删除redis key
            $this->redis->del('wechat:openid:number:'.$v['appid']);//删除计数的redis key，按100个计算
            $this->redis->del('wechat:openid:localtotal:'.$v['appid']);//删除本地一共获取了多少个openid
            $this->redis->del('wechat:openidlist:'.$v['appid']);//删除有序集合key
            $result=$this->GetOpenidList($v['pre_table'], $v['appid']);
            $admin=$dbdmp->where(array('admin_appid'=>$v['appid']))->find();
            $data['date']=date('Y-m-d H:i:s');
            if (null == $admin){
                $data['admin_appid']=$v['appid'];
                $dbdmp->add($data);
            }else{
                $dbdmp->where(array('admin_appid'=>$v['appid']))->save($data);
            }
            sleep(3);//试一下sleep可不可以等待服务器释放部分内存
        }
//         }else{
//             echo 'error'."\r\n";
//         }
    }





    /**
     * 全量获取微信会员信息
     */
    public function GetAllUserInfo(){
        echo 'start:'.date('Y-m-d H:i:s')."<br>\r\n";
        $ip=$_SERVER["SSH_CONNECTION"];
        $serverip=$_SERVER['SERVER_ADDR'];
        //查询微信授权表
        $db=M('third_authorizer_info','total_');
        $wechatlist=$db->join(' `total_admin` on `total_admin`.`wechat_appid` = `total_third_authorizer_info`.`appid`')->field('authorization_info,appid,pre_table,nick_name')->order('sortid  asc')->select();
        $wechat=null;
        //查询符合条件的appid
        foreach ($wechatlist as $key => $val){
            $authorization=explode(',', $val['authorization_info']);
            if (in_array('2', $authorization)){
                $wechat[]=$val;
            }
        }
        $dbdmp=M('date','dmp_');
        $dbisget=M('appid_isget','dmp_');
        foreach ($wechat as $k => $v){
            echo 'load--'.$v['appid'].':'.date('Y-m-d H:i:s')."<br>\r\n";
            //dump($v);
            //查询这个appid今天是否拉取过数据
            $find=$dbisget->where(array('appid'=>$v['appid'], 'datestr'=>array('gt',strtotime(date('Y-m-d')))))->find();
            //根据条件查询结果,如果拉取的时间在今天凌晨之后,则证明今天已经拉取过
            if (null == $find){
                echo 'begin--'.$v['appid'].':'.date('Y-m-d H:i:s')."<br>\r\n\r\n\r\n";
                //先更新一下数据库,以免定时任务两分钟后再去执行这个appid的拉取计划
                $data['nick_name']=$v['nick_name'];
                $data['datestr']=strtotime(date('Y-m-d H:i:s'));
                $data['datetime']=date('Y-m-d H:i:s');
                if (null == $dbisget->where(array('appid'=>$v['appid']))->find()){
                    $data['appid']=$v['appid'];
                    $dbisget->add($data);
                }else{
                    $dbisget->where(array('appid'=>$v['appid']))->save($data);
                }

                //防止意外情况，先删除redis key
                $this->redis->del('wechat:openid:number:'.$v['appid']);//删除计数的redis key，按100个计算
                $this->redis->del('wechat:openid:localtotal:'.$v['appid']);//删除本地一共获取了多少个openid
                $this->redis->del('wechat:openidlist:'.$v['appid']);//删除有序集合key
                $result=$this->GetOpenidList($v['pre_table'], $v['appid']);
                $admin=$dbdmp->where(array('admin_appid'=>$v['appid']))->find();
                $data['date']=date('Y-m-d H:i:s');
                if (null == $admin){
                    $data['admin_appid']=$v['appid'];
                    $dbdmp->add($data);
                }else{
                    $dbdmp->where(array('admin_appid'=>$v['appid']))->save($data);
                }
                //exit();
                break;
            }else{
                echo $v['appid'].'  was get.'.'--stop:'.date('Y-m-d H:i:s')."<br>\r\n\r\n\r\n";
                continue;
            }
        }
    }


    public function aaa($in){
        echo '完成'."\r\n";
    }


    /**
     * 返回openid是否已经关注公众号
     */
    public function checkOpenidFollowed()
    {
        $params['key_admin']=I('key_admin');
        $params['openid']=I('openid');
        $params['timestamp']=(int)I('timestamp');
        $params['sign']=I('sign');

        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }

        $ct=time()-$params['timestamp'];
        //如果计算得出来的秒数大于正负60秒，则判定请求方的服务器时间不对
        if ($ct > 60 || $ct < -60){
            returnjson(array('code'=>1056), $this->returnstyle, $this->callback);
        }


        //验证传入的参数个数是否符合
        $pa=I('param.');
        if ( count($pa) != count($params) ) {
            returnjson(array('code'=>1051), $this->returnstyle, $this->callback);
        }

        $admininfo=$this->getMerchant($params['key_admin']);

        $params['sign_key']=$admininfo['signkey'];

        //验证签名
        unset($pa['sign']);
        $pa['sign_key']=$admininfo['signkey'];
        $sign=sign($pa);
//         echo $sign;
        if ($sign != $params['sign']){
            returnjson(array('code'=>1002), $this->returnstyle, $this->callback);
        }

        //查询用户信息，判断是否关注过，调用的是批量接口，不再写单独的接口
        $array=array(
            'user_list'=>array(
                array(
                    'openid'=>'oWm-rt2OE-JtS9JSxlldzjpV1V7M',
                    'lang'=>'zh-CN'
                ),
            ),
        );

        $return=$this->get_wechat_user_one($admininfo['wechat_appid'], $params['openid']);

        //返回不是json
        if (!is_json($return)){
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }

        $userinfo=json_decode($return, true);
        //解析json失败
        if (false == $userinfo){
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }

        if (1 == $userinfo['subscribe']){//已关注
            returnjson(array('code'=>4006, 'data'=>$userinfo), $this->returnstyle, $this->callback);
        }else {
            returnjson(array('code'=>4007), $this->returnstyle, $this->callback);
        }
    }








}

?>