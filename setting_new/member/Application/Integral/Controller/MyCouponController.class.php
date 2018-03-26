<?php
/**
 * 优惠券管理(C端)
 * Created by EditPlus.
 * User: wutong
 * Date: 7/19/17
 * Time: 18:40
 */

namespace Integral\Controller;
use Think\Controller;
use Common\Controller\CommonController;
use common\ServiceLocator;
use PublicApi\Service\CouponService;
use MerAdmin\Model\ActivityTypeModel;
use MerAdmin\Model\ActivityPropertyModel;

class MyCouponController extends CommonController
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
                $this->admin_arr = $admin_arr;
                $this->key_admin = $key_admin;
            }
        }
    }

    /**
     * 优惠券领取页面
     * @param $key_admin $type_id
     * @return mixed 
     * localhost/member/index.php/Integral/MyCoupon/curl_api?buildid=860100010040500017&key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt658BqthlmdaWd4I20L871Y
     */
    public function curl_api(){
        $type_id = I('type_id');
        $shop_name = I('shop_name');
        $params['buildid'] = I('buildid') ? I('buildid') : "";
        $params['openid']  = I('openid');
        $unionid = I('unionid');
        $shop_name = !empty($shop_name)?urldecode($shop_name):"";
        
        $userCardService = ServiceLocator::getUserCardService();
        $code_arr = $userCardService->getMemberCode($this->admin_arr,$this->ukey,$params['openid'], $this->admin_arr['pre_table'], $this->admin_arr['id'],$unionid);

        //获取一条活动信息
        $activityService = ServiceLocator::getActivityService();
        $integral_arr = $activityService->getAll($this->admin_arr['id'], '', 'ZHT_YX', $params['buildid']);
        $erp_integral_arr = $activityService->getOnce($this->admin_arr['id'], 'ERP_YX', $params['buildid']);
        
        if(empty($integral_arr) && empty($erp_integral_arr))
        {
            $msg=array('code'=>307,'data'=>'暂无活动，敬请期待...');
        }
        else
        {
            if(!empty($integral_arr)){
                foreach($integral_arr as $key=>$val){
                    $activity_id_new[$val['buildid']]['activity'] = $activity_id_new[$val['buildid']]['activity'].$val['activity'].',';
                }
                
                unset($integral_arr);
                foreach($activity_id_new as $k=>$v){
                    $integral_arr_new['buildid'] = $k;
                    $integral_arr_new['activity'] = trim($v['activity'],',');
                    $integral_arr[] = $integral_arr_new;
                }
                
                $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'coupon_default');
                //$coupon_data['function_name'] == 2;//这里是默认4.0  上线之后删除
                
                $act_arr=array();
                foreach ($integral_arr as $k1=>$v1)
                {
//                     if(date('Y-m-d H:i:s', time()) >= $v1['client_showtime'])
                    if($v1['activity']!=''){
                        $act_arr1 = explode(',', $v1['activity']);
                        $act_arr = array_merge($act_arr,$act_arr1);
                    }
                }
                
                $score_arr = array();
                if(!empty($act_arr))
                {
                    foreach($act_arr as $k2=>$v2)
                    {
                        if($coupon_data['function_name'] == 2){
                            $coupon_return_data = CouponService::getListByAct($v2);
                            writeOperationLog(array('coupon_return_data'=>$coupon_return_data),'mycoupon1');
                            if($coupon_return_data['code'] != 200 || $coupon_return_data['data'] == ''){
                                $res = array();
                            }else{
                                foreach($coupon_return_data['data'] as $k4=>$v4){
                                    $coupon_data['pid'] = $v4['id'];
                                    $coupon_data['id'] = $v4['id'];
                                    $coupon_data['main_info'] = $v4['mainInfo'];
                                    $coupon_data['extend_info'] = $v4['descClause']?$v4['descClause']:$v4['mainInfo'];
                                    $coupon_data['image_url'] = $v4['couponImageList'][0]['imgUrl']?$v4['couponImageList'][0]['imgUrl']:'';
                                    $coupon_data['start_time'] = $v4['effectiveStartTime'];
                                    $coupon_data['end_time'] = $v4['effectiveEndTime'];
                                    $coupon_data['effectiveType'] = $v4['effectiveType'];
                                    $coupon_data['activedLimitedStartDay'] = $v4['activedLimitedStartDay'];
                                    $coupon_data['activedLimitedDays'] = $v4['activedLimitedDays'];
                                    $coupon_data['status'] = $v4['validateStatus'] == 1?0:$v4['validateStatus'];
                                    $coupon_data['activity'] = $v2;
                                    $coupon_data['activityname'] = $v4['activityName'];
                                    $coupon_data['activity_type'] = 'ZHT_YX';
                                    $shopName1 = $v4['couponApplyShopList'];
                                    $shopName1 = ArrKeyAll($shopName1,'poiName',false);
                                    $coupon_data['shop_name'] = $shopName1;
                                    $coupon_data['writeoff_count'] = $v4['writeoffNum'];
                                    $coupon_data['issue'] = $v4['getNum'];
                                
                                    //！！！！！！！
                                    //总数需要列表返回    --  目前没有这个参数。需要跟营销平台沟通  详情和列表都需要
                                    $coupon_data['num'] = $v4['pourNum']?$v4['pourNum']:'';
                                    $coupon_data['coupon_pid'] = $v4['couponActivityId'];
                                    $coupon_data_all[] = $coupon_data;
                                }//print_r($coupon_data);die;
                                $res = $coupon_data_all;
                            }//print_R($coupon_return_data);die;
                        }else{
                            $url = "http://101.201.175.219/promo/prize/ka/prize/list/".$v2;//活动下所有券
                            $return = json_decode(http($url,array()),true);//处理返回结果
                            $res = $return['data'];
                            foreach ($res as $i1=>$j1){
                                $res[$i1]['shop_name'] = array($j1['shop_name']);
                            }
                        }
                        
                        if(!empty($res))
                        {
                            foreach ($res as $k3 => $v3)
                            {
                                $v3['activity'] = $v2;
                                $v3['activity_type'] = 'ZHT_YX';
                                $score_arr[] = $v3;
                            }
                            unset($res);
                        }
                    }
                }
            }
            
            if(!empty($erp_integral_arr) && date('Y-m-d H:i:s', time()) >= $erp_integral_arr['client_showtime']){
                $url=C('DOMAIN') . '/ErpService/Erpoutput/prize_list';
                $erp_params['key_admin']=$this->key_admin;
                $erp_params['activity']=$erp_integral_arr['activity'];
                $erp_params['sign_key']=$this->admin_arr['signkey'];
                $erp_params['sign']=sign($erp_params);
                unset($erp_params['sign_key']);
                $erp_arr=json_decode(http($url,$erp_params),true);//处理返回结果
                if($erp_arr['code']==200){
                    foreach($erp_arr['data'] as $k=>$v){
                        $erp_arr['data'][$k]['activity']=$erp_integral_arr['activity'];
                        $erp_arr['data'][$k]['activity_type']='ERP_YX';
                    }
                }else{
                    $erp_arr=array();
                }
            }
            
            if(empty($erp_arr) && !empty($score_arr)){
                $arr=$score_arr;
            }else if(!empty($erp_arr) && empty($score_arr)){
                $arr=$erp_arr['data'];
            }else if(!empty($erp_arr) && !empty($score_arr)){
                $arr=array_merge($erp_arr['data'],$score_arr);
            }else{
                $arr=array();
            }
            writeOperationLog(array('shopname'=>$shop_name),'mycoupon1');

            //处理数据，返回实用数据
            foreach($arr as $k=>$v){
                $endTime = date('Y-m-d 23:59:59', strtotime($v['end_time']));
                
                if(empty($shop_name) || (!empty($shop_name) && in_array($shop_name,$v['shop_name'])))
                {
                    $api_arr[$v['id']]['main']=$v['main_info'];
                    $api_arr[$v['id']]['extend']=$v['extend_info']?$v['extend_info']:$v['main_info'];
                    $api_arr[$v['id']]['imgUrl']=$v['image_url'];
                    $api_arr[$v['id']]['num']=$v['num'];
                    $api_arr[$v['id']]['issue']=$v['issue'];
                    $api_arr[$v['id']]['startTime']=$v['start_time'];
                    $api_arr[$v['id']]['endTime']=strtotime($endTime);
                    $api_arr[$v['id']]['pid']=$v['id'];
                    $api_arr[$v['id']]['status']=$v['status'];
                    $api_arr[$v['id']]['activity']=$v['activity'];
                    $api_arr[$v['id']]['activity_type']=$v['activity_type'];
                    $api_arr[$v['id']]['shop_name']=$v['shop_name'];
                    $api_arr[$v['id']]['coupon_pid']=$v['coupon_pid'];
                    $api_arr[$v['id']]['effectiveType'] = $v['effectiveType'];
                    $api_arr[$v['id']]['activedLimitedStartDay'] = $v['activedLimitedStartDay'];
                    $api_arr[$v['id']]['activedLimitedDays'] = $v['activedLimitedDays'];
                    $api_pid[]=$v['id'];
                }
            }
            
            $integral_pro_db=M('property_new','integral_');
            if($type_id){
                $where_integral['type_id']=array('eq',$type_id);
            }
            if($params['buildid']){
                $where_integral['buildid']=array('eq',$params['buildid']);
            }
            $where_integral['system']=array('eq',1);
            $where_integral['admin_id']=array('eq',$this->admin_arr['id']);
            $where_integral['is_status']=array('eq',2);
            $where_integral['_logic']='and';
            $pro_arr=$integral_pro_db->where($where_integral)->order('des')->select();
            
            foreach($pro_arr as $k=>$v){
                if($v['discount']==2){
//                     if($code_arr){
//                         $integral=json_decode($v['integral'],true);
//                         if($integral[$code_arr['name']]){
//                             $v['integral']=$integral;//$integral[$code_arr['name']]
//                         }else{
//                             $v['integral']=null;
//                         }
//                     }else{
                        $v['integral']=json_decode($v['integral'],true);
//                     }
                }

                if(in_array($v['pid'],$api_pid)){
                    unset($v['status']);
                    unset($v['extend']);
                    unset($v['num']);
                    unset($v['issue']);
                    $res_api[]=array_merge($api_arr[$v['pid']],$v);
                }
            }
            $msg=array('code'=>200,'data'=>$res_api);
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 查询所有分类
     * @param $key_admin
     * @return mixed
     * http://localhost/member/index.php/Integral/MyCoupon/type_api?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function type_api(){
        $activityTypeService = ServiceLocator::getActivityTypeService();

        $integral_type_arr = $activityTypeService->getAll($this->admin_arr['id'], ActivityTypeModel::SYSTEM_1, 2);

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
     * localhost/member/index.php/Integral/MyCoupon/curl_api_once?key_admin=202cb962ac59075b964b07152d234b70&pid=170067
     */
    public function curl_api_once(){
        $act_id = I('pid');//券ID
        $coupon_id = I('couponID');
        if(empty($act_id))
        {
           $msg = array('code'=>1030);
        }
        else
        {
            $params['buildid'] = I('buildid') ? I('buildid') : '';

            $params['openid'] = I('openid');
            $unionid = I('unionid');

            $userCardService = ServiceLocator::getUserCardService();
            $code_arr = $userCardService->getMemberCode($this->admin_arr,$this->ukey,$params['openid'], $this->admin_arr['pre_table'], $this->admin_arr['id'],$unionid);
            
            $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'coupon_default');
            //$coupon_data['function_name'] == 2;//这里是默认4.0  上线之后删除
            
            $status = I('status') ? I('status') : 'ZHT_YX';
            $activity = I('activity');
            if($status == 'ZHT_YX'){
                
                if($coupon_data['function_name'] == 2){
                    $coupon_return_data = CouponService::getDetailById($coupon_id, $act_id);
                    if($coupon_return_data['code'] != 200 || $coupon_return_data['data'] == ''){
                        returnjson(array('code'=>102),$this->returnstyle,$this->callback);exit();
                    }//print_R($coupon_return_data);die;
                    
                    $coupon_data['pid'] = $coupon_return_data['data']['id'];
                    $coupon_data['id'] = $coupon_return_data['data']['id'];
                    $coupon_data['main_info'] = $coupon_return_data['data']['mainInfo'];
                    $coupon_data['extend'] = $coupon_return_data['data']['descClause']?$coupon_return_data['data']['descClause']:$coupon_return_data['data']['mainInfo'];
                    $coupon_data['imgUrl'] = $coupon_return_data['data']['couponImageList'][0]['imgUrl']?$coupon_return_data['data']['couponImageList'][0]['imgUrl']:'';
                    $coupon_data['start_time'] = $coupon_return_data['data']['effectiveStartTime'];
                    $coupon_data['end_time'] = $coupon_return_data['data']['effectiveEndTime'];
                    $coupon_data['effectiveType'] = $coupon_return_data['data']['effectiveType'];
                    $coupon_data['activedLimitedStartDay'] = $coupon_return_data['data']['activedLimitedStartDay'];
                    $coupon_data['activedLimitedDays'] = $coupon_return_data['data']['activedLimitedDays'];
                    $coupon_data['status'] = $coupon_return_data['data']['statusDesc'];
                    $coupon_data['activity'] = $activity;
                    $coupon_data['activityname'] = $coupon_return_data['data']['activityName'];
                    $coupon_data['activity_type'] = 'ZHT_YX';
                    $coupon_data['writeoff_count'] = $coupon_return_data['data']['writeoffNum'];
                    $coupon_data['issue'] = $coupon_return_data['data']['getNum'];
                    $coupon_data['desc'] = $coupon_return_data['data']['descClause']?$coupon_return_data['data']['descClause']:$coupon_return_data['data']['mainInfo'];
                    //！！！！！！！
                    //总数需要列表返回    --  目前没有这个参数。需要跟营销平台沟通   详情和列表都需要
                    $coupon_data['num'] = $coupon_return_data['data']['pourNum']?$coupon_return_data['data']['pourNum']:'';
                    $coupon_data['coupon_pid'] = $coupon_return_data['data']['couponActivityId'];
                    $once_arr = $coupon_data;
                }else{
                    $url = "http://182.92.31.114/rest/act/level/".$act_id;
                    $once_arr = json_decode(http($url,array()),true);
                    $once_arr['start_time'] = $once_arr['startTime'];
                    $once_arr['end_time'] = $once_arr['endTime'];
                }
            }
            elseif($status == 'ERP_YX')
            {
                if($activity == '')
                {
                    $msg=array('code'=>1030);
                    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
                }
                $url=C('DOMAIN') . '/ErpService/Erpoutput/prize_list';
                $erp_params['key_admin']=$this->key_admin;
                $erp_params['activity']=$activity;
                $erp_params['sign_key']=$this->admin_arr['signkey'];
                $erp_params['sign']=sign($erp_params);
                unset($erp_params['sign_key']);
                $erp_arr=json_decode(http($url,$erp_params),true);//处理返回结果
                $once_arr='';
                foreach($erp_arr['data'] as $k=>$v){
                    if($v['id'] == $act_id){
                        $once_arr=$v;
                        $once_arr['imgUrl']=$v['image_url'];
                        $once_arr['pid']=$v['id'];
                    }
                }
            }

            if(!is_array($once_arr))
            {
                $msg=array('code'=>317);
            }
            else
            {
                $activityPropertyNew = ServiceLocator::getActivityPropertyNewService();
                $res = $activityPropertyNew->getOnce($once_arr['pid'], $this->admin_arr['id'], $params['buildid']);
                
                $data['main']=$once_arr['main'];
                $data['extend']=$once_arr['extend'];
                $data['imgUrl']=$once_arr['imgUrl'];
                $data['imgurl']=$once_arr['imgUrl'];
                $data['num']=$once_arr['num'];
                $data['startTime']=$once_arr['start_time'];
                $data['endTime']=$once_arr['end_time'];
                $data['issue']=$once_arr['issue'];
                $data['desc']=$once_arr['desc'];
                $data['marketName']=$once_arr['marketName'];
                $data['logoUrl']=$once_arr['logoUrl'];
                $data['effectiveType'] = $once_arr['effectiveType'];
                $data['activedLimitedStartDay'] = $once_arr['activedLimitedStartDay'];
                $data['activedLimitedDays'] = $once_arr['activedLimitedDays'];

                if($res['discount']==2){
//                     if($code_arr){
//                         $integral=json_decode($res['integral'],true);
//                         if($integral[$code_arr['name']]){
//                             $res['integral']=$integral;//$integral[$code_arr['name']];
//                         }else{
//                             $res['integral']=null;
//                         }
//                     }else{
                        $res['integral']=json_decode($res['integral'],true);
//                     }
                }
                unset($res['imgurl']);
                unset($res['extend']);
                unset($res['num']);
                unset($res['issue']);
                $data=array_merge($data,$res);
                $msg=array('code'=>200,'data'=>$data);
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 查询用户积分
     * @param $key_admin $openid
     * @return mixed
     * http://localhost/member/index.php/Integral/MyCoupon/integral_admin?key_admin=202cb962ac59075b964b07152d234b70&pid=170864&openid=oWm-rt658BqthlmdaWd4I20L871Y
     */
    public function integral_admin(){
        //判断openid
        $openid = I('openid');
        $unionid = I('unionid');
        if(empty($openid)){
            $msg = array('code'=>1030);
        }
        else
        {
            //判断用户是否登录
            $userCardService = ServiceLocator::getUserCardService();
            $user_arr = $userCardService->getUserCardByOpenId($this->admin_arr,$this->ukey,$this->admin_arr['pre_table'], $openid,$unionid);
            
            if($user_arr == 2000)
            {
                echo returnjson(array('code'=>2000),$this->returnstyle,$this->callback);exit();
            }
            else
            {
                $url = C('DOMAIN').'/CrmService/OutputApi/Index/getuserinfobycard';//查询会员信息接口
                $sigs = sign(array('key_admin'=>$this->key_admin,'card'=>$user_arr['cardno'],'sign_key'=>$this->admin_arr['signkey']));
                $url3_arr = http($url,array('key_admin'=>$this->key_admin,'card'=>$user_arr['cardno'],'sign'=>$sigs));
                //返回会员信息
                $arr = json_decode($url3_arr,true);
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
     * 领取券
     * @param $key_admin $pid $main $openid
     * @return mixed
     * http://localhost/member/index.php/Integral/MyCoupon/exchange?key_admin=202cb962ac59075b964b07152d234b70&pid=175607&openid=oWm-rt8LCLZVHnhshK2B1BUkC3sY&main=优惠券2&payType=2&activity=19490
     */
    public function exchange(){
        $pid = I('pid');
        $main = I('main');
        $payType = I('payType');//付款方式 1积分 2微信
        $openid = I('openid');
        $pay_class = I('pay_class');
        $unionid = I('unionid');
        $zhihuitu_openid = I('zht_openid');
        $couponID = I('couponID');//券批ID  4.0 必传
        if(empty($pid) || empty($main) || empty($openid) || empty($payType))
        {
            $msg = array('code'=>1030);
        }
        else
        {
            $main = str_replace(array('&','?'), '', $main);
            
            $params['buildid'] = I('buildid') ? I('buildid') : "";
            $status = I('status') ? I('status') : 'ZHT_YX';
            $activity_id = I('activity');
            
            $userCardService = ServiceLocator::getUserCardService();
            $user_arr = $userCardService->getUserCardByOpenId($this->admin_arr,$this->ukey,$this->admin_arr['pre_table'], $openid,$unionid);
            
            //判断用户是否存在
            if($user_arr == 2000)
            {
                returnjson(array('code'=>2000),$this->returnstyle,$this->callback);
            }
            
            //判断奖品数据是否存在
            $activityPropertyNewService = ServiceLocator::getActivityPropertyNewService();
            $once_arr = $activityPropertyNewService->getOnce($pid, $this->admin_arr['id'], $params['buildid']);
            if(empty($once_arr))
            {
                echo returnjson(array('code'=>307),$this->returnstyle,$this->callback);
            }

            if ($once_arr['wechat_amount'] != false && $once_arr['integral'] != false){
                //判断用户是否存在
                if($user_arr == 2000)
                {
                    returnjson(array('code'=>2000),$this->returnstyle,$this->callback);
                }
            }

            

            

            
            //获取指定卡级别所需积分或人民币
            if($once_arr['discount'] == ActivityPropertyModel::DISCOUNT_2){
                if($user_arr['level'])
                {
                    $code_arr = $userCardService->getMemberCode($this->admin_arr,$this->ukey,$openid, $this->admin_arr['pre_table'], $this->admin_arr['id'],$unionid);
                    
                    if($code_arr)
                    {
                        $once_arr['integral'] = json_decode($once_arr['integral'],true);
                        
                        if($once_arr['integral'][$code_arr['name']] == '')
                        {
                            $msg['code']=1503;
                            echo returnjson($msg,$this->returnstyle,$this->callback);die;
                        }
                        
                        //获取尊享折扣的真实积分消耗和微信人民币消耗
                        if($payType == 1)
                        {
                            $once_arr['integral'] = $once_arr['integral'][$code_arr['name']]['inter'];
                        }
                        else
                        {
                            $once_arr['wechat_amount'] = $once_arr['integral'][$code_arr['name']]['wechat'];
                        }
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
            
            if($activity_id != '')
            {
                $activity_arr['activity'] = $activity_id;
            }
            else
            {
                //获取活动ID
                $activityService = ServiceLocator::getActivityService();
                $activity_arr = $activityService->getOnce($this->admin_arr['id'], $status, $params['buildid']);
            
                if(empty($activity_arr)){
                    $msg['code'] = 307;
                    returnjson($msg,$this->returnstyle,$this->callback);
                }
            
                $activity_res = explode(',', $activity_arr['activity']);
                $activity_arr['activity'] = $activity_res[0];
            }
            
            $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'coupon_default');
            //$coupon_data['function_name'] == 2;//这里是默认4.0  上线之后删除
            
            //缺少4.0活动状态接口
            if($coupon_data['function_name'] == 2){
                $activity_status_res = 1;//3.0判断4.0
            }else{
                $activity_status_url = "http://182.92.31.114/rest/act/status/".$activity_arr['activity'];//活动下所有券
                $activity_status_res = json_decode(http($activity_status_url, array()),true);//处理返回结果
            }
//             echo $res;die;
            if($activity_status_res!=1)//默认0准备中（审核通过，前提是当前状态为5）、1.已开始、2.暂停、3.已结束、默认为0、5.审核中，6、未审核，7驳回 8.已删除
            {
                returnjson(array('code'=>1082,'data'=>'活动未开启','msg'=>'活动未开启'),$this->returnstyle,$this->callback);
            }
            
            
            //积分支付
            if($payType == 1)
            {
                $amount = $once_arr['integral'];
                
                //判断用户积分是否充足
                $arr = $userCardService->getUserCardInfo($this->key_admin, $user_arr['cardno'], $this->admin_arr['signkey'],$unionid);
                
                if($arr['data']['score'] < (int)$amount){
                    returnjson(array('code'=>319),$this->returnstyle,$this->callback);
                }
                
                //扣除用户积分积分
                if($amount == 0)//花费为0或者免费
                {
                    $res['code'] = 200;
                }
                else
                {
                    $res = $userCardService->del_integral($this->key_admin,$this->admin_arr['signkey'],$user_arr['cardno'],$once_arr['integral'],$main,$unionid);
                }
                
                if($res['code'] >= 6000 && $res['code'] < 7000)
                {
                    returnjson($res,$this->returnstyle,$this->callback);
                }

                if($res['code'] != 200)
                {
                    $msg = array('code'=>$res['code'],'msg'=>$res['msg']);
                    returnjson($msg,$this->returnstyle,$this->callback);
                }
            }
            elseif($payType == 2)
            {
                //如果是免费券直接发券
                if($once_arr['wechat_amount'] == 0)
                {
                    $commonService = ServiceLocator::getCommonService();
                    if($status == 'ZHT_YX')
                    {
                        if($coupon_data['function_name'] == 2 ){
//                             $coupon_return = CouponService::giveCouponCheck($pid,$activity_arr['activity'],$openid,$couponID,1,'','');
                            $coupon_return = CouponService::giveCoupon($pid,$openid,$couponID,1,'','');
                            if($coupon_return['code'] == 200){
                                $mianfei_return['code'] = 0;
                                
                                $userCardService->log_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,'F',$this->admin_arr['pre_table'],'',$openid,$pid,$coupon_return['data']['qrcode']);
                            }else{
                                $mianfei_return['code'] = 1082;
                                $mianfei_return['message'] = $mianfei_return['msg'];
                            }
                        }else{
                            $mianfei_return = $commonService->getPrize($activity_arr['activity'], $openid, $pid, $user_arr['cardno'], $main, $this->admin_arr['pre_table']);
                        }                    
                    }
                    elseif($status == 'ERP_YX')
                    {
                        $mianfei_return1 = $commonService->getErpPrize($activity_arr['activity'], $openid, $pid, $user_arr['cardno'], $main, $this->admin_arr['pre_table'], $this->admin_arr['signkey']);
                    }
                    
                    if($mianfei_return['code'] == 0 || $mianfei_return1['code'] == 200){
                        $activityPropertyNewService->updateIssue($pid);
                    }
                    
                   $data =  array('code' => '200', 'msg' => 'SUCCESS!');
                   returnjson($data, $this->returnstyle, $this->callback);
                }
                
                //微信支付
                $notify_url = C('DOMAIN') . "/Integral/Wechat/confirmPay";
                $commonService = ServiceLocator::getCommonService();
                
                //微信回调参数
                $attach = array(
                    'name' => $main,
                    'key_admin' => $this->key_admin,
                    'amount' => $once_arr['wechat_amount'],
                    'openid' => $openid,
                    'activityId' => $activity_arr['activity'],
                    'pid' => $pid,
                    'cardno' => $user_arr['cardno'],
                    'pre_table' => $this->admin_arr['pre_table'],
                    'signkey' => $this->admin_arr['signkey'],
                    'status' => $status,
                    'couponID' => $couponID,
                );
                
                $data = $commonService->paybyweixinvs2($this->key_admin, $openid, $once_arr['wechat_amount'], $main, $notify_url, $this->admin_arr['pre_table'], $pay_class, $attach, $this->admin_arr['id'], 0 ,$payType,$zhihuitu_openid);
            
                returnjson($data, $this->returnstyle, $this->callback);
            }
            else
            {
                returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
            }
            
            //领券
            if($status == 'ZHT_YX')
            {
                //4.0
                if($coupon_data['function_name'] == 2 ){
//                     $coupon_return = CouponService::giveCouponCheck($pid,$activity_arr['activity'],$openid,$couponID,1,'','');
                    $coupon_return = CouponService::giveCoupon($pid,$openid,$couponID,1,'','');
                    $act_res = array();
                    if($coupon_return['code'] == 200){
                        $act_res['code']=0;
                        $act_res['qr']=$coupon_return['data']['qrCode'];
                    }else{
                        $act_res['code']= 1;
                        $act_res['message'] = $coupon_return['msg'];
                    }
                    
                }else{
                    $url2='http://101.201.176.54/rest/act/prize/'.$activity_arr['activity'].'/'.$pid.'/'.$openid;//领券接口
                    $act_arr=http($url2,array());
                    $act_res=json_decode($act_arr,true);
                }
                
                if($act_res['code']==0){
                    //领券成功写入日志
                    $userCardService->log_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,'F',$this->admin_arr['pre_table'],'',$openid,$pid,$act_res['qr']);
                    $msg=array('code'=>200,'data'=>'领奖成功');
                    $activityPropertyNewService->updateIssue($pid);
                    returnjson($msg,$this->returnstyle,$this->callback);
                }

                //判断是否为0积分
                if($once_arr['integral']==0){
                    $return_integral['code']=200;
                }else{
                    //领券失败返回积分
                    $return_integral=$userCardService->add_integral($this->key_admin,$this->admin_arr['signkey'],$arr['data']['user'],$once_arr['integral'],$user_arr['cardno'],$main,$res['data']['scorecode'],$unionid);
                }

                //记录日志
                $return_log_id=$userCardService->log_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,'M',$this->admin_arr['pre_table'],'',$openid,$pid,'');
                if($return_integral['code']==200){
                    //恢复积分成功修改日志状态
                    $userCardService->log_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,'A',$this->admin_arr['pre_table'],$return_log_id);
                    $msg=array('code'=>1082,'msg'=>$act_res['message']);
                }else{
                    $msg=array('code'=>304);
                }
                returnjson($msg,$this->returnstyle,$this->callback);
            }

            if($status=='ERP_YX'){
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
                    $msg['code']=200;
                    $activityPropertyNewService->updateIssue($pid);
                    $userCardService->log_integral($activity_arr['activity'], $user_arr['cardno'], $once_arr['integral'], $main, 'F', $this->admin_arr['pre_table'],'',$openid,$pid);
                }else{
                    $userCardService->log_integral($activity_arr['activity'], $user_arr['cardno'], $once_arr['integral'], $main, 'M', $this->admin_arr['pre_table'],'',$openid,$pid);
                    $msg=$erp_arr;
                }
                returnjson($msg,$this->returnstyle,$this->callback);
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 获取支付账号状态
     */
    public function pay_status(){
        $def_re = $this->GetOneAmindefault($this->admin_arr['pre_table'], $this->key_admin, 'public_pay_config');
        $sub_mich1 = json_decode($def_re['function_name'], true);
        if($sub_mich1){
            $msg['code'] = 200;
            $msg['data'] = $sub_mich1['publicismacc'];
        }else{
            $msg['code'] = 102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    public function banner_list_new(){
        $params['buildid']=I('buildid');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $banner_db=M('banner_coupon','integral_');
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
    
}
