<?php
/**
 * 优惠券管理(后台)
 */
namespace MerAdmin\Controller;

use common\ServiceLocator;
use MerAdmin\Model\ActivityModel;
use MerAdmin\Model\ActivityTypeModel;
use MerAdmin\Model\ActivityPropertyModel;
use MerAdmin\Model\ActivityPropertyNewModel;
use PublicApi\Service\CouponService;

class MyCouponController extends AuthController
{
    public $key_admin;
    public $admin_arr;
    
    public function _initialize(){
        parent::_initialize();
        $this->key_admin = $this->ukey;
        $this->admin_arr=$this->getMerchant($this->ukey);
        $this->bannerImg = "https://img.rtmap.com/cardbag/banner.jpg";
    }

    
    /**
     * 获取活动配置
     * @return
     */
    public function obtain_act(){
        $params['buildid'] = I('buildid') ? I('buildid') : "";
        $params['type'] = I('type')?I('type'):'';
        $activityService = ServiceLocator::getActivityService();
        $arr = $activityService->getAll($this->admin_arr['id'], '', $params['type'], $params['buildid'], ActivityModel::SYSTEM_1);
        
//         $data = array();
        if(!empty($arr))
        {
            $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'coupon_default');
            foreach ($arr as $k => $v)
            {
                if($v['type'] == 'ZHT_YX'){
                    $arr[$k]['status'] = 1;   
                }
            }
        }
        
        $msg = array('code'=>200, 'data' => $arr);
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 添加活动
     * @param $key_admin $activity
     * @return mixed
     * http://localhost/member/index.php/MerAdmin/MyCoupon/act_add?key_admin=202cb962ac59075b964b07152d234b70&activity=183263&buildid=860100010040500017&type=ERP_YX&client_showtime=2017-08-31 15:58:11
     */
    public function act_add(){
        $params['activity'] = I('activity');
        if(in_array('', $params)){
            $msg['code'] = 1030;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'coupon_default');
        if($coupon_data['function_name'] == 2){
            $res = 1;
        }else{
            $url = "http://182.92.31.114/rest/act/status/".$params['activity'];
            $res = json_decode(http($url, array()),true);
        }
        
        //活动不存在
        if($res == -1)
        {
            $msg['code'] = 1595;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        if($coupon_data['function_name'] == 2){
            $activityName['data']['name'] = '';
        }else{
            $urls = "http://101.201.175.219/promo/api/ka/activity/detail?activityId=".$params['activity'];
            $nameres = file_get_contents($urls);
            //活动不存在
            if(empty($nameres))
            {
                $msg['code'] = 1595;
                echo returnjson($msg,$this->returnstyle,$this->callback);exit();
            }
            
            $activityName = json_decode($nameres,true);
        }
        $params['type'] = I('type')?I('type'):'ZHT_YX';
        $params['buildid'] = I('buildid')?I('buildid'):"";
        $params['activity'] = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)/",',',$params['activity']);
        $params['activityname'] = $activityName['data']['name']?$activityName['data']['name']:'';
        $params['system'] = ActivityModel::SYSTEM_1;

        //判断是否存在当前的配置,如果存在则是修改,反之添加
        $activityService = ServiceLocator::getActivityService();
        //验证活动id是否被使用
        $res = $activityService->getAll($this->admin_arr['id'], $params['activity'], '', $params['buildid'], ActivityModel::SYSTEM_1);
        
        if(!empty($res))
        {
            $msg['code'] = 1504;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }

        //添加
        $params['admin_id'] = $this->admin_arr['id'];
        $res = $activityService->add($params);
         
        if($res !== false){
            $msg['code']=200;
        }else{
            $msg['code']=104;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 修改活动
     * @param $key_admin $id
     * @param $key_admin $activity
     * @return mixed
     * localhost/member/index.php/MerAdmin/MyCoupon/act_update?key_admin=202cb962ac59075b964b07152d234b70&client_showtime=2017-08-31 15:58:11&id=35
     */
    public function act_update(){
        $params['id'] = I('id');
        if(in_array('', $params)){
            $msg['code'] = 1030;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $params['client_showtime'] = I('client_showtime') ? I('client_showtime') : '';
        
        //判断是否存在当前的配置,如果存在则是修改,反之添加
        $activityService = ServiceLocator::getActivityService();
        
        $activity_arr = $activityService->getById($params['id']);
        
        if(empty($activity_arr))
        {
            $msg['code'] = 1035;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        else
        {
            //修改
            $res = $activityService->updateById($activity_arr['id'], $params);
        }
         
        if($res !== false){
            $msg['code']=200;
        }else{
            $msg['code']=104;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 删除活动
     * @param $id
     * localhost/member/index.php/MerAdmin/MyCoupon/act_del?key_admin=202cb962ac59075b964b07152d234b70&id=35
     */
    public function act_del(){
        $params['id'] = I('id');
        if(in_array('', $params)){
            $msg['code'] = 1030;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $activityService = ServiceLocator::getActivityService();
        $res = $activityService->delById($params['id']);
        
        if($res !== false){
            $msg['code']=200;
        }else{
            $msg['code']=104;
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 查询分类列表
     * @param $key_admin
     * @return mixed
     * localhost/member/index.php/MerAdmin/MyCoupon/integral_type_list?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function integral_type_list(){
        $activityTypeService = ServiceLocator::getActivityTypeService();
        $arr = $activityTypeService->getAll($this->admin_arr['id'], ActivityTypeModel::SYSTEM_1, 2);
        
        if($arr)
        {
            $msg=array('code'=>200,'data'=>$arr);
        }
        else
        {
            $msg=array('code'=>102);
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 添加&修改分类名称
     * @param $key_admin $id $type_name $status
     * @return mixed
     */
    public function integral_type_save(){
        //参数为空判断
        $id = I('id');
        $name = I('name');
        if(empty($name))
        {
            $msg=array('code'=>1030);
        }
        else
        {
            $activityTypeService = ServiceLocator::getActivityTypeService();
            $arr = $activityTypeService->getById($id);
            
            //添加
            if(empty($arr))
            {
                $data = $activityTypeService->getByName($this->admin_arr['id'], $name);
                
                if(!empty($data))
                {
                    //已经存在
				   echo returnjson(array('code'=>1008),$this->returnstyle,$this->callback);exit();
                }
                else
                {
                    $res = $activityTypeService->add(array('admin_id' => $this->admin_arr['id'], 'type_name' => $name, 'system' => ActivityModel::SYSTEM_1));
                }
            }
            else
            {//修改
                $res = $activityTypeService->updateById($arr['id'], array('type_name' => $name));
            }
            
            if($res)
            {
                $msg=array('code'=>200);
            }
            else
            {
                $msg=array('code'=>104);
            }
         }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 删除分类
     * @param $key_admin $id
     * @return mixed
     */
    public function integral_type_del(){
        $id = I('id');
        if(empty($id))
        {
            $msg = array('code'=>1030);
        }
        else
        {
            $activityTypeService = ServiceLocator::getActivityTypeService();
            $res = $activityTypeService->delById($id);
            
            if($res)
            {
                $msg = array('code'=>200);
            }
            else
            {
                $msg = array('code'=>104);
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 查询所有奖品信息
     * @param $key_admin $type_id
     * @return mixed
     * http://localhost/member/index.php/MerAdmin/MyCoupon/integral_list?buildid=860100010040500017&key_admin=202cb962ac59075b964b07152d234b70
     */
    public function integral_list(){
        $params['buildid'] = I('buildid') ? I('buildid') : "";
        $params['activity'] = I('activity') ? I('activity') : '';
        $params['status'] = I('status') != null ? I('status') : '';//券状态0:正常(投放中)  1:暂停 2:待发布(审核通过) 3:已过期 4: 待审核 5:驳回  9:删除
        $page = I('page') ? I('page') : 1;
    
        //查询活动ID
        $activityService = ServiceLocator::getActivityService();
        $integral_arr = $activityService->getAll($this->admin_arr['id'], $params['activity'], '', $params['buildid'], ActivityModel::SYSTEM_1, 0, 0);
        $inClienShowTime = array();
// print_r($integral_arr);die;
        if(empty($integral_arr))
        {
            $msg = array('code'=>307,'data'=>'暂无奖品，敬请期待...');
        }
        else
        {
            $activity_id_new = '';
            $activity_id_erp = '';
            foreach($integral_arr as $key=>$val){
                if($val['type'] == 'ZHT_YX'){
                    $activity_id_new = $activity_id_new.$val['activity'].',';
                }else{
                    $activity_id_erp = $activity_id_erp.$val['activity'].',';
                }
            }
            $integral_arr = array();
            $integral_arr_new['activity'] = trim($activity_id_new,',');
            $integral_arr_new['buildid'] = $params['buildid'];
            $integral_arr_new['admin_id'] = $this->admin_arr['id'];
            $integral_arr_new['system'] = ActivityModel::SYSTEM_1;
            $integral_arr_new['type'] = 'ZHT_YX';
            $integral_arr[] = $integral_arr_new; 
            $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->key_admin,'coupon_default');
            //$coupon_data['function_name'] == 2;//这里是默认4.0  上线之后删除
            //过滤无用活动
            foreach ($integral_arr as $k => $v)
            {
                $activitys = explode(',', $v['activity']);
                
                foreach ($activitys as $k2 => $v2)
                {
                    if($coupon_data['function_name']==2){
//                         //等待营销平台出4.0获取活动ID状态接口
//                         $url = "";
                        
                    }else{
                        $url = "http://182.92.31.114/rest/act/status/".$v2;//活动下所有券
                        $res = json_decode(http($url, array()),true);//处理返回结果
                        
                        if(!in_array($res, array(0,1,2,3)))//默认0准备中（审核通过，前提是当前状态为5）、1.已开始、2.暂停、3.已结束、默认为0、5.审核中，6、未审核，7驳回 8.已删除
                        {
                            unset($integral_arr[$k]);
                        }
                    }
                }
            }
    
            $score_arr = array();//奖品信息
            $api_arr   = array();//最终返回数据
            $res_data = array();//筛选后的数据
            //获取所有券列表
            $score_arr = $activityService->getCouponListByActivity($integral_arr,$coupon_data['function_name']);
            if(empty($score_arr)){
                if($params['status'] != null){
                    $msg['code']=102;
                }
            }
            else
            {
                //整合数据
                $activityTypeService = ServiceLocator::getActivityTypeService();
                $activityPropertyNewService = ServiceLocator::getActivityPropertyNewService();
                foreach($score_arr as $k => $v)
                {
                    $property = $activityPropertyNewService->getOnce($v['id'], '', $params['buildid'], ActivityPropertyNewModel::SYSTEM_1);
    
                    if($params['status'] == '' || ($params['status'] != '' && $params['status'] == $v['status']))
                    {
                        $api_arr[$v['id']]['pid']       = $v['id'];//券ID
                        $api_arr[$v['id']]['main']      = $v['main_info'];//主标题
                        $api_arr[$v['id']]['extend']    = $v['extend_info'] ? $v['extend_info'] : $v['main_info'];//副标题
                        $api_arr[$v['id']]['imgUrl']    = $v['image_url'] ? $v['image_url'] : '';//券图片地址
                        $api_arr[$v['id']]['startTime'] = $v['start_time'];
                        $api_arr[$v['id']]['endTime']   = $v['end_time'];
                        $api_arr[$v['id']]['status']    = $v['status'];//券状态0:正常(投放中)  1:暂停 2:待发布(审核通过) 3:已过期 4: 待审核 5:驳回  9:删除
                        $api_arr[$v['id']]['activity']  = $v['activity'];
                        $api_arr[$v['id']]['activityname']  = $v['activityname'];
                        $api_arr[$v['id']]['activity_type']  = $v['activity_type'];
                        $api_arr[$v['id']]['writeoff_count'] = $v['writeoff_count'];//券核销数量
                        $api_arr[$v['id']]['issue']     = $v['issue'];//券发放数量
                        $api_arr[$v['id']]['num']       = $v['num'];//券数量
                        $api_arr[$v['id']]['coupon_pid']       = $v['coupon_pid'];//券批ID
                        $api_arr[$v['id']]['effectiveType'] = $v['effectiveType'];
                        $api_arr[$v['id']]['activedLimitedStartDay'] = $v['activedLimitedStartDay'];
                        $api_arr[$v['id']]['activedLimitedDays'] = $v['activedLimitedDays'];

                        if(!empty($property))
                        {
                            if($property['discount'] == ActivityPropertyModel::DISCOUNT_2)
                            {
                                $integral = json_decode($property['integral'],true);
                            }
                            else
                            {
                                $integral = $property['integral'];
                            }
                        }
                        else
                        {
                            $integral = '';
                        }
    
                        $type = $activityTypeService->getById($property['type_id'], ActivityTypeModel::SYSTEM_1);
    
                        //本地数据
                        $api_arr[$v['id']]['id']        = !empty($property) ? $property['id'] : '';
                        $api_arr[$v['id']]['des']       = !empty($property) ? $property['des'] : '';//排序
                        $api_arr[$v['id']]['is_status'] = !empty($property) ? $property['is_status'] : ActivityPropertyModel::STATUS_1;//状态
                        $api_arr[$v['id']]['type_id']   = !empty($property) ? $property['type_id'] : '';//分类ID
                        $api_arr[$v['id']]['type_name'] = !empty($type) ? $type['type_name'] : '';//分类名字
                        $api_arr[$v['id']]['admin_id']  = !empty($property) ? $property['admin_id'] : '';//商场ID
                        $api_arr[$v['id']]['content']   = !empty($property) ? $property['content'] : '';//礼品详情
                        $api_arr[$v['id']]['discount']  = !empty($property) ? $property['discount'] : ActivityPropertyModel::DISCOUNT_1;//是否开启折扣
                        $api_arr[$v['id']]['wechat_amount']  = !empty($property) ? $property['wechat_amount'] : 0;//微信金额
                        
                        $api_arr[$v['id']]['integral']  = $integral;
    
                        $res_data[] = $api_arr[$v['id']];
                    }
                }
            }
//             print_R($res_data);die;
            $msg=array('code'=>200,'data'=>$res_data?$res_data:array());
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 查询奖品信息
     * @param $key_admin $pid
     * @return mixed
     * http://localhost/member/index.php/MerAdmin/MyCoupon/integral_list_once?buildid=860100010040500017&key_admin=202cb962ac59075b964b07152d234b70&pid=174739
     */
    public function integral_list_once(){
        //参数为空判断
        $act_id = I('pid');
        if(empty($act_id))
        {
            $msg = array('code'=>1030);
        }
        else
        {   
            $buildid = I('buildid') ? I('buildid') : '';
    
            //连表查询券所属分类以及券的一些信息
            $activityPropertyNewService = ServiceLocator::getActivityPropertyNewService();
            $pro_arr = $activityPropertyNewService->joinIntegralType($act_id, $this->admin_arr['id'], $buildid,'integral_type on integral_property_new.type_id=integral_type.id');
            
            if(empty($pro_arr))
            {
                $msg=array('code'=>102);
            }
            else
            {
                if($pro_arr['discount'] == ActivityPropertyModel::DISCOUNT_2)
                {
                    $pro_arr['integral'] = json_decode($pro_arr['integral'],true);
                }
                 
                $msg=array('code'=>200,'data'=>$pro_arr);
    
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 修改奖品信息
     * @param $key_admin $pid $des $integral $type_id
     * @return mixed
     * localhost/member/index.php/MerAdmin/MyCoupon/integral_operation?buildid=860100010040500017&key_admin=202cb962ac59075b964b07152d234b70&activity_id=19490&des=1&pid=175607&type_id=79&wechat_amount=1&integral={"\u94f6\u5361":{"inter":10,"wechat":1},"\u91d1\u5361":{"inter":5,"wechat":2}}&discount=2
     */
    public function integral_operation(){
        //参数为空判断
        $act_id        = I('pid');
        $des           = I('des');
        $type_id       = I('type_id');//分类
        $activity_id   = I('activity_id');
        //$get_type      = I('get_type');//领取类型,0:免费,1:付费
        $integral      = I('integral');//所需积分
        $discount      = I('discount') ? I('discount') : ActivityPropertyModel::DISCOUNT_1;//是否开启折扣,1统一折扣，2尊享折扣
        $wechat_amount = I('wechat_amount');//微信支付金额 单位分
        $activity_type = I('activity_type') ? I('activity_type') : 'ZHT_YX';
        $buildid       = I('buildid') ? I('buildid') : '';
        $coupon_id = I('couponID');
        if(empty($act_id) || !isset($des) || empty($type_id)  || empty($activity_id))
        {
            $msg=array('code'=>1030);
        }
        else
        {
            
            $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->key_admin,'coupon_default');
            //$coupon_data['function_name'] == 2;//这里是默认4.0  上线之后删除
            
            $activityService = ServiceLocator::getActivityService();
            //修改单条数据
            $once_arr = $activityService->getOnceCouponListByActivity($activity_id, $activity_type, $act_id, $coupon_data['function_name'],$coupon_id);
//             print_r($once_arr);die;
            if(empty($once_arr))
            {
                $msg['code'] = 1035;
                returnjson($msg,$this->returnstyle,$this->callback);exit();
            }
            
            $activityTypeService = ServiceLocator::getActivityTypeService();
            $type_arr = $activityTypeService->getById($type_id);
            
            if(!$type_arr)
            {
                $msg['code'] = 1081;
                returnjson($msg,$this->returnstyle,$this->callback);exit();
            }
            
            //查看表中是否存在这条信息，如果存在则是修改，如果不存在则是添加
            $activityPropertyNewService = ServiceLocator::getActivityPropertyNewService();
            $pro_arr = $activityPropertyNewService->getOnce($act_id, $this->admin_arr['id'], $buildid);
            //平台数据
            $data['pid']            = $act_id;//奖品ID
            $data['activity_id']    = $activity_id;//活动id
            $data['activity_type']  = $activity_type;
            $data['main']           = $once_arr['main_info'];
            $data['extend']         = $once_arr['extend_info'];
            $data['imgUrl']         = $once_arr['image_url'];
            $data['start_time']     = $once_arr['start_time'];
            $data['end_time']       = $once_arr['end_time'];
            $data['status']         = $once_arr['status'];
            $data['writeoff_count'] = $once_arr['writeoff_count'];
            $data['issue']          = $once_arr['issue'];
            $data['num']            = $once_arr['num'];
            //本地数据
            $data['buildid']        = $buildid;
            $data['integral']       = $integral;//兑换积分htmlspecialchars_decode($integral);
            $data['des']            = $des;//排序
            $data['is_status']      = ActivityPropertyModel::STATUS_2;//状态
            $data['type_id']        = $type_id;//分类ID
            $data['admin_id']       = $this->admin_arr['id'];
            $data['content']        = '';//htmlspecialchars_decode($content);//礼品详情
            $data['discount']       = $discount;//是否开启折扣
            $data['get_type']       = 0;//领取类型
            $data['wechat_amount']  = $wechat_amount;//微信支付金额
            $data['system']  = ActivityPropertyModel::SYSTEM_1;

            $data['vip_area'] = 0;
            $data['activity_status'] = '';//奖品名称
            
            if($discount == ActivityPropertyModel::DISCOUNT_2)
            {
                $data['integral'] = json_encode($integral);
            }
            else
            {
                $data['integral'] = $integral;
            }
            
            if(empty($pro_arr))
            {
                $res = $activityPropertyNewService->add($data);
            }
            else
            {
                unset($data['pid']);
                $res = $activityPropertyNewService->updateById($pro_arr['id'], $data);
            }
            
            if($res !== false){
                $msg=array('code'=>200);
            }else{
                $msg=array('code'=>104);
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 数据更新
     * @param $key_admin
     * http://localhost/member/index.php/MerAdmin/MyCoupon/update_operation?&key_admin=202cb962ac59075b964b07152d234b70
     */
    public function update_operation(){
        //查询活动ID
        $activityService = ServiceLocator::getActivityService();
        $integral_arr = $activityService->getAll($this->admin_arr['id'], '', '', '', ActivityModel::SYSTEM_1);
    
        if(empty($integral_arr))
        {
            $msg = array('code'=>307,'data'=>'暂无奖品，敬请期待...');
        }
        else
        {
            
            $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->key_admin,'coupon_default');
            //$coupon_data['function_name'] == 2;//这里是默认4.0  上线之后删除
            $score_arr = array();//奖品信息
            $api_arr   = array();//最终返回数据
            $score_arr = $activityService->getCouponListByActivity($integral_arr, $coupon_data['function_name']);
    
            if(empty($score_arr))
            {
                $msg['code'] = 102;
            }
            else
            {
                //整合数据
                $activityPropertyNewService = ServiceLocator::getActivityPropertyNewService();
                foreach($score_arr as $k => $v)
                {
                    $propertys = $activityPropertyNewService->getAll('',  '',  '',  '', $v['id']);
    
                    if(!empty($propertys))
                    {
                        foreach ($propertys as $k => $v2)
                        {
                            $v2['activity_id']    = $v['activity'];//活动id
                            $v2['activity_type']  = $v['activity_type'];
                            $v2['main']           = $v['main_info'];
                            $v2['extend']         = $v['extend_info'];
                            $v2['imgUrl']         = $v['image_url'];
                            $v2['start_time']     = $v['start_time'];
                            $v2['end_time']       = $v['end_time'];
                            $v2['status']         = $v['status'];
                            $v2['writeoff_count'] = $v['writeoff_count'];
                            $v2['issue']          = $v['issue'];
                            $v2['num']            = $v['num'];
    
                            $activityPropertyNewService->updateById($v2['id'], $v2);
                        }
                    }
                }
            }
    
            $msg = array('code'=>200);
        }
    
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 上下线操作
     * @param $key_admin
     * @return mixed
     * http://localhost/member/index.php/MerAdmin/MyCoupon/status_operation?key_admin=202cb962ac59075b964b07152d234b70&status=1&pid=169301
     */
    public function status_operation(){
        //参数为空判断
        $act_id  = I('pid');
        $status = I('status');//状态,1下线，2上线
        $buildid  = I('buildid');
    
        if(empty($act_id) || empty($status))
        {
            $msg = array('code' => 1030);
        }
        else
        {
            $act_ids = explode(',', $act_id);
    
            //查看表中是否存在这条信息，如果存在则是修改，如果不存在则是添加
            $activityPropertyNewService = ServiceLocator::getActivityPropertyNewService();
    
            foreach ($act_ids as $k => $v)
            {
                $pro_arr = $activityPropertyNewService->getOnce($v, $this->admin_arr['id'], $buildid);
    
                //未关联的数据不能操作
                if(empty($pro_arr))
                {
                    $msg = array('code'=>1030);
                    returnjson($msg,$this->returnstyle,$this->callback);exit();
                }
    
                $pro_arr['is_status'] = $status;
    
                $res = $activityPropertyNewService->updateById($pro_arr['id'], $pro_arr);
    
                if($res !== false)
                {
                    $msg=array('code'=>200);
                }
                else
                {
                    $msg=array('code'=>104);
                }
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 获取卡类别接口
     */
    public function card_type_list(){
        $db=M('member_code','total_');
        $where['admin_id']=array('eq',$this->admin_arr['id']);
        $arr=$db->where($where)->order('sort asc')->select();
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 获取建筑物ID
     */
    public function builid_list(){
        $where['adminid']=array('eq',$this->admin_arr['id']);
        $where['is_del']=array('eq',2);
        $where['_logic']='and';
        $db=M('buildid','total_');
        $arr=$db->where($where)->select();
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


    //newadd

    /**
     * 获取banner列表(老接口迁移过来,表结构稍做调整)
     */
    public function banner_list(){
        $buildid=I('buildid');
        if($buildid){
            $map['buildid']=array('eq',$buildid);
            $map['_logic']='and';
        }
        $banner_db=M('banner_coupon','integral_');
        $map['admin_id']=array('eq',$this->admin_arr['id']);
        $res=$banner_db->where($map)->order('sort desc')->select();

        $where['adminid']=array('eq',$this->admin_arr['id']);
        $db=M('buildid','total_');
        $arr=$db->where($where)->select();

        foreach($arr as $k=>$v){
            $arr[$v['buildid']]=$v['name'];
        }

        foreach($res as $key=>$val){
            if($arr[$val['buildid']] != ''){
                $res[$key]['name']=$arr[$val['buildid']];
            }else{
                $res[$key]['name']=$val['buildid'];
            }
        }

        if($res){
            $msg['code']=200;
            $msg['data']=$res;
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 取单个banner接口(之前接口迁移过来)
     */
    public function banner_find(){
        $params['id']=I('banner_id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $banner_db=M('banner_coupon','integral_');
            $map['admin_id']=array('eq',$this->admin_arr['id']);
            $map['id']=array('eq',$params['id']);
            $map['_logic']='and';
            $res=$banner_db->where($map)->find();
            if($res){
                $where['adminid']=array('eq',$this->admin_arr['id']);
                $where['buildid']=array('eq',$res['buildid']);
                $where['_logic']='and';
                $db=M('buildid','total_');
                $arr=$db->where($where)->find();
                $res['name']=$arr['name']?$arr['name']:$res['buildid'];
                $msg['code']=200;
                $msg['data']=$res;
            }else{
                $msg['code']=102;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 修改或添加Y币商城的banner
     */
    public function banner_save(){
        $params['url']=I('img_url');
        $params['url'] = !empty($params['url'])?$params['url']:$this->bannerImg;
        $params['buildid']=I('buildid');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $params['jump_url']=I('jump_url');
            $banner_id=I('id');
            $banner_db=M('banner_coupon','integral_');
            if($banner_id){
                $map['id']=array('eq',$banner_id);
                $map['admin_id']=array('eq',$this->admin_arr['id']);
                $map['_logic']='and';
                $res=$banner_db->where($map)->save($params);
            }else{
                $where1 = array('buildid'=>$params['buildid']);
                $count=$banner_db->where($where1)->count();
                if((int)$count>=5){
                    returnjson(array('code'=>1082,'msg'=>"每个建筑物下最多添加5条记录！"),$this->returnstyle,$this->callback);
                }
                $params['admin_id']=$this->admin_arr['id'];
                $params['status']=2;
                $params['sort']=time();
                $res=$banner_db->add($params);
            }
            if($res !== false){
                $msg['code']=200;
            }else{
                $msg['code']=104;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * banner删除接口(迁移老接口)
     */
    public function banner_del(){
        $params['id']=I('banner_id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $banner_db=M('banner_coupon','integral_');
            $map['id']=array('eq',$params['id']);
            $map['admin_id']=array('eq',$this->admin_arr['id']);
            $map['_logic']='and';
            $re=$banner_db->where($map)->delete();
            if($re){
                $msg['code']=200;
            }else{
                $msg['code']=104;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * banner记录上下移动(新增接口)
     */
    public function banner_move(){
        $params['id']=I('banner_id');
        $params['direction']=I('direction');//移动方向(1：向上，2：向下)
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $banner_db=M('banner_coupon','integral_');
            //查询当前记录
            $map['id']=array('eq',$params['id']);
            $map['admin_id']=array('eq',$this->admin_arr['id']);
            $map['_logic']='and';
            $re=$banner_db->where($map)->find();
            if(!$re){
                returnjson(array('code'=>1082,'msg'=>"未找到记录"),$this->returnstyle,$this->callback);
            }
            //查询将要移动的记录
            list($order,$operator) = $params['direction']=="1"?array("asc","gt"):array("desc","lt");
            $where = array('admin_id'=>$this->admin_arr['id'],'sort'=>array($operator,$re['sort']));
            $re_move = $banner_db->where($where)->order("sort $order")->find();

            if(!$re_move){
                returnjson(array('code'=>1082,'msg'=>"官人,移不动啦!"),$this->returnstyle,$this->callback);
            }
            $banner_db->startTrans();
            $up2=$banner_db->where(array('id'=>$re_move['id']))->save(array('sort'=>0));
            $up1=$banner_db->where(array('id'=>$re['id']))->save(array('sort'=>$re_move['sort']));
            $up2=$banner_db->where(array('id'=>$re_move['id']))->save(array('sort'=>$re['sort']));
            if($up1 && $up2){
                $banner_db->commit();
                $msg = array('code'=>200);
            }else{
                $banner_db->rollback();
                $msg = array('code'=>104);
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * 编辑上线下线
     */
    public function banner_status(){
        $params['id']=I('banner_id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $db=M('banner_coupon','integral_');
            $arr=$db->where(array('id'=>array('eq',$params['id'])))->find();
            if($arr['status'] == 1){
                $data['status']=2;
            }else{
                $data['status']=1;
            }
            if($arr['status'] == 1){
                $where['admin_id']=array('eq',$this->admin_arr['id']);
                $where['status']=array('eq',1);
                $where['_logic']='and';
                $num=$db->where($where)->count();
                if($num>=5){
                    $msg['code']=1009;
                    $msg['msg']='开启已达上限';
                    returnjson($msg,$this->returnstyle,$this->callback);exit();
                }
            }
            $res=$db->where(array('id'=>array('eq',$params['id'])))->save($data);
            if($res){
                $msg['code']=200;
            }else{
                $msg['code']=104;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


}

?>

