<?php
/**
 * 积分抽奖(C端)
 * User: wutong
 * Date: 11/6/17
 * Time: 15:20 AM
 */

namespace Integral\Controller;
use Think\Controller;
use Common\Controller\CommonController;
use common\ServiceLocator;
use MerAdmin\Model\ActivityTypeModel;
use MerAdmin\Model\ActivityModel;
use MerAdmin\Model\ActivityPropertyModel;
use MerAdmin\Model\ActivityPropertyNewModel;

class DrawController extends CommonController
{
    public $admin_arr;
    public $key_admin;

    public function _initialize(){
        parent::__initialize();
        $key_admin = I('key_admin');
        if(empty($key_admin))
        {
            echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
        else
        {
            $adminService = ServiceLocator::getAdminService();
            $admin_arr = $adminService->getByUkey($key_admin);
            
            if(empty($admin_arr))
            {
                echo returnjson(array('code'=>1001),$this->returnstyle,$this->callback);exit();
            }
            else
            {
                $this->admin_arr=$admin_arr;
                $this->key_admin=$key_admin;
            }
        }
    }

    /**
     * 查询活动下所有券
     * @param $key_admin $type_id
     * @return mixed
     * localhost/member/index.php/Integral/Draw/curl_api?buildid=860100010040500017&key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE
     */
    public function curl_api(){
        $type_id           = I('type_id');
        $params['buildid'] = I('buildid') ? I('buildid') : "";
        $params['openid']  = $this->userucid;
        $params['vip_area']  = I('vip_area') ? I('vip_area') : "";

        $userCardService = ServiceLocator::getUserCardService();
        $user_arr = $userCardService->getUserCardByOpenId($this->admin_arr['pre_table'], $params['openid']);
        
        $code_arr = $userCardService->getMemberCode($params['openid'], $this->admin_arr['pre_table'], $this->admin_arr['id']);
        
        $activityDrawService = ServiceLocator::getActivityDrawService();
        $integral_arr = $activityDrawService->getOnce($this->admin_arr['id'], $params['buildid']);
        
        if(empty($integral_arr))
        {
            $msg = array('code'=>307,'data'=>'暂无活动，敬请期待...');
        }
        else
        {
            //返回数据
            $msg = array('code'=>200, 'data'=> $integral_arr);
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 查询用户积分
     * @param $key_admin $openid
     * @return mixed
     * localhost/member/index.php/Integral/Draw/integral_admin?buildid=860100010040500017&key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE
     */
    public function integral_admin(){
        //判断openid
        $openid = $this->userucid;
        if(empty($openid))
        {
            $msg=array('code'=>1030);
        }
        else
        {
            //判断用户是否登录
            $userCardService = ServiceLocator::getUserCardService();
            $user_arr = $userCardService->getUserCardByOpenId($this->admin_arr['pre_table'],$openid);
            if($user_arr == 2000)
            {
                echo returnjson(array('code'=>2000),$this->returnstyle,$this->callback);exit();
            }
            else
            {
                //返回会员信息
                $arr = $userCardService->getUserCardInfo($this->key_admin, $user_arr['cardno'], $this->admin_arr['signkey']);
                
                if($arr['code']!=200)
                {
                    $msg=array('code'=>3000,'data'=>'没有用户信息');
                }
                else
                {
                    $msg=array('code'=>200,'data'=>$arr['data']);
                }
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 抽奖兑换
     * @param $key_admin $pid $main $openid
     * @return mixed
     * localhost/member/index.php/Integral/Draw/integral_draw?buildid=860100010040500017&key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE&main=main&activity=19264
     */
    public function integral_draw(){
        $main   = I('main');
        $openid = $this->userucid;
        $activity_id = I('activity');
        if(empty($main) || empty($openid))
        {
            $msg=array('code'=>1030);
        }
        else
        {
            $params['buildid'] = I('buildid') ? I('buildid') : "";
            $status            = I('status') ? I('status') : 'ZHT_YX';
            
            $userCardService = ServiceLocator::getUserCardService();
            $user_arr = $userCardService->getUserCardByOpenId($this->admin_arr['pre_table'],$openid);
            
            //判断用户是否存在
            if($user_arr == 2000)
            {
                echo returnjson(array('code'=>2000),$this->returnstyle,$this->callback);exit();
            }
            
            //判断奖品数据是否存在
            $activityDrawService = ServiceLocator::getActivityDrawService();
            $once_arr = $activityDrawService->getByActity($activity_id);
            
            if(empty($once_arr))
            {
                echo returnjson(array('code'=>307),$this->returnstyle,$this->callback);exit();
            }

            //获取指定卡级别所需积分
            if($once_arr['discount'] == ActivityPropertyModel::DISCOUNT_2){
                if($user_arr['level'])
                {
                    $code_arr = $userCardService->getMemberCode($openid, $this->admin_arr['pre_table'], $this->admin_arr['id']);
                    if($code_arr)
                    {
                        $once_arr['integral'] = json_decode($once_arr['integral'],true);
                        if($once_arr['integral'][$code_arr['name']] == '')
                        {
                            $msg['code']=1503;
                            echo returnjson($msg,$this->returnstyle,$this->callback);die;
                        }
                        
                        $once_arr['integral'] = $once_arr['integral'][$code_arr['name']];
                    }
                    else
                    {
                        $msg['code']=1503;
                        echo returnjson($msg,$this->returnstyle,$this->callback);die;
                    }
                }
                else
                {
                    $msg['code']=1503;
                    echo returnjson($msg,$this->returnstyle,$this->callback);die;
                }
            }
print_r($once_arr);exit;

            //判断用户积分是否充足
            $arr = $userCardService->getUserCardInfo($this->key_admin, $user_arr['cardno'], $this->admin_arr['signkey']);
            
            if($arr['data']['score'] < (int)$once_arr['integral'])
            {
                echo returnjson(array('code'=>319),$this->returnstyle,$this->callback);die;
            }
            
            $activity_arr['activity'] = $activity_id;
            
            //领券
            $act_res = $userCardService->prize_integral($activity_arr['activity'], $pid, $openid);
            
            //领取成功
            if(!empty($act_res) && $act_res['code'] == 0)
            {
                //扣除用户积分
                if($once_arr['integral'] == 0)
                {
                    $res['code'] = 200;
                }
                else
                {
                    $res = $userCardService->del_integral($this->key_admin,$this->admin_arr['signkey'],$user_arr['cardno'],$once_arr['integral'],$main);
                }
                
                //扣除积分失败,还券
                if($res['code'] != 200 || ($res['code'] >= 6000 && $res['code'] < 7000))
                {
                    //还券
                    $userCardService->coupon_return($activity_arr['activity'], $pid, $openid, $act_res['qr']);
                    $userCardService->newlog_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,4,$this->admin_arr['pre_table'],$openid,$pid,$act_res['qr'], $params['buildid']);
                    
                    $msg = array('code'=> $res['code']);
                    echo returnjson($msg,$this->returnstyle,$this->callback);die;
                }
                else
                {
                    //更新数量
                    $activityPropertyNewService->updateIssue($once_arr['pid']);
            
                    //领券成功写入日志
                    $userCardService->newlog_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,1,$this->admin_arr['pre_table'],$openid,$pid,$act_res['qr'], $params['buildid']);
                    $msg = array('code'=>200,'data'=>'领奖成功');
                    echo returnjson($msg,$this->returnstyle,$this->callback);die;
                }
            }
            else
            {
                $return_log_id = $userCardService->log_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,2,$this->admin_arr['pre_table'],$openid,$pid,'', $params['buildid']);
                $msg = array('code'=>$act_res['code'],'msg'=>$act_res['message'] ? $act_res['message'] : '领取失败');
                echo returnjson($msg,$this->returnstyle,$this->callback);die;
            }

        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);die;
    }

    /**
     * 判断活动id状态
     * @param $key_admin $activity_id
     * @return mixed
     */
    public function activity_id_status(){
        $params['activity_id'] = I('activity_id');
        if(in_array('',$params))
        {
            $msg['code'] = 1030;
        }
        else
        {
            $url = "http://182.92.31.114/rest/act/status/".$params['activity_id'];//活动下所有券
            $arr = json_decode(http($url),true);//处理返回结果
            $msg['code'] = 200;
            $msg['data'] = $arr;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 获取建筑物ID
     */
    public function builid_list(){
        $where['adminid'] = array('eq',$this->admin_arr['id']);
        $where['is_del']  = array('eq',2);
        $where['_logic']  = 'and';
        $db = M('buildid','total_');
        $arr=$db->where($where)->select();
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
}
