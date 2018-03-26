<?php
/**
 * 新版积分商城(C端)
 * User: wutong
 * Date: 9/13/17
 * Time: 10:29 AM
 */

namespace Integral\Controller;
use Think\Controller;
use PublicApi\Service\CouponService;
use Common\Controller\CommonController;
use common\ServiceLocator;
use MerAdmin\Model\ActivityTypeModel;
use MerAdmin\Model\ActivityModel;
use MerAdmin\Model\ActivityPropertyModel;
use MerAdmin\Model\ActivityPropertyNewModel;

class NewIntegralController extends CommonController
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
     * 获取颜色接口
     * localhost/member/index.php/Integral/NewIntegral/get_integral_color?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function get_integral_color(){
        $find=$this->GetOneAmindefault($this->admin_arr['pre_table'], $this->key_admin, 'integralcolorset');
        if($find){
            $msg['code']=200;
            $msg['data']=$find['function_name'];
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 查询活动下所有券
     * @param $key_admin $type_id
     * @return mixed
     * localhost/member/index.php/Integral/NewIntegral/curl_api?buildid=860100010040500017&key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE
     */
    public function curl_api(){
        $type_id           = I('type_id');
        $params['buildid'] = I('buildid') ? I('buildid') : "";
        $params['openid']  = $this->userucid;
        $params['vip_area']  = I('vip_area') ? I('vip_area') : "";
        $unionid = I('unionid');
        
        $userCardService = ServiceLocator::getUserCardService();
        $user_arr = $userCardService->getUserCardByOpenId($this->admin_arr,$this->key_admin,$this->admin_arr['pre_table'], $params['openid'], $unionid);
        
        $code_arr = $userCardService->getMemberCode($this->admin_arr,$this->key_admin,$params['openid'], $this->admin_arr['pre_table'], $this->admin_arr['id'],$unionid);
        
        $activityService = ServiceLocator::getActivityService();
        $integral_arr = $activityService->getAll($this->admin_arr['id'], '', '', $params['buildid'], ActivityModel::SYSTEM_2, 0, ActivityModel::IS_SHOWTIME);
        
        if(empty($integral_arr))
        {
            $msg = array('code'=>307,'data'=>'暂无活动，敬请期待...');
        }
        else
        {
            $activityPropertyNewService = ServiceLocator::getActivityPropertyNewService();
            $activityTypeService = ServiceLocator::getActivityTypeService();
            $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->key_admin,'coupon_default');
            
            $api_arr = array();//最终返回数据
            foreach ($integral_arr as $k => $v)
            {
               $arr = $activityPropertyNewService->getAll($v['activity'], '', $params['vip_area'], $params['buildid']);
               
               if(!empty($arr))
               {
                   foreach ($arr as $k2 => $v2)
                   {
                       if($v2['is_status'] == ActivityPropertyModel::STATUS_2)//上线状态
                       {
                           if(empty($type_id) || (!empty($type_id) && $type_id == $v2['type_id']))//类别筛选
                           {
                               $typeinfo = $activityTypeService->getById($v2['type_id'], ActivityTypeModel::SYSTEM_0);
                               
                               if($typeinfo['disable'] == 0)
                               {
                                   if($coupon_data['function_name'] == 2){
                                       $coupon_return = CouponService::getDetailById($v2['coupon_id'],$v2['pid']);
                                       if($coupon_return['code'] != 200){
                                           unset($arr[$k2]);continue;
                                       }else{
                                           $once_arr['issue'] = $coupon_return['data']['getNum'];
                                       }
                                   }else{
                                       $url="http://182.92.31.114/rest/act/level/".$v2['pid'];
                                       $once_arr=json_decode(http($url,array()),true);
                                   }                   
                                   $v2['issue'] = $once_arr['issue'];
                                    
                                   if($v2['discount'] == ActivityPropertyModel::DISCOUNT_2)
                                   {
                                       $v2['integral'] = json_decode($v2['integral'],true);
                                       if($code_arr)
                                       {
                                           if($v2['integral'][$code_arr['name']])
                                           {
                                               $v2['myintegral'] = $v2['integral'][$code_arr['name']];
                                           }
                                           else
                                           {
                                               $v2['myintegral'] = 0;
                                           }
                                       }
                                       else
                                       {
                                           $v2['myintegral'] = $activityService->integralSort($v2['integral']);
                                       }
                                   }
                                   else
                                   {
                                       $v2['myintegral'] = $v2['integral'];//当前卡级别的所需积分
                                   }
                                    
                                    
                                   $api_arr[] = $v2;
                               }
                           }
                       }
                   }
               }
            }
            
            //返回数据
            $msg = array('code'=>200, 'data'=> $api_arr);
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 查询所有分类
     * @param $key_admin
     * @return mixed
     */
    public function type_api(){
        $activityTypeService = ServiceLocator::getActivityTypeService();
        $integral_type_arr = $activityTypeService->getAll($this->admin_arr['id'], ActivityTypeModel::SYSTEM_0, 0);
        
        if(!is_array($integral_type_arr))
        {
            $msg=array('code'=>102,'msg'=>'暂无数据');
        }
        else
        {
            $msg=array('code'=>200,'data'=>$integral_type_arr);
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 查询某个奖品的详细信息
     * @param $key_admin $pid
     * @return mixed
     * localhost/member/index.php/Integral/NewIntegral/curl_api_once?buildid=860100010040500017&key_admin=202cb962ac59075b964b07152d234b70&pid=169301
     */
    public function curl_api_once(){
        $act_id = I('pid');//券ID
        $openid = $this->userucid;
        $buildid = I('buildid');
        $unionid = I('unionid');
        
        if(empty($act_id))
        {
            $msg=array('code'=>1030);
        }
        else
        {
            $userCardService = ServiceLocator::getUserCardService();
            $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->key_admin,'coupon_default');
            
            if($openid)
            {
                $user_arr = $userCardService->getUserCardByOpenId($this->admin_arr,$this->key_admin,$this->admin_arr['pre_table'], $openid, $unionid);
            }

            $code_arr = $userCardService->getMemberCode($this->admin_arr,$this->key_admin,$openid, $this->admin_arr['pre_table'], $this->admin_arr['id'],$unionid);
            
            $activityPropertyNewService = ServiceLocator::getActivityPropertyNewService();
            $api_arr = $activityPropertyNewService->getOnce($act_id, $this->admin_arr['id'], $buildid);
            
            if($api_arr['discount'] == ActivityPropertyModel::DISCOUNT_2)
            {
                $api_arr['integral'] = json_decode($api_arr['integral'],true);
            
                if($code_arr)
                {
                    if($api_arr['integral'][$code_arr['name']])
                    {
                        $api_arr['myintegral'] = $api_arr['integral'][$code_arr['name']];
                    }
                    else
                    {
                        $api_arr['myintegral'] = 0;
                    }
                }
                else
                {
                    $activityService = ServiceLocator::getActivityService();
                    $api_arr['myintegral'] = $activityService->integralSort($api_arr['integral']);
                }
            }
            else
            {
                $api_arr['myintegral'] = $api_arr['integral'];//当前卡级别的所需积分
            }
            
            if($coupon_data['function_name'] == 2){
                $coupon_return = CouponService::getDetailById($api_arr['coupon_id'],$act_id);
                if($coupon_return['code'] != 200){
                    echo returnjson(array('code'=>$coupon_return['code'],'msg'=>$coupon_return['msg']),$this->returnstyle,$this->callback);exit();
                }
                $once_arr['issue'] = $coupon_return['data']['getNum'];
                $once_arr['shop_id'] = $coupon_return['data']['couponApplyShopList'][0];
            }else{
                $url="http://182.92.31.114/rest/act/level/".$act_id;
                $once_arr=json_decode(http($url,array()),true);
                $once_arr['shop_id'] = '';
            }
            $api_arr['issue'] = $once_arr['issue'];
            $api_arr['shop_id'] = $once_arr['shop_id'];
            
            $msg = array('code'=>200, 'data'=> $api_arr);
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 查询用户积分
     * @param $key_admin $openid
     * @return mixed
     * localhost/member/index.php/Integral/NewIntegral/integral_admin?buildid=860100010040500017&key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE
     */
    public function integral_admin(){
        //判断openid
        $openid = $this->userucid;
        $unionid = I('unionid');
        if(empty($openid))
        {
            $msg=array('code'=>1030);
        }
        else
        {
            //判断用户是否登录
            $userCardService = ServiceLocator::getUserCardService();
            $user_arr = $userCardService->getUserCardByOpenId($this->admin_arr,$this->key_admin,$this->admin_arr['pre_table'],$openid, $unionid);
            if($user_arr == 2000)
            {
                echo returnjson(array('code'=>2000),$this->returnstyle,$this->callback);exit();
            }
            else
            {
                //返回会员信息
                $arr = $userCardService->getUserCardInfo($this->key_admin, $user_arr['cardno'], $this->admin_arr['signkey'],$unionid);
                
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
     * 兑换
     * @param $key_admin $pid $main $openid
     * @return mixed
     * localhost/member/index.php/Integral/NewIntegral/integral_delete?buildid=860100010040500017&key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE&pid=174499&main=main&activity=19165
     */
    public function integral_delete(){
        $pid    = I('pid');
        $main   = I('main');
        $openid = $this->userucid;
        $activity_id = I('activity');
        $unionid = I('unionid');
        $shop_id = I('shop_id');
        
        if(empty($pid) || empty($main) || empty($openid) || empty($activity_id))
        {
            $msg=array('code'=>1030);
        }
        else
        {
            $couponID = I('couponID');//券批ID  4.0 必传
            $params['buildid'] = I('buildid') ? I('buildid') : "";
            $status            = I('status') ? I('status') : 'ZHT_YX';
            
            $userCardService = ServiceLocator::getUserCardService();
            $user_arr = $userCardService->getUserCardByOpenId($this->admin_arr,$this->key_admin,$this->admin_arr['pre_table'],$openid,$unionid);
            
            $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'coupon_default');
            //$coupon_data['function_name'] == 2;//这里是默认4.0  上线之后删除
            
            //判断用户是否存在
            if($user_arr == 2000)
            {
                echo returnjson(array('code'=>2000),$this->returnstyle,$this->callback);exit();
            }
            
            //判断奖品数据是否存在
            $activityPropertyNewService = ServiceLocator::getActivityPropertyNewService();
            $once_arr = $activityPropertyNewService->getOnce($pid, $this->admin_arr['id'], $params['buildid']);
            
            if(empty($once_arr))
            {
                echo returnjson(array('code'=>307),$this->returnstyle,$this->callback);exit();
            }

            //获取指定卡级别所需积分
            if($once_arr['discount'] == ActivityPropertyModel::DISCOUNT_2){
                if($user_arr['level'])
                {
                    $code_arr = $userCardService->getMemberCode($this->admin_arr,$this->key_admin,$openid, $this->admin_arr['pre_table'], $this->admin_arr['id'],$unionid);
//                     print_r($code_arr);die;
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

            if($once_arr['vip_area'] == ActivityPropertyNewModel::VIP_2)
            {
                if($code_arr['name'] != '黑钻卡')
                {
                    $msg['code'] = 15;
                    $msg['msg']  = "卡级别不足!";
                    echo returnjson($msg,$this->returnstyle,$this->callback);die;
                }
            }
            
            //判断是否开启线下兑换
            $default_db  = M('default',$this->admin_arr['pre_table']);
            $default_arr = $default_db->where(array('customer_name'=>array('eq','integral_status')))->find();
            if($default_arr['function_name'] == 1)
            {
                $par['default_return_description'] = "请到".$default_arr['description']."兑换";
                $msg['code'] = 1033;
                $msg['data'] = "请到".$default_arr['description']."兑换";
                echo returnjson($msg, $this->returnstyle,$this->callback);die;
            }
            
            //判断用户积分是否充足
            $arr = $userCardService->getUserCardInfo($this->key_admin, $user_arr['cardno'], $this->admin_arr['signkey'],$unionid);
            
            if($arr['data']['score']<(int)$once_arr['integral'])
            {
                echo returnjson(array('code'=>319),$this->returnstyle,$this->callback);die;
            }
            
            $activity_arr['activity'] = $activity_id;
            
            if($status == 'ZHT_YX')
            {
                //领券
                $act_res = $userCardService->prize_integral($activity_arr['activity'], $pid, $openid,$coupon_data['function_name'],$couponID);
                
                //领取成功
                if(!empty($act_res) && $act_res['code'] == 0)
                {
                    $main = str_replace('&','',htmlspecialchars($once_arr['main']));//过滤特殊符号
                    
                    //扣除用户积分
                    if($once_arr['integral'] == 0)
                    {
                        $res['code'] = 200;
                    }
                    else
                    {
                        $res = $userCardService->del_integral($this->key_admin,$this->admin_arr['signkey'],$user_arr['cardno'],$once_arr['integral'],$main,$unionid);
                    }
                    
                    if($res['code'] >= 6000 && $res['code'] < 7000)
                    {
                        $userCardService->coupon_return($activity_arr['activity'], $pid, $openid, $act_res['qr'],$coupon_data['function_name'],$couponID,$shop_id);
                        $userCardService->newlog_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,4,$this->admin_arr['pre_table'],$openid,$pid,$act_res['qr'], $params['buildid']);
                        returnjson($res,$this->returnstyle,$this->callback);
                    }
                    
                    //扣除积分失败,还券
                    if($res['code'] != 200)
                    {
                        //还券
                        $userCardService->coupon_return($activity_arr['activity'], $pid, $openid, $act_res['qr'],$coupon_data['function_name'],$couponID,$shop_id);
                        $userCardService->newlog_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,4,$this->admin_arr['pre_table'],$openid,$pid,$act_res['qr'], $params['buildid']);
                        
                        $msg = array('code'=> $res['code'],'msg'=>$res['msg']);
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
                    $msg = array('code'=>$act_res['code'],'msg'=>$act_res['message'] ? $act_res['message'] : '领取失败');
                    echo returnjson($msg,$this->returnstyle,$this->callback);die;
                }
            }
            elseif($status=='ERP_YX')
            {
                $url=C('DOMAIN') . '/ErpService/Erpoutput/prize_exchange';
                $erp_params['key_admin']=$this->key_admin;
                $erp_params['cardno']=$user_arr['cardno'];
                $erp_params['pid']=$pid;
                $erp_params['activity']=$activity_arr['activity'];
                $erp_params['sign_key']=$this->admin_arr['signkey'];
                $erp_params['sign']=sign($erp_params);
                unset($erp_params['sign_key']);
                $erp_arr=json_decode(http($url,$erp_params),true);//处理返回结果
                if($erp_arr['code']==200){
                    //更新数量
                    $activityPropertyNewService->updateIssue($once_arr['pid']);
                    
                    $msg['code']=200;
                    $userCardService->log_integral($activity_arr['activity'], $user_arr['cardno'], $once_arr['integral'], $main, 'F', $this->admin_arr['pre_table'],'',$openid,$pid, '', $params['buildid']);
                }else{
                    $userCardService->log_integral($activity_arr['activity'], $user_arr['cardno'], $once_arr['integral'], $main, 'M', $this->admin_arr['pre_table'],'',$openid,$pid, '', $params['buildid']);
                    $msg=$erp_arr;
                }
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
            $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->key_admin,'coupon_default');
            //$coupon_data['function_name'] == 2;//这里是默认4.0  上线之后删除
            if($coupon_data['function_name'] == 2){
                $msg['code'] = 200;
                $msg['data'] = 1;
            }else{
                $url = "http://182.92.31.114/rest/act/status/".$params['activity_id'];//活动下所有券
                $arr = json_decode(http($url),true);//处理返回结果
                $msg['code'] = 200;
                $msg['data'] = $arr;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 获取banner接口
     */
    public function banner_list(){
        $params['buildid']=I('buildid');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $banner_db=M('banner','integral_');
            $map['admin_id']=array('eq',$this->admin_arr['id']);
            $map['buildid']=array('eq',$params['buildid']);
            $map['status']=array('eq',1);
            $map['_logic']='and';
            $res=$banner_db->where($map)->order('sort desc')->limit(5)->select();
            if($res){
                $msg['code']=200;
                $msg['data']=$res;
            }else{
                $msg['code']=102;
            }
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

    /**
     *　获取用户卡等级
     */
    public function member_cardtype(){
        $params['openid']=I('openid');
        $unionid= I('unionid');
        $userCardService = ServiceLocator::getUserCardService();
        $user_arr = $userCardService->getUserCardByOpenId($this->admin_arr,$this->key_admin,$this->admin_arr['pre_table'], $params['openid'],$unionid);
        

        if($user_arr != 2000){
            $msg['code']=200;

            $use_db=M('member_code','total_');
            $code_arr=$use_db->where("code='".$user_arr['level']."' and admin_id=".$this->admin_arr['id'])->field('name,id')->find();

            $msg['data']['level']=$user_arr['level'];
            $msg['data']['name']=$code_arr['name'];
        }else{
            $msg['code']=$user_arr;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
}
