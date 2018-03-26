<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\ActivityModel;
use PublicApi\Service\CouponService;

class ActivityService{
    
    /**
     * Activity对象
     *
     * @var MerAdmin\Model\ActivityModel 
     */
    public $activity_model;//活动配置model
    
    public function __construct()
    {
        $this->activity_model = Singleton::getModel('MerAdmin\\Model\\ActivityModel');
    }
    
    /**
     * 验证活动id是否被使用
     * @param int $activity
     * @return
     */
    public function checkisUser($admin_id, $activity, $system = ActivityModel::SYSTEM_0)
    {
        $data = $this->getAll($admin_id, '', '', '', $system);
    
        if(!empty($data))
        {
            foreach ($data as $k => $v)
            {
                if(!empty($v['activity']))
                {
                    $activitys = explode(',', $v['activity']);
    
                    if(in_array($activity, $activitys))
                    {
                        return false;
                    }
                }
            }
        }
    
        return true;
    }
    
    /**
     * 拉取活动下的优惠券数据
     *
     * @param arr $integral_arr
     * @return
     */
    public function getCouponListByActivity($integral_arr,$coupon_default = 1)
    { 
        $score_arr = array();//奖品信息
        
        if(!empty($integral_arr))
        {
            foreach ($integral_arr as $k => $v)
            {
                $activitys = explode(',', $v['activity']);
                
                foreach ($activitys as $k3 => $v3)
                {
                    if($v['type'] == 'ZHT_YX')//平台活动
                    {
                        if($coupon_default == 2){
                            $coupon_return_data = CouponService::getListByAct($v3);
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
                                    $coupon_data['activity'] = $v3;
                                    $coupon_data['activityname'] = $v4['activityName'];
                                    $coupon_data['activity_type'] = 'ZHT_YX';
                                    $coupon_data['writeoff_count'] = $v4['writeoffNum'];
                                    $coupon_data['issue'] = $v4['getNum'];
                                
                                    //！！！！！！！
                                    //总数需要列表返回    --  目前没有这个参数。需要跟营销平台沟通  详情和列表都需要
                                    $coupon_data['num'] = $v4['pourNum']?$v4['pourNum']:'';
                                    $coupon_data['coupon_pid'] = $v4['couponActivityId'];
                                    $res[] = $coupon_data;
//                                     $res = $coupon_data_all;
                                }
//                                 unset($coupon_data_all);
                            }
                        }else{
                            $url = "http://101.201.175.219/promo/prize/ka/prize/list/".$v3;
                            $url_params = array();
                        }
                    }
                    elseif($v['type'] == 'ERP_YX')//第三方活动
                    {
                        $url = C('DOMAIN').'/ErpService/Erpoutput/prize_list';
                        $url_params['key_admin'] = $this->key_admin;
                        $url_params['activity']  = $v['activity'];
                        $url_params['sign_key']  = $this->admin_arr['signkey'];
                        $url_params['sign']      = sign($url_params);
                        unset($url_params['sign_key']);
                    }
                    
                    if($coupon_default != 2){
                        $return = json_decode(http($url, $url_params),true);//处理返回结果
                        $res = $return['data'];
                    }
                    
                    if(!empty($res))
                    {
                        foreach ($res as $k2 => $v2)
                        {
                            $v2['activity'] = $v3;
                            $v2['activityname'] = $v['activityname'];
                            $v2['activity_type'] = $v['type'];
                            $score_arr[] = $v2;
                        }
                        unset($res);
                    }
                }
            }
        }
        return $score_arr;
    }
    
    function integralSort($integral)
    {
        $arr = array();
        $max = '';
        if(!empty($integral))
        {
            foreach ($integral as $k => $v)
            {
                $arr[] = $v;
            }
            
            $max = max($arr);
        }
        
        return $max;
    }
    
    /**
     * 拉取单条活动下的优惠券数据
     *
     * @param int    $activity_id
     * @param string $type
     * @param int    $pid
     * @return
     */
    public function getOnceCouponListByActivity($activity_id, $type, $pid, $coupon_default = 1,$coupon_id)
    { 
        $score_arr = array();//奖品信息

        if($type == 'ZHT_YX')//平台活动
        {
            if($coupon_default == 2){
                $coupon_return_data = CouponService::getDetailById($coupon_id, $pid);
                if($coupon_return_data['code'] != 200 || $coupon_return_data['data'] == ''){
                    return array();die;
                }//print_R($coupon_return_data);die;

                $coupon_data['pid'] = $coupon_return_data['data']['id'];
                $coupon_data['id'] = $coupon_return_data['data']['id'];
                $coupon_data['main_info'] = $coupon_return_data['data']['mainInfo'];
                $coupon_data['extend_info'] = $coupon_return_data['data']['descClause']?$coupon_return_data['data']['descClause']:$coupon_return_data['data']['mainInfo'];
                $coupon_data['image_url'] = $coupon_return_data['data']['couponImageList'][0]['imgUrl']?$coupon_return_data['data']['couponImageList'][0]['imgUrl']:'';
                $coupon_data['start_time'] = $coupon_return_data['data']['effectiveStartTime'];
                $coupon_data['end_time'] = $coupon_return_data['data']['effectiveEndTime'];
                $coupon_data['status'] = $coupon_return_data['data']['validateStatus'] == 1?0:$coupon_return_data['data']['validateStatus'];
                $coupon_data['activity'] = $activity_id;
                $coupon_data['activityname'] = $coupon_return_data['data']['activityName'];
                $coupon_data['activity_type'] = 'ZHT_YX';
                $coupon_data['writeoff_count'] = $coupon_return_data['data']['writeoffNum'];
                $coupon_data['issue'] = $coupon_return_data['data']['getNum'];
                
                //！！！！！！！
                //总数需要列表返回    --  目前没有这个参数。需要跟营销平台沟通   详情和列表都需要
                $coupon_data['num'] = $coupon_return_data['data']['pourNum']?$coupon_return_data['data']['pourNum']:'';
                $coupon_data['coupon_pid'] = $coupon_return_data['data']['couponActivityId'];
                return $coupon_data;die;
            }else{
                $url = "http://101.201.175.219/promo/prize/ka/prize/list/".$activity_id;
                $url_params = array();
            }
        }
        elseif($type == 'ERP_YX')//第三方活动
        {
            $url = C('DOMAIN').'/ErpService/Erpoutput/prize_list';
            $url_params['key_admin'] = $this->key_admin;
            $url_params['activity']  = $activity_id;
            $url_params['sign_key']  = $this->admin_arr['signkey'];
            $url_params['sign']      = sign($url_params);
            unset($url_params['sign_key']);
        }

        $return = json_decode(http($url, $url_params),true);//处理返回结果
        $res = $return['data'];

        if(!empty($res))
        {
            foreach ($res as $k2 => $v2)
            {
                if($v2['id'] == $pid)
                {
                    $v2['activity'] = $activity_id;
                    $v2['activity_type'] = $type;
                    $score_arr = $v2;
                }
            }
        }

        return $score_arr;
    }
    
    /**
     * 获取活动信息
     *
     * @param int $admin_id
     * @param int $activity
     * @param int $type
     * @param int $buildid
     * @param int $system
     * @param int $notid
     * @param int $isShowTime
     * @return
     */
    public function getAll($admin_id, $activity = '', $type = '', $buildid = '', $system = ActivityModel::SYSTEM_1, $notid = 0, $isShowTime = 0)
    {
        $arr = $this->activity_model->getAll($admin_id, $activity, $type, $buildid, $system, $notid, $isShowTime);
        
        return $arr;
    }
    
    /**
     * 获取一条活动信息
     *
     * @param int $admin_id
     * @param int $type
     * @param int $buildid
     * @param int $system
     * @return
     */
    public function getOnce($admin_id, $type = '', $buildid = '', $system = ActivityModel::SYSTEM_1)
    {
        $arr = $this->activity_model->getOnce($admin_id, $type, $buildid, $system);
    
        return $arr;
    }
    
    /**
     * 根据活动id获取一条活动信息
     *
     * @param int $actity
     * @return
     */
    public function getByActity($actity)
    { 
        $arr = $this->activity_model->getByActity($actity);
    
        return $arr;
    }
    
    /**
     * 按唯一id获取一条活动信息
     *
     * @param int $id
     * @return
     */
    public function getById($id)
    {
        $arr = $this->activity_model->getById($id);
    
        return $arr;
    }
    
    /**
     * 按唯一id删除
     *
     * @param int $id
     * @return
     */
    public function delById($id)
    {
        $arr = $this->activity_model->delById($id);
    
        return $arr;
    }
    
    /**
     * 插入一条活动券信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->activity_model->add($insert);
    
        return $lastid;
    }
    
    /**
     * 更新一条活动券信息
     *
     * @param int $id
     * @param arr $arr
     * @return
     */
    public function updateById($id, $arr)
    {
        $this->activity_model->updateById($id, $arr);
    
        return $id;
    }
}
?>