<?php
/**
 * 积分抽奖(后台)
 * User: wutong
 * Date: 11/3/17
 * Time: 17:19 AM
 */
namespace MerAdmin\Controller;

use common\ServiceLocator;
use MerAdmin\Model\ActivityDrawModel;

class DrawController extends AuthController
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
     * http://localhost/member/index.php/MerAdmin/Draw/obtain_act?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function obtain_act(){
        $buildid = I('buildid');
        $status = I('status');
        
        $activityDrawService = ServiceLocator::getActivityDrawService();
        $act_arr = $activityDrawService->getAll($this->admin_arr['id'], '', $buildid);
        
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
                        $url = "http://182.92.31.114/rest/act/status/".$v['activity'];//活动下所有券
                        $res = json_decode(http($url, array()),true);//处理返回结果
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
    
    /**
     * 添加活动
     * @param $key_admin $activity
     * @return mixed
     * http://localhost/member/index.php/MerAdmin/Draw/act_add?key_admin=202cb962ac59075b964b07152d234b70&activity=18675&buildid=860100010040500017&discount=1&integral=10&templateid=1
     */
    public function act_add(){
        $params['activity'] = I('activity');
        $params['buildid'] = I('buildid');
        $params['discount'] = I('discount');//是否开启折扣,1统一折扣，2尊享折扣
        $params['integral'] = I('integral');
        $params['templateid'] = I('templateid');//模板id

        if(in_array('', $params)){
            $msg['code'] = 1030;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }

        $url = "http://182.92.31.114/rest/act/status/".$params['activity'];
        $res = json_decode(http($url, array()),true);
        
        //活动不存在
        if($res == -1)
        {
            $msg['code'] = 1595;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $urls = "http://101.201.175.219/promo/api/ka/activity/detail?activityId=".$params['activity'];//获取活动名
        $nameres = file_get_contents($urls);
        
        //活动不存在
        if(empty($nameres))
        {
            $msg['code'] = 1595;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $activityName = json_decode($nameres,true);
        
        $params['admin_id'] = $this->admin_arr['id'];
        $params['name'] = I('name') ? I('name') : $activityName;//活动名
        $params['desc'] = I('desc') ? I('desc') : '';//描述
        $params['backimage'] = I('backimage') ? I('backimage') : '';//背景图
        $params['advertisement'] = I('advertisement') ? I('advertisement') : '';//广告设置
        $params['buttonset'] = I('buttonset') ? I('buttonset') : '';//按钮设置
        $params['drawlogo'] = I('drawlogo') ? I('drawlogo') : '';//抽奖图片logo
        $params['sponsorlogo'] = I('sponsorlogo') ? I('sponsorlogo') : '';//主办方logo
        $params['ctime'] = date('Y-m-d H:i:s', time());
        
        //判断是否存在当前的配置,如果存在则是修改,反之添加
        $activityDrawService = ServiceLocator::getActivityDrawService();
        //验证活动id是否被使用
        $res = $activityDrawService->getAll($this->admin_arr['id'], $params['activity'], $params['buildid']);
        
        if(!empty($res))
        {
            $msg['code'] = 1504;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }

        //添加
        $res = $activityDrawService->add($params);
         
        if($res !== false){
            $msg['code']=200;
        }else{
            $msg['code']=104;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 修改活动
     * @return mixed
     * localhost/member/index.php/MerAdmin/Draw/act_update?key_admin=202cb962ac59075b964b07152d234b70&activity=18675&buildid=860100010040500017&discount=1&integral=20&templateid=2
     */
    public function act_update(){
        $params['id'] = I('id');
        if(in_array('', $params)){
            $msg['code'] = 1030;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        //判断是否存在当前的配置,如果存在则是修改,反之添加
        $activityDrawService = ServiceLocator::getActivityDrawService();
        
        $activity_arr = $activityDrawService->getById($params['id']);
        
        if(empty($activity_arr))
        {
            $msg['code'] = 1035;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        else
        {
            //修改
            $res = $activityDrawService->updateById($activity_arr['id'], $params);
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
     * localhost/member/index.php/MerAdmin/Draw/act_del?key_admin=202cb962ac59075b964b07152d234b70&id=37
     */
    public function act_del(){
        $params['id'] = I('id');
        if(in_array('', $params)){
            $msg['code'] = 1030;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $activityDrawService = ServiceLocator::getActivityDrawService();
        $res = $activityDrawService->delById($params['id']);
        
        if($res !== false){
            $msg['code']=200;
        }else{
            $msg['code']=104;
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 搜索兑奖记录
     * http://localhost/member/index.php/MerAdmin/Draw/get_prize_search?&key_admin=202cb962ac59075b964b07152d234b70&status=2&export=yes
     */
    public function get_prize_search(){
        $activity_id= I('activity_id');
        $buildid    = I('buildid') ? I('buildid') : "";
        $isexport   = I('export');
        $page       = I('page');
        
        $activityDrawService = ServiceLocator::getActivityDrawService();
        $integralLogService = ServiceLocator::getIntegralLogService($this->admin_arr['pre_table']);
        
        $integral_arr = $activityDrawService->getAll($this->admin_arr['id'], $activity_id, $buildid);
        
        //活动id
        if(!empty($integral_arr))
        {
            foreach ($integral_arr as $k => $v)
            {
                $params['activity_id'][] = $v['activity'];
            }
            
            $activity_str = implode(',', $params['activity_id']);
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
        
        $msg = $this->get_prize_action($params['activity_id'], $page, $where, $isexport, $buildid, $mobile);

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
    private function get_prize_action($activity_id, $page, $where, $export, $buildid, $mobile = ''){

        $page = $page ? $page : 1;
        $lines = 10;
        $start = ($page-1) * $lines;
    
        //实例化营销平台记录表
        $commonService = ServiceLocator::getCommonService();
        $db = $commonService->db_connect();
        
        if(strtolower($export) == 'yesall')
        {
            $arr = $db->where($where)->field('id,prize_id,open_id,get_time,status,qr_code,activity_id')->order('get_time desc')->select();
        }
        else
        {
            $arr = $db->where($where)->field('id,prize_id,open_id,get_time,status,qr_code,activity_id')->limit($start,$lines)->order('get_time desc')->select();
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
    
}

?>

