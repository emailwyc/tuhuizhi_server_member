<?php
/**
 * 新版积分商城(后台)
 * User: wutong
 * Date: 9/4/17
 * Time: 10:02 AM
 */
namespace MerAdmin\Controller;

use common\ServiceLocator;
use MerAdmin\Model\ActivityModel;
use MerAdmin\Model\ActivityTypeModel;
use MerAdmin\Model\ActivityPropertyModel;

class NewIntegralController extends AuthController
{
    public $key_admin;
    public $admin_arr;
    
    public function _initialize(){
        parent::_initialize();
        $this->key_admin = $this->ukey;
    }
    
    /**
     * 获取活动配置
     * @return
     * http://localhost/member/index.php/MerAdmin/NewIntegral/obtain_act?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function obtain_act(){
        $buildid = I('buildid');
        $type = I('type');
        $status = I('status');
        
        $activityService = ServiceLocator::getActivityService();
        $act_arr = $activityService->getAll($this->admin_arr['id'], '', $type, $buildid, ActivityModel::SYSTEM_2);
        //3.0,4.0
        $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'coupon_default');
        //判断活动ID是否设置
        if(empty($act_arr))
        {
            $msg = array('code'=>102);
        }
        else
        {
            $i = 0;
            $new = array();
            foreach ($act_arr as $k => $v)
            {
                if($v['type'] == 'ZHT_YX')
                {
                    if($v['activity'])
                    {
                        if($coupon_data['function_name'] == 2){
                            $res = $status?$status:1;
                        }else{
                            $url = "http://182.92.31.114/rest/act/status/".$v['activity'];//活动下所有券
                            $res = json_decode(http($url, array()),true);//处理返回结果
                        }
                        
                    }
                    
                    if($status == null || ($status != null && $status == $res))
                    {
                        $new[$i] = $v;
                        $new[$i]['status'] = !is_null($res) ? $res : '';//默认0准备中（审核通过，前提是当前状态为5）、1.已开始、2.暂停、3.已结束、默认为0、5.审核中，6、未审核，7驳回 8.已删除
                        $i++;
                    }
                }
                else
                {
                    $new[$i] = $v;
                    $new[$i]['status'] = 1;
                    $i++;
                }
            }
            
            $msg = array('code'=>200,'data'=> $new);
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    function sock_get($url)
    {
             $info = parse_url($url);
             
             
             $fp = fsockopen($info["host"], 80, $errno, $errstr, 3);
             $head = "GET ".$info['path']."?".$info["query"]." HTTP/1.0\r\n";
             $head .= "Host: ".$info['host']."\r\n";
             $head .= "\r\n";
             $write = fputs($fp, $head);
             while (!feof($fp)){
                  $line = fgets($fp);
                  echo $line."<br>";
             }
    }
    
    /**
     * 添加活动
     * @param $key_admin $activity
     * @return mixed
     * http://localhost/member/index.php/MerAdmin/NewIntegral/act_add?key_admin=202cb962ac59075b964b07152d234b70&activity=183263&buildid=860100010040500017&type=ERP_YX&start_showtime=2017-08-31 15:58:11&end_showtime=2017-09-21 15:58:11
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
        $params['system'] = ActivityModel::SYSTEM_2;
        $params['start_showtime'] = I('start_showtime') ? I('start_showtime') : '';
        $params['end_showtime'] = I('end_showtime') ? I('end_showtime') : '';

        //判断是否存在当前的配置,如果存在则是修改,反之添加
        $activityService = ServiceLocator::getActivityService();
        //验证活动id是否被使用
        $res = $activityService->getAll($this->admin_arr['id'], $params['activity'], '', $params['buildid'], ActivityModel::SYSTEM_2);
        
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
     * localhost/member/index.php/MerAdmin/NewIntegral/act_update?key_admin=202cb962ac59075b964b07152d234b70&start_showtime=2017-09-04 15:58:11&end_showtime=2017-10-04 15:58:11&id=37
     */
    public function act_update(){
        $params['id'] = I('id');
        if(in_array('', $params)){
            $msg['code'] = 1030;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $params['start_showtime'] = I('start_showtime') ? I('start_showtime') : '';
        $params['end_showtime'] = I('end_showtime') ? I('end_showtime') : '';
        
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
     * localhost/member/index.php/MerAdmin/NewIntegral/act_del?key_admin=202cb962ac59075b964b07152d234b70&id=37
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
     * localhost/member/index.php/MerAdmin/NewIntegral/integral_type_list?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function integral_type_list(){
        $activityTypeService = ServiceLocator::getActivityTypeService();
        $arr = $activityTypeService->getAll($this->admin_arr['id'], ActivityTypeModel::SYSTEM_0, 2);
        
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
     * 添加&修改分类
     * @param $key_admin $id $type_name $status
     * @return mixed
     * localhost/member/index.php/MerAdmin/NewIntegral/integral_type_save?key_admin=202cb962ac59075b964b07152d234b70&name=新积分商城
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
            $arr = $activityTypeService->getById($id, ActivityTypeModel::SYSTEM_0);
            
            //添加
            if(empty($arr))
            {
                $data = $activityTypeService->getByName($this->admin_arr['id'], $name, ActivityTypeModel::SYSTEM_0);
                
                if(!empty($data))
                {
                    //已经存在
				   echo returnjson(array('code'=>1008),$this->returnstyle,$this->callback);exit();
                }
                else
                {
                    $res = $activityTypeService->add(array('admin_id' => $this->admin_arr['id'], 'type_name' => $name, 'system' => ActivityTypeModel::SYSTEM_0));
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
     * 修改分类状态
     * @param $key_admin $id $disable
     * @return mixed
     * localhost/member/index.php/MerAdmin/NewIntegral/integral_type_disable?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function integral_type_disable(){
        //参数为空判断
        $id = I('id');
        $disable = I('disable');//是否禁用，0启用，1禁用
        
        if(empty($id)){
            $msg['code'] = 1030;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $activityTypeService = ServiceLocator::getActivityTypeService();
        $arr = $activityTypeService->getById($id, ActivityTypeModel::SYSTEM_0);
        
        if(empty($arr)){
            $msg['code'] = 102;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $res = $activityTypeService->updateById($id, array('disable' => $disable));
        
        if($res)
        {
            $msg=array('code'=>200);
        }
        else
        {
            $msg=array('code'=>104);
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
            $activityPropertyNewService = ServiceLocator::getActivityPropertyNewService();
            $data = $activityPropertyNewService->getAll('', $id);
            
            //有正在使用该分类的礼品
            if(!empty($data))
            {
                $msg = array('code'=>1596);
                echo returnjson($msg,$this->returnstyle,$this->callback);exit();
            }
            
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
     * 活动id列表
     * http://localhost/member/index.php/MerAdmin/NewIntegral/activty_list?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function activty_list(){
        $params['buildid'] = I('buildid') ? I('buildid') : "";
        $activityService = ServiceLocator::getActivityService();
        $arr = $activityService->getAll($this->admin_arr['id'], '', '', $params['buildid'], ActivityModel::SYSTEM_2);
        
        $data = array();
        if(!empty($arr))
        {
            foreach ($arr as $k => $v)
            {
                $data[] = array('id' => $v['activity'], 'name' => $v['activityname']);
            }
        }
        
        $msg = array('code'=>200, data => $data);
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 查询所有奖品信息
     * @param $key_admin $type_id
     * @return mixed
     * http://localhost/member/index.php/MerAdmin/NewIntegral/integral_list?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function integral_list(){
        $params['buildid'] = I('buildid') ? I('buildid') : "";
        $params['activity'] = I('activity') ? I('activity') : '';
        $params['status'] = I('status') != null ? I('status') : '';//券状态0:正常(投放中)  1:暂停 2:待发布(审核通过) 3:已过期 4: 待审核 5:驳回  9:删除
        $isShowTime = I('isshoutime') ? I('isshoutime') : 0;//0全部，1c端展示中，2c端未展示
        $page = I('page') ? I('page') : 1;
        
        //查询c端未展示的数据时，避免遗漏查询所有
        $newIsShowTime = $isShowTime;
        if($isShowTime == ActivityModel::NOT_SHOWTIME)
        {
            $newIsShowTime = 0;
        }
        
        //查询活动ID
        $activityService = ServiceLocator::getActivityService();
        $integral_arr = $activityService->getAll($this->admin_arr['id'], $params['activity'], '', $params['buildid'], ActivityModel::SYSTEM_2, 0, $newIsShowTime);
        $inClienShowTime = array();
        
        if(empty($integral_arr))
        {
            $msg = array('code'=>307,'data'=>'暂无奖品，敬请期待...');
        }
        else
        {
            $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->key_admin,'coupon_default');
            //$coupon_data['function_name'] == 2;//这里是默认4.0  上线之后删除
            
            //过滤无用活动
            foreach ($integral_arr as $k => $v)
            {
                if($v['start_showtime'] > date('Y-m-d H:i:s', time()) ||  $v['end_showtime'] < date('Y-m-d H:i:s', time()))//c端未展示的活动
                {
                   $inClienShowTime[] = $v['activity'];
                }
                
                $url = "http://182.92.31.114/rest/act/status/".$v['activity'];//活动下所有券
                $res = json_decode(http($url, array()),true);//处理返回结果

                if(!in_array($res, array(0,1,2,3)))//默认0准备中（审核通过，前提是当前状态为5）、1.已开始、2.暂停、3.已结束、默认为0、5.审核中，6、未审核，7驳回 8.已删除
                {
                    unset($integral_arr[$k]);
                }
            }
            
            $score_arr = array();//奖品信息
            $api_arr   = array();//最终返回数据
            $res_data = array();//筛选后的数据
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
                    $property = $activityPropertyNewService->getOnce($v['id'], '', $params['buildid']);
                    
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
                    
                        $type = $activityTypeService->getById($property['type_id'], ActivityTypeModel::SYSTEM_0);
                    
                        //本地数据
                        $api_arr[$v['id']]['id']        = !empty($property) ? $property['id'] : '';
                        $api_arr[$v['id']]['des']       = !empty($property) ? $property['des'] : '';//排序
                        $api_arr[$v['id']]['is_status'] = !empty($property) ? $property['is_status'] : ActivityPropertyModel::STATUS_1;//状态
                        $api_arr[$v['id']]['type_id']   = !empty($property) ? $property['type_id'] : '';//分类ID
                        $api_arr[$v['id']]['type_name'] = !empty($type) ? $type['type_name'] : '';//分类名字
                        $api_arr[$v['id']]['admin_id']  = !empty($property) ? $property['admin_id'] : '';//商场ID
                        $api_arr[$v['id']]['content']   = !empty($property) ? $property['content'] : '';//礼品详情
                        $api_arr[$v['id']]['discount']  = !empty($property) ? $property['discount'] : ActivityPropertyModel::DISCOUNT_1;//是否开启折扣
                        $api_arr[$v['id']]['integral']  = $integral;
                        
                        //上下线判断
                        if($isShowTime == 0 || 
                          ($isShowTime == ActivityModel::NOT_SHOWTIME && in_array($api_arr[$v['id']]['activity'], $inClienShowTime)) ||
                          ($isShowTime == ActivityModel::NOT_SHOWTIME && $api_arr[$v['id']]['is_status'] == ActivityPropertyModel::STATUS_1) ||
                          ($isShowTime == ActivityModel::IS_SHOWTIME && $api_arr[$v['id']]['is_status'] == ActivityPropertyModel::STATUS_2))
                        {
                            $res_data[] = $api_arr[$v['id']];
                        }
                    }
                }
            }
            
            //返回数据
            if($res_data)
            {
                $offset = 10;
                $start = ($page - 1) * $offset;
                
                $count = count($res_data);
                $allpage = ceil($count/$offset);//总页数
                $newarr = array_slice($res_data, $start, $offset);
                
                $msg=array('code'=>200, 'data'=> array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$newarr ? $newarr : array()));
            }
            else
            {
                $msg=array('code'=>200,'data'=>'');
            }
    
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 查询单个奖品信息
     * @param $key_admin $pid
     * @return mixed
     * http://localhost/member/index.php/MerAdmin/NewIntegral/integral_list_once?buildid=860100010040500017&key_admin=202cb962ac59075b964b07152d234b70&pid=169301
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
                $msg = array('code'=>102);
            }
            else
            {
                if($pro_arr['discount'] == ActivityPropertyModel::DISCOUNT_2)
                {
                    $pro_arr['integral'] = json_decode($pro_arr['integral'],true);
                }

                $msg = array('code'=>200,'data'=>$pro_arr);
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 修改奖品信息
     * @param $key_admin
     * @param $pid
     * @param $activity_id
     * @param $des
     * @param $type_id
     * @param $integral
     * @param $discount
     * @param $activity_type
     * @param $buildid
     * @param $content
     * @param $vip_area
     * localhost/member/index.php/MerAdmin/NewIntegral/integral_operation?buildid=860100010040500017&key_admin=202cb962ac59075b964b07152d234b70&pid=169301&activity_id=18676&type_id=75&integral=1
     */
    public function integral_operation(){
        //参数为空判断
        $act_id        = I('pid');
        $activity_id   = I('activity_id');
        $des           = I('des');//排序
        $type_id       = I('type_id');//分类
        $integral      = I('integral') ? I('integral') : 0;//所需积分
        $discount      = I('discount') ? I('discount') : ActivityPropertyModel::DISCOUNT_1;//是否开启折扣,1统一折扣，2尊享折扣
        $activity_type = I('activity_type') ? I('activity_type') : 'ZHT_YX';
        $buildid       = I('buildid') ? I('buildid') : '';
        $content       = I('content');//描述信息
        $vip_area      = I('vip_area') ? I('vip_area') : 0;//是否开启专区,１不开启 ２开启
        $coupon_id = I('couponID');
        
        if(empty($act_id) || empty($activity_id))
        {
            $msg = array('code'=>1030);
        }
        else
        {
            
            $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->key_admin,'coupon_default');
            //$coupon_data['function_name'] == 2;//这里是默认4.0  上线之后删除
            
            $activityService = ServiceLocator::getActivityService();
            $once_arr = $activityService->getOnceCouponListByActivity($activity_id, $activity_type, $act_id,$coupon_data['function_name'],$coupon_id);
            
            if(empty($once_arr))
            {
                $msg['code'] = 1035;
                returnjson($msg,$this->returnstyle,$this->callback);exit();
            }
            
            $activityTypeService = ServiceLocator::getActivityTypeService();
            $type_arr = $activityTypeService->getById($type_id, ActivityTypeModel::SYSTEM_0);
            
            if(!$type_arr)
            {//分类不存在
                $msg['code'] = 1081;
                returnjson($msg,$this->returnstyle,$this->callback);exit();
            }
            
            //查看表中是否存在这条信息，如果存在则是修改，如果不存在则是添加
            $activityPropertyNewService = ServiceLocator::getActivityPropertyNewService();
            $pro_arr = $activityPropertyNewService->getOnce($act_id, $this->admin_arr['id'], $buildid);
            //平台数据
            $data['pid']            = $act_id;//奖品ID
            $data['coupon_id']    =$once_arr['coupon_pid'];//券批ID
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
            $data['integral']       = $integral;//兑换积分
            $data['des']            = $des;//排序
            $data['is_status']      = ActivityPropertyModel::STATUS_2;//状态
            $data['type_id']        = $type_id;//分类ID
            $data['admin_id']       = $this->admin_arr['id'];
            $data['content']        = htmlspecialchars_decode($content);//礼品详情
            $data['discount']       = $discount;//是否开启折扣
            $data['get_type']       = 0;//领取类型
            $data['wechat_amount']  = 0;//微信支付金额
            $data['system']  = ActivityPropertyModel::SYSTEM_0;//积分商城
            $data['vip_area'] = $vip_area;
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
     * http://localhost/member/index.php/MerAdmin/NewIntegral/update_operation?&key_admin=202cb962ac59075b964b07152d234b70
     */
    public function update_operation(){
        //查询活动ID
        $activityService = ServiceLocator::getActivityService();
        $integral_arr = $activityService->getAll($this->admin_arr['id'], '', '', '', ActivityModel::SYSTEM_2);
        
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
            $score_arr = $activityService->getCouponListByActivity($integral_arr,$coupon_data['function_name']);
        
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
                            $v2['coupon_id']   =  $v['coupon_pid'];
                            
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
     * http://localhost/member/index.php/MerAdmin/NewIntegral/status_operation?key_admin=202cb962ac59075b964b07152d234b70&status=1&pid=169301
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
                
                $activityService = ServiceLocator::getActivityService();
                $activity = $activityService->getByActity($pro_arr['activity_id']);
                
                //未到礼品展示时间，如需上线请修改当前活动ID在c端展示时间~
                if(empty($activity) ||  date('Y-m-d H:i:s', time()) < $activity['client_showtime'] )
                {
                    $msg = array('code'=>1598);
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
     * 搜索兑奖记录
     * http://localhost/member/index.php/MerAdmin/NewIntegral/get_prize_search?&key_admin=202cb962ac59075b964b07152d234b70&status=2&export=yes
     */
    public function get_prize_search(){
        $activity_id= I('activity_id');
        $type       = I('type') ? I('type') : 'ZHT_YX';
        $buildid    = I('buildid') ? I('buildid') : "";
        $isexport   = I('export');
        $status     = I('status');//中奖状态 0:未发放 1:已发放 2:已领取 3: 已核销 4:已撤销 5:已过期 6:转增中 7:核销中 8:退款中 9：已退款
        $mobile     = I('mobile');
        $prize_name = I('prize_name');//奖品ID
        $starttime  = I('starttime');
        $endtime    = I('endtime') ? I('endtime') : date('Y-m-d H:i:s');
        $page       = I('page');
        
        $activityService = ServiceLocator::getActivityService();
        $integralLogService = ServiceLocator::getIntegralLogService($this->admin_arr['pre_table']);
        
        $integral_arr = $activityService->getAll($this->admin_arr['id'], $activity_id, '', $buildid, ActivityModel::SYSTEM_2);
        
        //活动id
        if(!empty($integral_arr))
        {
            foreach ($integral_arr as $k => $v)
            {
                $params['activity_id'][] = $v['activity'];
            }
            
            $activity_str = implode(',', $params['activity_id']);
        }
        
        if($activity_str == ''){
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);exit();
        }
        
//         if($mobile)
//         {
//             $mem_db = M('mem',$this->admin_arr['pre_table']);
//             $mem_arr = $mem_db->where(array('mobile'=>array('eq',$mobile)))->find();
//             if($mem_arr['openid'] != '')
//             {
//                 $where['open_id'] = $mem_arr['openid'];
//             }
//             else
//             {
//                 $msg['code'] = 102;
//                 returnjson($msg,$this->returnstyle,$this->callback);exit();die;
//             }
//         }
        
        if($status != '')
        {
            $where['status'] = array('eq',$status);
        }
        if($prize_name)
        {
            $where['prize_id'] = array('eq',$prize_name);
        }
        if($starttime)
        {
            $where['get_time'] = array('between',array($starttime,$endtime));
        }
        
        if($activity_str)
        {
            $where['activity_id'] = array('in',$activity_str);
        }

        $excelService = ServiceLocator::getExcelService();
        //excel导出
        if(strtolower($isexport) == 'yes' || strtolower($isexport) == 'yesall'){
            set_time_limit(0);
            $excelService->exportHeader();
            $title = array("建筑物","活动id","奖品名称","会员名","会员卡号","会员等级","手机号","积分","领取时间","状态");
            $excelService->addArray($title);
        }
        
        $coupon_data = $this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'coupon_default');
        
        $msg = $this->get_prize_action($params['activity_id'], $page, $where, $isexport, $buildid, $mobile,$coupon_data['function_name']);

        $use_db = M('member_code','total_');
        $level_returns = $use_db->where("admin_id=".$this->admin_arr['id'])->field('name,id,code')->select();

        //获取卡级别信息
        foreach($level_returns as $k => $v)
        {
            $level_return[$v['code']] = $v['name'];
        }

        //卡级别字段
        foreach($msg['data'] as $k => $v)
        {
            $msg['data'][$k]['level'] = $level_return[$v['level']];
        }
        
        //excel导出
        if(strtolower($isexport) == 'yes' || strtolower($isexport) == 'yesall'){
            //获取商场信息
            $buildid_db  = M('total_buildid');
            $buildid_arr = $buildid_db->where(array('buildid'=>array('eq',$buildid)))->find();
            
            if($msg['code']=='200')
            {
                $msgNew = $excelService->integral_log($msg['data'], $buildid_arr['name']);exit;
            }
        }
        
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    //查询返回数据方法
    private function get_prize_action($activity_id, $page, $where, $export, $buildid, $mobile = '',$coupon_status){

        $page = $page ? $page : 1;
        $lines = 10;
        $start = ($page-1) * $lines;
    
        //实例化营销平台记录表
        $commonService = ServiceLocator::getCommonService();
        if($coupon_status == 2){
            $db = $commonService->db_connect4();
            if(strtolower($export) == 'yesall')
            {
                $arr = $db->where($where)->field('id,coupon_id as prize_id,open_id,get_time,status,qr_code,activity_id,coupon_batch_id')->order('get_time desc')->select();
            }
            else
            {
                $arr = $db->where($where)->field('id,coupon_id as prize_id,open_id,get_time,status,qr_code,activity_id,coupon_batch_id')->limit($start,$lines)->order('get_time desc')->select();
            }
        }else{
            $db = $commonService->db_connect();
            if(strtolower($export) == 'yesall')
            {
                $arr = $db->where($where)->field('id,prize_id,open_id,get_time,status,qr_code,activity_id')->order('get_time desc')->select();
            }
            else
            {
                $arr = $db->where($where)->field('id,prize_id,open_id,get_time,status,qr_code,activity_id')->limit($start,$lines)->order('get_time desc')->select();
            }
        }
        
        if($arr){
            $sum = $db->where($where)->count();
    
            $integral_db = M('integral_log',$this->admin_arr['pre_table']);
            $integral_log = $this->admin_arr['pre_table'].'integral_log';
            $mem          = $this->admin_arr['pre_table'].'mem';
            $qr_arr = array();
            
            foreach($arr as $k => $v){
                $qr_code[] = $v['qr_code'];//QR二维码
                $qr_arr[$v['qr_code']] = array('buildid' => $buildid, 'activity_id' => $v['activity_id']);
            }
            
            $qr_num = count($qr_code);
            if($qr_num){
                $qu_once = ceil($qr_num/100);

                $integral_arr = array();

                for($i=0;$i<$qu_once;$i++){
                    $start_num = $i*100;
                    $new_qr    = array_slice($qr_code,$start_num,100);
    
                    $qr_code_str = implode(',', $new_qr);
    
                    $sqlArr['code'] = array('in', $qr_code_str);
                    if($mobile)
                    {
                        $sqlArr[$mem.'.mobile'] = $mobile;
                    }
                    if($buildid)
                    {
                        $sqlArr[$integral_log.'.buildid'] = $buildid;
                    }
                    
                    $integral_arr_once = $integral_db->where($sqlArr)->join('LEFT JOIN '.$mem.' on '.$integral_log.'.cardno = '.$mem.'.cardno')->field($mem.'.cardno,usermember,mobile,level,prize_name,integral,code')->select();

                    $integral_arr = array_merge($integral_arr,$integral_arr_once);
                }
            }
            
            //注:禁止循环套循环
            foreach($integral_arr as $k => $v)
            {
                $res_arr[$v['code']] = $v;
                if(!empty($qr_arr[$v['code']]))
                {
                    $res_arr[$v['code']]['activity_id'] = $qr_arr[$v['code']]['activity_id'];
                }
            }
            
            $newarr = array();
            foreach($arr as $key => $val)
            {
                if($res_arr[$val['qr_code']]['prize_name'])
                {
                    if($res_arr[$val['qr_code']] != '')
                    {
                        $newarr[$key] = array_merge($val, $res_arr[$val['qr_code']]);
                    }
                    
                    $newarr[$key]['buildid'] = $buildid;
                    $newarr[$key]['starttime'] = $val['get_time'];
                }
            }
            
            $msg['code'] = 200;
            $msg['data'] = $newarr;
            $msg['sum']  = $sum;
            $msg['page'] = $page;
        }
        else
        {
            $msg['code'] = 102;
        }
        
        //结束
        return $msg;
    }
    
    /**
     * 添加或修改兑换状态
     * http://localhost/member/index.php/MerAdmin/NewIntegral/integral_save?&key_admin=202cb962ac59075b964b07152d234b70&function_name=2&description=test
     */
    public function integral_save(){
        $params['function_name'] = I('function_name');//是否开启线下兑换
        if(in_array('', $params)){
            $msg['code']=1030;
        }
        else
        {
            $params['description'] = I('description');//描述
            $db = M('default',$this->admin_arr['pre_table']);
            $arr = $db->where(array('customer_name'=>array('eq','integral_status')))->select();
             
            $data['description']   = $params['description'];
            $data['function_name'] = $params['function_name'];
            if($arr){
                $res = $db->where(array('customer_name'=>array('eq','integral_status')))->save($data);
                if($res !== false){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
            }
            else
            {
                $data['customer_name'] = 'integral_status';
                $res = $db->add($data);
                if($res !== false){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
            }
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
    }
    
    /**
     * 查看支付状态
     */
    public function integral_status(){
        $db=M('default',$this->admin_arr['pre_table']);
        $arr=$db->where(array('customer_name'=>array('eq','integral_status')))->select();
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 获取颜色接口
     */
    public function get_integral_color(){
        $find=$this->GetOneAmindefault($this->admin_arr['pre_table'], $this->key_admin, 'integralcolorset');
        if($find){
            $msg['code']=200;
            $msg['data']=$find;
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 添加颜色接口
     */
    public function integral_color_add(){
        $params['color']=I('color');
        $find=$this->GetOneAmindefault($this->admin_arr['pre_table'], $this->key_admin, 'integralcolorset');
        $db=M('default',$this->admin_arr['pre_table']);
        if($find != ''){
            $save_url=$db->where(array('customer_name'=>'integralcolorset'))->save(array('function_name'=>$params['color']));
        }else{
            $save_url=$db->add(array('function_name'=>$params['color'],'customer_name'=>'integralcolorset','description'=>'设置C端积分商城颜色配置'));
        }
        $this->redis->del('admin:default:one:integralcolorset:'. $this->key_admin);//删除redis缓存
         
        if($save_url !== false){
            $msg['code']=200;
        }else{
            $msg['code']=104;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 获取banner列表(老接口迁移过来,表结构稍做调整)
     */
    public function banner_list(){
        $buildid=I('buildid');
        if($buildid){
            $map['buildid']=array('eq',$buildid);
            $map['_logic']='and';
        }
        $banner_db=M('banner_new','integral_');
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
            $banner_db=M('banner_new','integral_');
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
        $params['buildid']=I('buildid');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $params['jump_url']=I('jump_url');
            $banner_id=I('id');
            $banner_db=M('banner_new','integral_');
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
            $banner_db=M('banner_new','integral_');
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
            $banner_db=M('banner_new','integral_');
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
            $db=M('banner_new','integral_');
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

}

?>