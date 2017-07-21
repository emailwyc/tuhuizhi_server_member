<?php
namespace Member\Controller;

use Common\Controller\ErrorcodeController;
class InsidememberController extends ErrorcodeController
{
    // TODO - Insert your code here
    
    
   /**
     * 全量获取会员信息，分页获取
     */
    public function getall(){
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
                    $sel=$db->where(array('ukey'=>$key_admin,'enable'=>1))->select();
                    if (1<count($sel) || 1 > count($sel)){
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
                        if ($versign==$sign){
                            $lines=null==$lines?5000:$lines;//默认每页5000
                            $lines=$lines >= 10000 ? 10000 : $lines;//如果大于10000，则最多10000条
                            $dbmem=M('mem',$sel[0]['pre_table'],'DB_CONFIG1');
                            $start=(int)($page-1) * (int)$lines;//计算每页条数
                            $count=$dbmem->count('id');
                            if ($page > $count/$lines){
                                $msg['code']=112;
                            }else{
                                $list=$dbmem->field('cardno,usermember,idnumber,mobile,openid')->where(array('openid'=>array('neq','')))->order('id asc')->limit($start,$lines)->select();
                                //echo $dbmem->_sql();
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
    
    
    
    public function get_key_admin() {
        $msg=$this->commonerrorcode;
        $starttime=mktime(23,30,0);
        $endtime=mktime(8,0,0);
        $now=time();
//         if ($now < $endtime || $now > $starttime){
        if (1==1){
        
            if (IS_POST){
                $db=M('admin','total_','DB_CONFIG1');
                //只查询已经授权过的
                $list=$db->field('ukey as key_admin,signkey as sign,total_admin.wechat_appid as appid,nick_name,authorization_info')->join(' `total_third_authorizer_info` on `total_admin`.`wechat_appid`=`total_third_authorizer_info`.`appid`')->where(array('enable'=>1))->order('`total_admin`.`id` asc')->select();
                $msg['code']=200;
                foreach ($list as $key => $val){
                    if (!strpos($val['authorization_info'],',2,')){
                        unset($list[$key]);
                    }
                    unset($list[$key]['authorization_info']);
                }
                $msg['data']=$list;
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